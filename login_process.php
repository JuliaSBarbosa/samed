<?php
require_once 'config.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Receber dados do formulário
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validações básicas
if (empty($email) || empty($password)) {
    $_SESSION['erro_login'] = "Preencha todos os campos.";
    header('Location: login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro_login'] = "E-mail inválido.";
    header('Location: login.php');
    exit;
}

try {
    // Buscar usuário pelo email
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo, crm, coren FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verificar se usuário existe e senha está correta
    if ($usuario && password_verify($password, $usuario['senha'])) {
        // Login bem-sucedido - criar sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['usuario_crm'] = $usuario['crm'] ?? null;
        $_SESSION['usuario_coren'] = $usuario['coren'] ?? null;
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

