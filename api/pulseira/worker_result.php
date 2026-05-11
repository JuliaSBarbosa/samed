<?php
require_once __DIR__ . '/../../funcoes_pulseira.php';

function workerUpsertPulseira(PDO $pdo, string $uidTag, string $deviceId, ?string $payloadNdef, string $status, string $tipoTag = 'NTAG215', bool $marcarGravacao = false): int
{
    $stmt = $pdo->prepare("SELECT * FROM pulseiras WHERE uid_tag = ? LIMIT 1 FOR UPDATE");
    $stmt->execute([$uidTag]);
    $pulseira = $stmt->fetch();

    if ($pulseira) {
        $campos = [
            'tipo_tag = ?',
            'status = ?',
            'ultimo_device_id = ?',
            'ultima_leitura_em = NOW()',
        ];
        $valores = [$tipoTag, $status, $deviceId];

        if ($payloadNdef !== null && $payloadNdef !== '') {
            $campos[] = 'payload_ndef = ?';
            $valores[] = $payloadNdef;
        }

        if ($marcarGravacao) {
            $campos[] = 'ultima_gravacao_em = NOW()';
        }

        $valores[] = (int) $pulseira['id'];
        $stmt = $pdo->prepare("UPDATE pulseiras SET " . implode(', ', $campos) . " WHERE id = ?");
        $stmt->execute($valores);

        return (int) $pulseira['id'];
    }

    $stmt = $pdo->prepare("
        INSERT INTO pulseiras (
            uid_tag,
            tipo_tag,
            payload_ndef,
            status,
            ultimo_device_id,
            ultima_leitura_em,
            ultima_gravacao_em
        ) VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    $stmt->execute([
        $uidTag,
        $tipoTag,
        $payloadNdef,
        $status,
        $deviceId,
        $marcarGravacao ? date('Y-m-d H:i:s') : null,
    ]);

    return (int) $pdo->lastInsertId();
}

function workerDesativarVinculosAtivos(PDO $pdo, int $pulseiraId, int $perfilMedicoId, string $observacao): array
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT pulseira_id
        FROM pulseira_vinculos
        WHERE ativo = 1
          AND (pulseira_id = ? OR perfil_medico_id = ?)
        FOR UPDATE
    ");
    $stmt->execute([$pulseiraId, $perfilMedicoId]);
    $ids = array_map('intval', array_column($stmt->fetchAll(), 'pulseira_id'));

    $stmt = $pdo->prepare("
        UPDATE pulseira_vinculos
        SET ativo = 0,
            desvinculado_em = NOW(),
            observacao = ?
        WHERE ativo = 1
          AND (pulseira_id = ? OR perfil_medico_id = ?)
    ");
    $stmt->execute([$observacao, $pulseiraId, $perfilMedicoId]);

    return $ids;
}

pulseiraRequirePdo($pdo);
$deviceId = pulseiraRequireWorkerAuth();

$input = pulseiraGetJsonInput();
$comandoId = isset($input['command_id']) ? (int) $input['command_id'] : 0;
$statusInformado = strtolower(trim((string) ($input['status'] ?? '')));
$uidTag = pulseiraNormalizeUid($input['uid_tag'] ?? '');
$payloadNdef = isset($input['payload_ndef']) ? trim((string) $input['payload_ndef']) : null;
$mensagemWorker = trim((string) ($input['message'] ?? ''));
$tipoTag = trim((string) ($input['tipo_tag'] ?? 'NTAG215'));

if ($comandoId <= 0) {
    pulseiraJsonResponse([
        'success' => false,
        'message' => 'command_id é obrigatório.'
    ], 422);
}

if (!in_array($statusInformado, ['sucesso', 'erro'], true)) {
    pulseiraJsonResponse([
        'success' => false,
        'message' => 'status do worker inválido.'
    ], 422);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT *
        FROM pulseira_comandos
        WHERE id = ?
          AND device_id = ?
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$comandoId, $deviceId]);
    $comando = $stmt->fetch();

    if (!$comando) {
        throw new RuntimeException('Comando não encontrado para este device_id.');
    }

    $payloadDesejado = pulseiraParseResultado($comando['payload_desejado'] ?? null);
    $resultado = [
        'device_id' => $deviceId,
        'acao' => $comando['acao'],
        'uid_tag' => $uidTag ?: null,
        'payload_ndef' => $payloadNdef,
    ];
    $pulseiraId = null;

    if ($statusInformado === 'erro') {
        $erroMensagem = $mensagemWorker !== '' ? $mensagemWorker : 'Falha na operação da pulseira no Raspberry.';

        $stmt = $pdo->prepare("
            UPDATE pulseira_comandos
            SET status = 'erro',
                erro_mensagem = ?,
                resultado_json = ?,
                processado_em = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $erroMensagem,
            json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $comandoId
        ]);

        pulseiraRegistrarEvento($pdo, null, $comandoId, 'erro_' . $comando['acao'], [
            'device_id' => $deviceId,
            'uid_tag' => $uidTag ?: null,
            'payload_ndef' => $payloadNdef,
            'message' => $erroMensagem,
        ], false);

        $pdo->commit();

        pulseiraJsonResponse([
            'success' => true,
            'message' => 'Resultado de erro registrado com sucesso.'
        ]);
    }

    if (in_array($comando['acao'], ['gravar', 'ler'], true) && $uidTag === '') {
        throw new RuntimeException('O worker precisa informar uid_tag para ações de gravar ou ler.');
    }

    if ($comando['acao'] === 'gravar') {
        $perfilMedicoId = (int) ($comando['perfil_medico_id'] ?? 0);
        if ($perfilMedicoId <= 0) {
            throw new RuntimeException('Comando de gravação sem perfil_medico_id.');
        }

        $payloadFinal = $payloadNdef ?: ($payloadDesejado['url'] ?? null);
        $pulseiraId = workerUpsertPulseira($pdo, $uidTag, $deviceId, $payloadFinal, 'vinculada', $tipoTag, true);

        $pulseirasAfetadas = workerDesativarVinculosAtivos(
            $pdo,
            $pulseiraId,
            $perfilMedicoId,
            'Substituído por nova gravação de pulseira'
        );

        $idsDisponiveis = array_values(array_filter($pulseirasAfetadas, static function ($id) use ($pulseiraId) {
            return (int) $id !== $pulseiraId;
        }));

        if ($idsDisponiveis) {
            $placeholders = implode(',', array_fill(0, count($idsDisponiveis), '?'));
            $stmt = $pdo->prepare("UPDATE pulseiras SET status = 'disponivel' WHERE id IN ($placeholders)");
            $stmt->execute($idsDisponiveis);
        }

        $stmt = $pdo->prepare("
            INSERT INTO pulseira_vinculos (
                pulseira_id,
                perfil_medico_id,
                ativo,
                vinculado_por,
                observacao
            ) VALUES (?, ?, 1, ?, ?)
        ");
        $stmt->execute([
            $pulseiraId,
            $perfilMedicoId,
            $comando['solicitante_id'] ? (int) $comando['solicitante_id'] : null,
            'Vínculo criado via Raspberry + PN532'
        ]);

        $resultado['pulseira_id'] = $pulseiraId;
        $resultado['perfil_medico_id'] = $perfilMedicoId;
        $resultado['payload_ndef'] = $payloadFinal;
        $resultado['redirect_url'] = pulseiraBuildFichaUrl($perfilMedicoId);
        $resultado['message'] = 'Pulseira gravada e vinculada à ficha com sucesso.';

        pulseiraRegistrarEvento($pdo, $pulseiraId, $comandoId, 'gravar', [
            'device_id' => $deviceId,
            'uid_tag' => $uidTag,
            'payload_ndef' => $payloadFinal,
            'perfil_medico_id' => $perfilMedicoId,
        ], true);
    } elseif ($comando['acao'] === 'ler') {
        $pulseiraId = workerUpsertPulseira($pdo, $uidTag, $deviceId, $payloadNdef, 'disponivel', $tipoTag, false);

        $stmt = $pdo->prepare("
            SELECT pv.perfil_medico_id
            FROM pulseira_vinculos pv
            WHERE pv.pulseira_id = ?
              AND pv.ativo = 1
            ORDER BY pv.vinculado_em DESC, pv.id DESC
            LIMIT 1
        ");
        $stmt->execute([$pulseiraId]);
        $vinculo = $stmt->fetch();

        $perfilResolvido = $vinculo ? (int) $vinculo['perfil_medico_id'] : pulseiraExtrairIdFichaDoPayload($payloadNdef);
        $fonteResolucao = $vinculo ? 'vinculo' : ($perfilResolvido ? 'payload' : 'nenhuma');

        if ($vinculo) {
            $stmt = $pdo->prepare("UPDATE pulseiras SET status = 'vinculada' WHERE id = ?");
            $stmt->execute([$pulseiraId]);
        }

        $resultado['pulseira_id'] = $pulseiraId;
        $resultado['perfil_medico_id'] = $perfilResolvido;
        $resultado['fonte_resolucao'] = $fonteResolucao;
        $resultado['redirect_url'] = $perfilResolvido ? pulseiraBuildFichaUrl($perfilResolvido) : null;
        $resultado['message'] = $perfilResolvido
            ? 'Pulseira lida com sucesso e ficha localizada.'
            : 'Pulseira lida, mas nenhuma ficha vinculada foi encontrada.';

        pulseiraRegistrarEvento($pdo, $pulseiraId, $comandoId, 'ler', [
            'device_id' => $deviceId,
            'uid_tag' => $uidTag,
            'payload_ndef' => $payloadNdef,
            'perfil_medico_id' => $perfilResolvido,
            'fonte_resolucao' => $fonteResolucao,
        ], true);
    } else {
        $resultado['message'] = 'Ação recebida pelo worker não exige processamento adicional.';
    }

    $stmt = $pdo->prepare("
        UPDATE pulseira_comandos
        SET status = 'sucesso',
            pulseira_id = ?,
            resultado_json = ?,
            erro_mensagem = NULL,
            processado_em = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $pulseiraId,
        json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $comandoId
    ]);

    $pdo->commit();

    pulseiraJsonResponse([
        'success' => true,
        'message' => 'Resultado do worker registrado com sucesso.',
        'result' => $resultado,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    pulseiraJsonResponse([
        'success' => false,
        'message' => 'Falha ao registrar o resultado do worker.',
        'error' => $e->getMessage(),
    ], 500);
}
?>
