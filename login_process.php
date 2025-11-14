<?php
require_once 'config.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Receber dados do formulário
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validações básicas
if (empty($username) || empty($password)) {
    $_SESSION['erro_login'] = "Preencha todos os campos.";
    header('Location: login.php');
    exit;
}

try {
    // Buscar usuário pelo username
    $stmt = $pdo->prepare("SELECT id, nome, email, username, senha, tipo FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $usuario = $stmt->fetch();

    // Verificar se usuário existe e senha está correta
    if ($usuario && password_verify($password, $usuario['senha'])) {
        // Login bem-sucedido - criar sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_username'] = $usuario['username'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['logado'] = true;

        // Redirecionar para página inicial
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['erro_login'] = "Usuário ou senha incorretos.";
        header('Location: login.php');
        exit;
    }

} catch(PDOException $e) {
    $_SESSION['erro_login'] = "Erro ao fazer login: " . $e->getMessage();
    header('Location: login.php');
    exit;
}
?>

