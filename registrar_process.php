<?php
require_once 'config.php';
require_once 'validar_registros.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registrar.php');
    exit;
}

// Receber e limpar dados do formulário
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$crm = trim($_POST['crm'] ?? '');
$coren = trim($_POST['coren'] ?? '');

// Validações básicas
$erros = [];

if (empty($nome)) {
    $erros[] = "Nome completo é obrigatório.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "E-mail válido é obrigatório.";
}

if (empty($password) || strlen($password) < 6) {
    $erros[] = "Senha deve ter no mínimo 6 caracteres.";
}

if (empty($tipo) || !in_array($tipo, ['paciente', 'medico', 'enfermeiro'])) {
    $erros[] = "Selecione se você é paciente, médico ou enfermeiro.";
}

// Validações específicas por tipo
if ($tipo === 'medico') {
    if (empty($crm)) {
        $erros[] = "CRM é obrigatório para médicos.";
    } else {
        // Validar CRM usando função de validação
        $validacao_crm = validarCRM($crm);
        if (!$validacao_crm['valido']) {
            $erros[] = $validacao_crm['mensagem'];
        } else {
            // Se a validação retornou dados, usar o CRM formatado
            if (isset($validacao_crm['dados']['crm_formatado'])) {
                $crm = $validacao_crm['dados']['crm_formatado'];
            }
        }
    }
}

if ($tipo === 'enfermeiro') {
    if (empty($coren)) {
        $erros[] = "COREN é obrigatório para enfermeiros.";
    } else {
        // Validar COREN usando função de validação
        $validacao_coren = validarCOREN($coren);
        if (!$validacao_coren['valido']) {
            $erros[] = $validacao_coren['mensagem'];
        } else {
            // Se a validação retornou dados, usar o COREN formatado
            if (isset($validacao_coren['dados']['coren_formatado'])) {
                $coren = $validacao_coren['dados']['coren_formatado'];
            }
        }
    }
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

    // Verificar se CRM já existe (se for médico)
    if ($tipo === 'medico' && !empty($crm)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE crm = ?");
        $stmt->execute([$crm]);
        if ($stmt->fetch()) {
            $_SESSION['erros'] = ["Este CRM já está cadastrado."];
            header('Location: registrar.php');
            exit;
        }
    }

    // Verificar se COREN já existe (se for enfermeiro)
    if ($tipo === 'enfermeiro' && !empty($coren)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE coren = ?");
        $stmt->execute([$coren]);
        if ($stmt->fetch()) {
            $_SESSION['erros'] = ["Este COREN já está cadastrado."];
            header('Location: registrar.php');
            exit;
        }
    }

    // Hash da senha
    $senha_hash = password_hash($password, PASSWORD_DEFAULT);

    // Inserir novo usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, crm, coren) VALUES (?, ?, ?, ?, ?, ?)");
    $crm_value = ($tipo === 'medico') ? $crm : null;
    $coren_value = ($tipo === 'enfermeiro') ? $coren : null;
    $stmt->execute([$nome, $email, $senha_hash, $tipo, $crm_value, $coren_value]);

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
