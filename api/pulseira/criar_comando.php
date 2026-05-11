<?php
require_once __DIR__ . '/../../funcoes_pulseira.php';

pulseiraRequireAuth();
pulseiraRequirePdo($pdo);

$input = array_merge($_POST, pulseiraGetJsonInput());
$acao = strtolower(trim((string) ($input['acao'] ?? '')));
$deviceId = trim((string) ($input['device_id'] ?? pulseiraDefaultDeviceId()));
$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$perfilMedicoId = isset($input['perfil_medico_id']) ? (int) $input['perfil_medico_id'] : 0;

if ($usuarioId <= 0) {
    pulseiraJsonResponse([
        'success' => false,
        'message' => 'Usuário inválido para operar pulseiras.'
    ], 401);
}

if (!in_array($acao, ['gravar', 'ler', 'esquecer'], true)) {
    pulseiraJsonResponse([
        'success' => false,
        'message' => 'Ação de pulseira inválida.'
    ], 422);
}

if ($deviceId === '') {
    pulseiraJsonResponse([
        'success' => false,
        'message' => 'Nenhum dispositivo Raspberry foi configurado para esta operação.'
    ], 422);
}

try {
    if (in_array($acao, ['gravar', 'esquecer'], true)) {
        if ($perfilMedicoId <= 0) {
            pulseiraJsonResponse([
                'success' => false,
                'message' => 'Perfil médico inválido para a operação.'
            ], 422);
        }

        $perfil = pulseiraBuscarPerfilOperavel($pdo, $perfilMedicoId, $usuarioId);
        if (!$perfil) {
            pulseiraJsonResponse([
                'success' => false,
                'message' => 'Você não tem permissão para operar esta ficha.'
            ], 403);
        }
    }

    if ($acao === 'esquecer') {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO pulseira_comandos (
                device_id,
                acao,
                perfil_medico_id,
                solicitante_id,
                status
            ) VALUES (?, ?, ?, ?, 'em_execucao')
        ");
        $stmt->execute([$deviceId, $acao, $perfilMedicoId, $usuarioId]);
        $comandoId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            SELECT pv.id, pv.pulseira_id, p.uid_tag
            FROM pulseira_vinculos pv
            INNER JOIN pulseiras p ON p.id = pv.pulseira_id
            WHERE pv.perfil_medico_id = ?
              AND pv.ativo = 1
        ");
        $stmt->execute([$perfilMedicoId]);
        $vinculosAtivos = $stmt->fetchAll();

        $uids = [];
        $pulseiraIds = [];
        foreach ($vinculosAtivos as $vinculo) {
            $uids[] = $vinculo['uid_tag'];
            $pulseiraIds[] = (int) $vinculo['pulseira_id'];
        }

        $stmt = $pdo->prepare("
            UPDATE pulseira_vinculos
            SET ativo = 0,
                desvinculado_em = NOW(),
                observacao = 'Desvinculado via botao Esquecer Pulseira'
            WHERE perfil_medico_id = ?
              AND ativo = 1
        ");
        $stmt->execute([$perfilMedicoId]);

        if ($pulseiraIds) {
            $placeholders = implode(',', array_fill(0, count($pulseiraIds), '?'));
            $stmt = $pdo->prepare("
                UPDATE pulseiras
                SET status = 'disponivel',
                    ultimo_device_id = ?,
                    atualizado_em = NOW()
                WHERE id IN ($placeholders)
            ");
            $stmt->execute(array_merge([$deviceId], $pulseiraIds));
        }

        $resultado = [
            'message' => $pulseiraIds
                ? 'Vínculo da pulseira removido com sucesso. A tag pode ser gravada novamente.'
                : 'Nenhuma pulseira ativa estava vinculada a esta ficha.',
            'uids_desvinculados' => $uids,
            'perfil_medico_id' => $perfilMedicoId,
        ];

        $stmt = $pdo->prepare("
            UPDATE pulseira_comandos
            SET status = 'sucesso',
                resultado_json = ?,
                processado_em = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $comandoId
        ]);

        foreach ($pulseiraIds as $index => $pulseiraId) {
            pulseiraRegistrarEvento($pdo, $pulseiraId, $comandoId, 'desvinculo', [
                'uid_tag' => $uids[$index] ?? null,
                'device_id' => $deviceId,
                'perfil_medico_id' => $perfilMedicoId,
                'acao' => 'esquecer',
            ], true);
        }

        $pdo->commit();

        pulseiraJsonResponse([
            'success' => true,
            'command_id' => $comandoId,
            'status' => 'sucesso',
            'message' => $resultado['message'],
            'result' => $resultado,
        ]);
    }

    $payload = $acao === 'gravar'
        ? pulseiraMontarPayloadDesejado($perfilMedicoId)
        : ['tipo' => 'consulta', 'tag_tipo' => 'NTAG215'];

    $stmt = $pdo->prepare("
        INSERT INTO pulseira_comandos (
            device_id,
            acao,
            perfil_medico_id,
            solicitante_id,
            status,
            payload_desejado
        ) VALUES (?, ?, ?, ?, 'pendente', ?)
    ");
    $stmt->execute([
        $deviceId,
        $acao,
        $perfilMedicoId ?: null,
        $usuarioId,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ]);

    $comandoId = (int) $pdo->lastInsertId();

    $mensagem = $acao === 'gravar'
        ? 'Comando enviado. Aproximando a tag do leitor PN532 para gravar a pulseira.'
        : 'Comando enviado. Aproximando a pulseira do leitor PN532 para consultar a tag.';

    pulseiraJsonResponse([
        'success' => true,
        'command_id' => $comandoId,
        'status' => 'pendente',
        'message' => $mensagem,
        'device_id' => $deviceId,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    pulseiraJsonResponse([
        'success' => false,
        'message' => 'Falha ao criar o comando de pulseira.',
        'error' => $e->getMessage(),
    ], 500);
}
?>
