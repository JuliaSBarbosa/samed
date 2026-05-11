<?php
require_once __DIR__ . '/../../funcoes_pulseira.php';

pulseiraRequireAuth();
pulseiraRequirePdo($pdo);

$comandoId = isset($_GET['command_id']) ? (int) $_GET['command_id'] : 0;
$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

if ($comandoId <= 0) {
    pulseiraJsonResponse([
        'success' => false,
        'message' => 'command_id inválido.'
    ], 422);
}

$stmt = $pdo->prepare("
    SELECT *
    FROM pulseira_comandos
    WHERE id = ?
      AND solicitante_id = ?
    LIMIT 1
");
$stmt->execute([$comandoId, $usuarioId]);
$comando = $stmt->fetch();

if (!$comando) {
    pulseiraJsonResponse([
        'success' => false,
        'message' => 'Comando não encontrado para este usuário.'
    ], 404);
}

$resultado = pulseiraParseResultado($comando['resultado_json'] ?? null);
$mensagem = $resultado['message'] ?? null;

if (!$mensagem) {
    if ($comando['status'] === 'pendente') {
        $mensagem = 'Comando aguardando atendimento do Raspberry.';
    } elseif ($comando['status'] === 'em_execucao') {
        $mensagem = 'Raspberry processando a operação na pulseira.';
    } elseif ($comando['status'] === 'erro') {
        $mensagem = $comando['erro_mensagem'] ?: 'Falha ao processar a pulseira.';
    } elseif ($comando['status'] === 'sucesso') {
        $mensagem = 'Operação na pulseira concluída com sucesso.';
    }
}

pulseiraJsonResponse([
    'success' => true,
    'command' => [
        'id' => (int) $comando['id'],
        'device_id' => $comando['device_id'],
        'acao' => $comando['acao'],
        'status' => $comando['status'],
        'perfil_medico_id' => $comando['perfil_medico_id'] ? (int) $comando['perfil_medico_id'] : null,
        'pulseira_id' => $comando['pulseira_id'] ? (int) $comando['pulseira_id'] : null,
        'created_at' => $comando['criado_em'],
        'processed_at' => $comando['processado_em'],
        'message' => $mensagem,
        'error_message' => $comando['erro_mensagem'],
        'result' => $resultado,
    ]
]);
?>
