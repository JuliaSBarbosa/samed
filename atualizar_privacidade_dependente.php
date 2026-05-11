<?php
require_once 'verificar_login.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dependentes.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$dependente_id = isset($_POST['dependente_id']) ? (int) $_POST['dependente_id'] : null;

if (!$usuario_id || !$dependente_id || !$pdo) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações.";
    header('Location: dependentes.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM dependentes WHERE id = ? AND paciente_id = ?");
    $stmt->execute([$dependente_id, $usuario_id]);
    $dependente_existe = $stmt->fetch();

    if (!$dependente_existe) {
        $_SESSION['erro_perfil'] = "Dependente não encontrado ou não pertence a você.";
        header('Location: dependentes.php');
        exit;
    }

    $autorizacao_usuario = isset($_POST['autorizacao_usuario']) && $_POST['autorizacao_usuario'] === 'sim' ? 'sim' : 'nao';

    $stmt = $pdo->prepare("SELECT id FROM perfis_medicos WHERE dependente_id = ? AND usuario_id IS NULL");
    $stmt->execute([$dependente_id]);
    $perfil_existente = $stmt->fetch();

    $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($perfil_existente) {
        if (in_array('autorizacao_usuario', $colunas)) {
            $stmt = $pdo->prepare("UPDATE perfis_medicos SET autorizacao_usuario = ? WHERE dependente_id = ? AND usuario_id IS NULL");
            $stmt->execute([$autorizacao_usuario, $dependente_id]);
        }
    } else {
        $campos_insert = ['dependente_id'];
        $valores_insert = [$dependente_id];

        if (in_array('autorizacao_usuario', $colunas)) {
            $campos_insert[] = 'autorizacao_usuario';
            $valores_insert[] = $autorizacao_usuario;
        }

        $placeholders = str_repeat('?, ', count($valores_insert) - 1) . '?';
        $campos_str = implode(', ', $campos_insert);

        $stmt = $pdo->prepare("INSERT INTO perfis_medicos ($campos_str) VALUES ($placeholders)");
        $stmt->execute($valores_insert);
    }

    $_SESSION['sucesso_perfil'] = "Configurações de privacidade atualizadas com sucesso!";
} catch (PDOException $e) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações: " . $e->getMessage();
}

header('Location: perfil_dependente.php?id=' . $dependente_id);
exit;
?>
