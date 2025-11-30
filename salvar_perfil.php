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

// Processar doenças crônicas (pode ser array ou string)
$doencas_input = $_POST['doencas'] ?? '';
if (is_array($doencas_input)) {
    $doencas_filtradas = array_filter(array_map('trim', $doencas_input), function($d) {
        return !empty($d) && $d !== '';
    });
    $doencas_cronicas = !empty($doencas_filtradas) ? implode(', ', $doencas_filtradas) : '';
} else {
    $doencas_cronicas = trim($doencas_input);
}

// Processar alergias (pode ser array ou string)
$alergias_input = $_POST['alergias'] ?? '';
if (is_array($alergias_input)) {
    $alergias_filtradas = array_filter(array_map('trim', $alergias_input), function($a) {
        return !empty($a) && $a !== '';
    });
    $alergias = !empty($alergias_filtradas) ? implode(', ', $alergias_filtradas) : '';
} else {
    $alergias = trim($alergias_input);
}

// Processar medicações (pode ser array ou string)
$medicacao_input = $_POST['medicacao'] ?? '';
if (is_array($medicacao_input)) {
    $medicacoes_filtradas = array_filter(array_map('trim', $medicacao_input), function($m) {
        return !empty($m);
    });
    $medicacao_continua = !empty($medicacoes_filtradas) ? implode(', ', $medicacoes_filtradas) : '';
} else {
    $medicacao_continua = trim($medicacao_input);
}

// Processar doença mental (pode ser array ou string)
$doenca_mental_input = $_POST['doenca_mental'] ?? '';
if (is_array($doenca_mental_input)) {
    $doencas_mentais_filtradas = array_filter(array_map('trim', $doenca_mental_input), function($dm) {
        return !empty($dm) && $dm !== '';
    });
    $doenca_mental = !empty($doencas_mentais_filtradas) ? implode(', ', $doencas_mentais_filtradas) : '';
} else {
    $doenca_mental = trim($doenca_mental_input);
}

// Processar dispositivos (pode ser array ou string)
$dispositivo_input = $_POST['dispositivo'] ?? '';
if (is_array($dispositivo_input)) {
    $dispositivos_filtrados = array_filter(array_map('trim', $dispositivo_input), function($d) {
        return !empty($d) && $d !== '';
    });
    $dispositivo_implantado = !empty($dispositivos_filtrados) ? implode(', ', $dispositivos_filtrados) : '';
} else {
    $dispositivo_implantado = trim($dispositivo_input);
}

$info_relevantes = trim($_POST['info_relevantes'] ?? '');
$cirurgias = trim($_POST['cirurgias'] ?? '');

// Configurações de privacidade
$autorizacao_usuario = $_POST['autorizacao_usuario'] ?? 'nao';

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

    // Processar upload de foto
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/fotos/';
        
        // Criar diretório se não existir
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['foto_perfil'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Validar extensão
        if (!in_array($file_ext, $allowed_exts)) {
            throw new Exception("Formato de arquivo não permitido. Use JPG, PNG ou GIF.");
        }
        
        // Validar tamanho (máx 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("Arquivo muito grande. Tamanho máximo: 2MB.");
        }
        
        // Gerar nome único
        $foto_perfil = uniqid('foto_') . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $foto_perfil;
        
        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception("Erro ao fazer upload da foto.");
        }
        
        // Se houver foto antiga, deletá-la (só se a coluna existir)
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos LIKE 'foto_perfil'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("SELECT foto_perfil FROM perfis_medicos WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
                $foto_antiga = $stmt->fetchColumn();
                if ($foto_antiga && file_exists($upload_dir . $foto_antiga)) {
                    unlink($upload_dir . $foto_antiga);
                }
            }
        } catch(PDOException $e) {
            // Ignorar erro se a coluna não existir
        }
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

    // Verificar se a coluna foto_perfil existe
    $coluna_foto_existe = false;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos LIKE 'foto_perfil'");
        $coluna_foto_existe = $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        // Se der erro, assumir que não existe
        $coluna_foto_existe = false;
    }
    
    // Verificar se a coluna autorizacao_usuario existe
    $coluna_autorizacao_existe = false;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos LIKE 'autorizacao_usuario'");
        $coluna_autorizacao_existe = $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        // Se der erro, assumir que não existe
        $coluna_autorizacao_existe = false;
    }

    if ($perfil_existente) {
        // Atualizar perfil existente
        $set_parts = [
            'data_nascimento = ?',
            'sexo = ?',
            'cpf = ?',
            'telefone = ?',
            'email = ?',
            'tipo_sanguineo = ?',
            'doencas_cronicas = ?',
            'alergias = ?',
            'medicacao_continua = ?',
            'doenca_mental = ?',
            'dispositivo_implantado = ?',
            'info_relevantes = ?',
            'cirurgias = ?'
        ];
        
        $valores_update = [
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
        ];
        
        if ($foto_perfil && $coluna_foto_existe) {
            $set_parts[] = 'foto_perfil = ?';
            $valores_update[] = $foto_perfil;
        }
        
        if ($coluna_autorizacao_existe) {
            $set_parts[] = 'autorizacao_usuario = ?';
            $valores_update[] = $autorizacao_usuario;
        }
        
        $set_parts[] = 'data_atualizacao = CURRENT_TIMESTAMP';
        $valores_update[] = $usuario_id;
        
        $set_clause = implode(', ', $set_parts);
        
        $stmt = $pdo->prepare("
            UPDATE perfis_medicos 
            SET $set_clause
            WHERE usuario_id = ?
        ");
        $stmt->execute($valores_update);
    } else {
        // Inserir novo perfil
        $campos_insert = ['usuario_id', 'data_nascimento', 'sexo', 'cpf', 'telefone', 'email', 'tipo_sanguineo', 
                         'doencas_cronicas', 'alergias', 'medicacao_continua', 'doenca_mental', 
                         'dispositivo_implantado', 'info_relevantes', 'cirurgias'];
        $valores_insert = [$usuario_id, $data_nascimento_formatada, $sexo, $cpf_formatado, $telefone, $email,
                          $tipo_sanguineo, $doencas_cronicas ?: null, $alergias ?: null, $medicacao_continua ?: null,
                          $doenca_mental ?: null, $dispositivo_implantado ?: null, $info_relevantes ?: null, $cirurgias ?: null];
        
        if ($coluna_foto_existe) {
            $campos_insert[] = 'foto_perfil';
            $valores_insert[] = $foto_perfil;
        }
        
        if ($coluna_autorizacao_existe) {
            $campos_insert[] = 'autorizacao_usuario';
            $valores_insert[] = $autorizacao_usuario;
        }
        
        $placeholders = str_repeat('?, ', count($valores_insert) - 1) . '?';
        $campos_str = implode(', ', $campos_insert);
        
        $stmt = $pdo->prepare("
            INSERT INTO perfis_medicos ($campos_str) 
            VALUES ($placeholders)
        ");
        $stmt->execute($valores_insert);
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
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Mensagem de erro mais amigável
    $mensagem_erro = "Erro ao salvar perfil.";
    if (strpos($e->getMessage(), 'foto_perfil') !== false) {
        $mensagem_erro = "Erro: A coluna 'foto_perfil' não existe na tabela. Execute o script SQL 'adicionar_campo_foto.sql' no banco de dados.";
    } else {
        $mensagem_erro = "Erro ao salvar perfil: " . $e->getMessage();
    }
    
    $_SESSION['erros'] = [$mensagem_erro];
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

