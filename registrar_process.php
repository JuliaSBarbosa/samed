<?php
require_once 'config.php';
require_once 'validar_registros.php';
require_once 'funcoes_auxiliares.php';

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

// Validação de senha segura
if (empty($password)) {
    $erros[] = "Senha é obrigatória.";
} else {
    $validacao_senha = validarSenhaSegura($password);
    if (!$validacao_senha['valido']) {
        $erros[] = $validacao_senha['mensagem'];
    }
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

// Validação por foto para médico e enfermeiro (obrigatório no registro)
$foto_documento_nome = null;
$foto_selfie_nome = null;
if (in_array($tipo, ['medico', 'enfermeiro'])) {
    if (empty($_FILES['foto_documento']['name']) || $_FILES['foto_documento']['error'] !== UPLOAD_ERR_OK) {
        $erros[] = "Envie a foto do documento profissional (CRM/COREN ou RG com registro).";
    }
    if (empty($_FILES['foto_selfie']['name']) || $_FILES['foto_selfie']['error'] !== UPLOAD_ERR_OK) {
        $erros[] = "Envie a selfie segurando o documento.";
    }
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros'] = $erros;
    $_SESSION['dados_form'] = $_POST; // Para manter os dados preenchidos
    header('Location: registrar.php');
    exit;
}

if ($pdo === null) {
    $_SESSION['erros'] = [
        "Não foi possível conectar ao banco de dados. Verifique se o MySQL está ligado no XAMPP e se o banco \"samed\" existe. Confira também o arquivo config.php (host, usuário e senha)."
    ];
    $_SESSION['dados_form'] = $_POST;
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

    // Definir status de validação inicial
    $status_validacao = in_array($tipo, ['medico', 'enfermeiro']) ? 'pendente' : 'aprovado';

    // Salvar fotos de validação para médico/enfermeiro
    $pasta_upload = __DIR__ . '/uploads/fotos/';
    if (!is_dir($pasta_upload)) {
        mkdir($pasta_upload, 0775, true);
    }
    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($tipo, ['medico', 'enfermeiro'])) {
        foreach (['foto_documento' => 'doc_reg', 'foto_selfie' => 'selfie_reg'] as $key => $prefixo) {
            $arq = $_FILES[$key] ?? null;
            if ($arq && $arq['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($arq['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $permitidas)) {
                    $_SESSION['erros'] = ["Formato inválido em \"$key\". Use JPG, PNG, GIF ou WEBP."];
                    $_SESSION['dados_form'] = $_POST;
                    header('Location: registrar.php');
                    exit;
                }
                $nome_arquivo = $prefixo . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($arq['tmp_name'], $pasta_upload . $nome_arquivo)) {
                    $_SESSION['erros'] = ["Erro ao salvar o arquivo de $key."];
                    $_SESSION['dados_form'] = $_POST;
                    header('Location: registrar.php');
                    exit;
                }
                if ($key === 'foto_documento') $foto_documento_nome = $nome_arquivo;
                else $foto_selfie_nome = $nome_arquivo;
            }
        }
    }

    // Inserir novo usuário (com fotos de validação quando for médico/enfermeiro)
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, crm, coren, status_validacao, foto_documento, foto_selfie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $crm_value = ($tipo === 'medico') ? $crm : null;
    $coren_value = ($tipo === 'enfermeiro') ? $coren : null;
    $stmt->execute([$nome, $email, $senha_hash, $tipo, $crm_value, $coren_value, $status_validacao, $foto_documento_nome, $foto_selfie_nome]);

    // Sucesso - redirecionar para login (mensagem diferente para profissionais em validação)
    if (in_array($tipo, ['medico', 'enfermeiro'])) {
        $_SESSION['sucesso_profissional_kyc'] = true;
    } else {
        $_SESSION['sucesso'] = "Cadastro realizado com sucesso! Faça login para continuar.";
    }
    header('Location: login.php');
    exit;

} catch(PDOException $e) {
    $_SESSION['erros'] = ["Erro ao cadastrar: " . $e->getMessage()];
    header('Location: registrar.php');
    exit;
}
?>
