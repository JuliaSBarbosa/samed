<?php
require_once 'config.php';
require_once 'verificar_login.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form_perfil.php');
    exit;
}

// Verificar se o usuário está logado e é paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'paciente') {
    $_SESSION['erro'] = "Apenas pacientes podem atualizar o perfil médico.";
    header('Location: perfil.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Receber e limpar dados do formulário
$nome = trim($_POST['nome'] ?? '');
$data_nascimento = $_POST['data_nascimento'] ?? null;
$sexo = $_POST['sexo'] ?? null;
$cpf = trim($_POST['cpf'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');

// Dados de contato de emergência
$contato_nome = trim($_POST['contato_nome'] ?? '');
$parentesco = trim($_POST['parentesco'] ?? '');
$contato_telefone = trim($_POST['contato_telefone'] ?? '');

// Dados médicos
$tipo_sanguineo = $_POST['tipo_sanguineo'] ?? null;
$doencas_cronicas = trim($_POST['doencas'] ?? '');
$alergias = trim($_POST['alergias'] ?? '');
$medicacao_continua = trim($_POST['medicacao'] ?? '');
$doenca_mental = trim($_POST['doenca_mental'] ?? '');
$dispositivo_implantado = trim($_POST['dispositivo'] ?? '');
$info_relevantes = trim($_POST['info_relevantes'] ?? '');
$cirurgias = trim($_POST['cirurgias'] ?? '');

// Validações básicas
$erros = [];

if (empty($nome)) {
    $erros[] = "Nome completo é obrigatório.";
}

if (empty($data_nascimento)) {
    $erros[] = "Data de nascimento é obrigatória.";
}

if (empty($sexo)) {
    $erros[] = "Sexo é obrigatório.";
}

if (empty($contato_nome)) {
    $erros[] = "Nome do contato de emergência é obrigatório.";
}

if (empty($contato_telefone)) {
    $erros[] = "Telefone do contato de emergência é obrigatório.";
}

// Validar CPF se fornecido
if (!empty($cpf)) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) !== 11) {
        $erros[] = "CPF inválido.";
    }
}

// Validar email se fornecido
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "E-mail inválido.";
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['erros'] = $erros;
    $_SESSION['dados_form'] = $_POST;
    header('Location: form_perfil.php');
    exit;
}

try {
    // Verificar se o banco está disponível
    if ($pdo === null) {
        throw new Exception("Banco de dados não disponível.");
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Verificar se já existe perfil médico para este usuário
    $stmt = $pdo->prepare("SELECT id FROM perfis_medicos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $perfil_existente = $stmt->fetch();

    // Formatar data de nascimento
    $data_nascimento_formatada = $data_nascimento ? date('Y-m-d', strtotime($data_nascimento)) : null;
    
    // Formatar CPF (com máscara)
    $cpf_formatado = !empty($cpf) ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2) : null;

    if ($perfil_existente) {
        // Atualizar perfil existente
        $stmt = $pdo->prepare("
            UPDATE perfis_medicos 
            SET data_nascimento = ?, 
                sexo = ?, 
                cpf = ?, 
                telefone = ?, 
                email = ?, 
                tipo_sanguineo = ?, 
                doencas_cronicas = ?, 
                alergias = ?, 
                medicacao_continua = ?, 
                doenca_mental = ?, 
                dispositivo_implantado = ?, 
                info_relevantes = ?, 
                cirurgias = ?,
                data_atualizacao = CURRENT_TIMESTAMP
            WHERE usuario_id = ?
        ");
        $stmt->execute([
            $data_nascimento_formatada,
            $sexo,
            $cpf_formatado,
            $telefone,
            $email,
            $tipo_sanguineo,
            $doencas_cronicas ?: null,
            $alergias ?: null,
            $medicacao_continua ?: null,
            $doenca_mental ?: null,
            $dispositivo_implantado ?: null,
            $info_relevantes ?: null,
            $cirurgias ?: null,
            $usuario_id
        ]);
    } else {
        // Inserir novo perfil
        $stmt = $pdo->prepare("
            INSERT INTO perfis_medicos 
            (usuario_id, data_nascimento, sexo, cpf, telefone, email, tipo_sanguineo, 
             doencas_cronicas, alergias, medicacao_continua, doenca_mental, 
             dispositivo_implantado, info_relevantes, cirurgias) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $usuario_id,
            $data_nascimento_formatada,
            $sexo,
            $cpf_formatado,
            $telefone,
            $email,
            $tipo_sanguineo,
            $doencas_cronicas ?: null,
            $alergias ?: null,
            $medicacao_continua ?: null,
            $doenca_mental ?: null,
            $dispositivo_implantado ?: null,
            $info_relevantes ?: null,
            $cirurgias ?: null
        ]);
    }

    // Verificar se já existe contato de emergência
    $stmt = $pdo->prepare("SELECT id FROM contatos_emergencia WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $contato_existente = $stmt->fetch();

    if ($contato_existente) {
        // Atualizar contato existente
        $stmt = $pdo->prepare("
            UPDATE contatos_emergencia 
            SET nome = ?, parentesco = ?, telefone = ? 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$contato_nome, $parentesco, $contato_telefone, $usuario_id]);
    } else {
        // Inserir novo contato
        $stmt = $pdo->prepare("
            INSERT INTO contatos_emergencia (usuario_id, nome, parentesco, telefone) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$usuario_id, $contato_nome, $parentesco, $contato_telefone]);
    }

    // Atualizar nome do usuário se foi alterado
    if (!empty($nome)) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
        $stmt->execute([$nome, $usuario_id]);
        $_SESSION['usuario_nome'] = $nome;
    }

    // Confirmar transação
    $pdo->commit();

    // Sucesso
    $_SESSION['sucesso'] = "Perfil médico atualizado com sucesso!";
    header('Location: perfil.php');
    exit;

} catch(PDOException $e) {
    // Reverter transação em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['erros'] = ["Erro ao salvar perfil: " . $e->getMessage()];
    $_SESSION['dados_form'] = $_POST;
    header('Location: form_perfil.php');
    exit;
} catch(Exception $e) {
    $_SESSION['erros'] = ["Erro: " . $e->getMessage()];
    $_SESSION['dados_form'] = $_POST;
    header('Location: form_perfil.php');
    exit;
}
?>

