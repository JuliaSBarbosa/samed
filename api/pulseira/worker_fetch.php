<?php
require_once __DIR__ . '/../../funcoes_pulseira.php';

pulseiraRequirePdo($pdo);
$deviceId = pulseiraRequireWorkerAuth();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT *
        FROM pulseira_comandos
        WHERE device_id = ?
          AND status = 'pendente'
        ORDER BY criado_em ASC, id ASC
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$deviceId]);
    $comando = $stmt->fetch();

    if (!$comando) {
        $pdo->commit();
        pulseiraJsonResponse([
            'success' => true,
            'command' => null,
        ]);
    }

    $stmt = $pdo->prepare("
        UPDATE pulseira_comandos
        SET status = 'em_execucao'
        WHERE id = ?
    ");
    $stmt->execute([(int) $comando['id']]);

    $pdo->commit();

    pulseiraJsonResponse([
        'success' => true,
        'command' => [
            'id' => (int) $comando['id'],
            'device_id' => $comando['device_id'],
            'acao' => $comando['acao'],
            'perfil_medico_id' => $comando['perfil_medico_id'] ? (int) $comando['perfil_medico_id'] : null,
            'payload_desejado' => pulseiraParseResultado($comando['payload_desejado'] ?? null),
            'created_at' => $comando['criado_em'],
        ],
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    pulseiraJsonResponse([
        'success' => false,
        'message' => 'Falha ao buscar comandos pendentes.',
        'error' => $e->getMessage(),
    ], 500);
}
?>
