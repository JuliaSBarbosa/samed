<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$usuario_id) {
    header('Location: historico.php');
    exit;
}

if (!$pdo) {
    $_SESSION['erro'] = 'Não foi possível conectar ao banco de dados para registrar a denúncia.';
    header('Location: historico.php');
    exit;
}

$historico_id = isset($_POST['historico_acesso_id']) ? (int) $_POST['historico_acesso_id'] : 0;
$motivo       = trim($_POST['motivo']    ?? '');
$descricao    = trim($_POST['descricao'] ?? '');

$motivos_validos = ['acesso_indevido', 'dados_incorretos', 'profissional_desconhecido', 'outro'];

if ($historico_id <= 0 || !in_array($motivo, $motivos_validos, true) || mb_strlen($descricao) < 10) {
    $_SESSION['erro'] = 'Preencha todos os campos da denúncia (descrição com pelo menos 10 caracteres).';
    header('Location: historico.php');
    exit;
}

try {
    // 1) Confirmar que o registro existe e pertence ao usuário (paciente direto OU dependente seu).
    $stmt = $pdo->prepare("
        SELECT ha.id
        FROM historico_acessos ha
        LEFT JOIN dependentes d ON ha.dependente_id = d.id
        WHERE ha.id = ?
          AND (
                ha.paciente_id = ?
             OR (ha.dependente_id IS NOT NULL AND d.paciente_id = ?)
          )
        LIMIT 1
    ");
    $stmt->execute([$historico_id, $usuario_id, $usuario_id]);
    $pertence = $stmt->fetch();

    if (!$pertence) {
        $_SESSION['erro'] = 'Consulta não encontrada ou não pertence a você.';
        header('Location: historico.php');
        exit;
    }

    // 2) Evitar duplicidade.
    $stmt = $pdo->prepare("
        SELECT id FROM denuncias_acessos
        WHERE historico_acesso_id = ? AND denunciante_id = ?
        LIMIT 1
    ");
    $stmt->execute([$historico_id, $usuario_id]);
    if ($stmt->fetch()) {
        $_SESSION['erro'] = 'Você já denunciou esta consulta. Aguarde a análise da equipe SAMED.';
        header('Location: historico.php');
        exit;
    }

    // 3) Registrar denúncia.
    $stmt = $pdo->prepare("
        INSERT INTO denuncias_acessos (historico_acesso_id, denunciante_id, motivo, descricao, status)
        VALUES (?, ?, ?, ?, 'pendente')
    ");
    $stmt->execute([$historico_id, $usuario_id, $motivo, $descricao]);

    $_SESSION['sucesso'] = 'Denúncia registrada com sucesso. Nossa equipe irá analisar.';
} catch (PDOException $e) {
    error_log('Erro ao registrar denúncia: ' . $e->getMessage());
    $_SESSION['erro'] = 'Não foi possível registrar a denúncia agora. Tente novamente mais tarde.';
}

header('Location: historico.php');
exit;
