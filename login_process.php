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

// ============================================
// VERIFICAR USUÁRIOS PADRÃO PRIMEIRO
// ============================================
$usuario_encontrado = null;

foreach ($usuarios_padrao as $usuario_padrao) {
    if ($usuario_padrao['email'] === $email && $usuario_padrao['senha'] === $password) {
        $usuario_encontrado = $usuario_padrao;
        break;
    }
}

// Se encontrou nos usuários padrão, fazer login
if ($usuario_encontrado) {
    $_SESSION['usuario_id'] = $usuario_encontrado['id'];
    $_SESSION['usuario_nome'] = $usuario_encontrado['nome'];
    $_SESSION['usuario_email'] = $usuario_encontrado['email'];
    $_SESSION['usuario_tipo'] = $usuario_encontrado['tipo'];
    $_SESSION['usuario_crm'] = $usuario_encontrado['crm'];
    $_SESSION['usuario_coren'] = $usuario_encontrado['coren'];
    $_SESSION['logado'] = true;

    // Redirecionar para página inicial
    header('Location: index.php');
    exit;
}

// ============================================
// SE NÃO ENCONTROU NOS PADRÕES, BUSCAR NO BANCO
// ============================================
if ($pdo !== null) {
    try {
        // Buscar usuário pelo email no banco de dados
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
        }
    } catch(PDOException $e) {
        // Erro no banco, mas não impede o login com usuários padrão
    }
}

// Se chegou aqui, não encontrou em nenhum lugar
$_SESSION['erro_login'] = "Usuário ou senha incorretos.";
header('Location: login.php');
exit;
?>
