<?php
require_once 'config.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registrar.php');
    exit;
}

// Receber e limpar dados do formulário
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$tipo = $_POST['tipo'] ?? '';

// Validações básicas
$erros = [];

if (empty($nome)) {
    $erros[] = "Nome é obrigatório.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "E-mail válido é obrigatório.";
}

if (empty($username)) {
    $erros[] = "Nome de usuário é obrigatório.";
}

if (empty($password) || strlen($password) < 6) {
    $erros[] = "Senha deve ter no mínimo 6 caracteres.";
}

if (empty($tipo) || !in_array($tipo, ['paciente', 'medica'])) {
    $erros[] = "Selecione se você é paciente ou médica.";
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros'] = $erros;
    $_SESSION['dados_form'] = $_POST; // Para manter os dados preenchidos
    header('Location: registrar.php');
    exit;
}

try {
    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['erros'] = ["Este e-mail já está cadastrado."];
        header('Location: registrar.php');
        exit;
    }

    // Verificar se username já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['erros'] = ["Este nome de usuário já está em uso."];
        header('Location: registrar.php');
        exit;
    }

    // Hash da senha
    $senha_hash = password_hash($password, PASSWORD_DEFAULT);

    // Inserir novo usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, username, senha, tipo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $username, $senha_hash, $tipo]);

    // Sucesso - redirecionar para login
    $_SESSION['sucesso'] = "Cadastro realizado com sucesso! Faça login para continuar.";
    header('Location: login.php');
    exit;

} catch(PDOException $e) {
    $_SESSION['erros'] = ["Erro ao cadastrar: " . $e->getMessage()];
    header('Location: registrar.php');
    exit;
}
?>

