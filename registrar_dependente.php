<?php
require_once 'config.php';
require_once 'verificar_login.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form_dependentes.php');
    exit;
}

// Verificar se o usuário está logado e é paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'paciente') {
    $_SESSION['erro'] = "Apenas pacientes podem cadastrar dependentes.";
    header('Location: dependentes.php');
    exit;
}

$paciente_id = $_SESSION['usuario_id'];
$dependente_id = isset($_POST['dependente_id']) ? (int)$_POST['dependente_id'] : null;
$editar = $dependente_id !== null;

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

// Processar doenças crônicas (array)
$doencas_array = $_POST['doencas'] ?? [];
$outras_doencas = $_POST['outraDoenca'] ?? [];
$doencas_lista = [];

if (is_array($doencas_array)) {
    foreach ($doencas_array as $index => $doenca) {
        $doenca = trim($doenca);
        if (!empty($doenca) && $doenca !== '') {
            if ($doenca === 'outra_nao_listada') {
                // Se for "outra", usar o texto do campo correspondente
                $outra_texto = isset($outras_doencas[$index]) ? trim($outras_doencas[$index]) : '';
                if (!empty($outra_texto)) {
                    $doencas_lista[] = $outra_texto;
                }
            } else {
                // Mapear valores para nomes legíveis
                $doencas_nomes = [
                    'hipertensao' => 'Hipertensão arterial',
                    'insuficiencia_cardiaca' => 'Insuficiência cardíaca',
                    'arritmias_cronicas' => 'Arritmias crônicas',
                    'doenca_arterial_coronariana' => 'Doença arterial coronariana',
                    'aterosclerose' => 'Aterosclerose',
                    'doenca_vascular_periferica' => 'Doença vascular periférica',
                    'diabetes_tipo1' => 'Diabetes tipo 1',
                    'diabetes_tipo2' => 'Diabetes tipo 2',
                    'hipotireoidismo' => 'Hipotireoidismo',
                    'hipertireoidismo' => 'Hipertireoidismo',
                    'obesidade_cronica' => 'Obesidade crônica',
                    'sindrome_metabolica' => 'Síndrome metabólica',
                    'asma' => 'Asma',
                    'dpoc' => 'DPOC (Doença Pulmonar Obstrutiva Crônica)',
                    'bronquite_cronica' => 'Bronquite crônica',
                    'enfisema' => 'Enfisema',
                    'fibrose_pulmonar' => 'Fibrose pulmonar',
                    'artrite_reumatoide' => 'Artrite reumatoide',
                    'lupus' => 'Lúpus (LES)',
                    'psoriase' => 'Psoríase',
                    'doenca_celiaca' => 'Doença celíaca',
                    'tireoidite_hashimoto' => 'Tireoidite de Hashimoto',
                    'doenca_de_crohn' => 'Doença de Crohn',
                    'retocolite_ulcerativa' => 'Retocolite ulcerativa',
                    'epilepsia' => 'Epilepsia',
                    'enxaqueca_cronica' => 'Enxaqueca crônica',
                    'doenca_de_parkinson' => 'Doença de Parkinson',
                    'esclerose_multipla' => 'Esclerose múltipla',
                    'neuropatias_perifericas' => 'Neuropatias periféricas',
                    'artrose_osteoartrite' => 'Artrose / Osteoartrite',
                    'fibromialgia' => 'Fibromialgia',
                    'lombalgia_cronica' => 'Lombalgia crônica',
                    'osteoporose' => 'Osteoporose',
                    'hepatite_cronica' => 'Hepatite crônica',
                    'cirrose' => 'Cirrose',
                    'esteatose_hepatica_cronica' => 'Esteatose hepática (gordura no fígado) crônica',
                    'doenca_renal_cronica' => 'Doença renal crônica',
                    'insuficiencia_renal' => 'Insuficiência renal',
                    'refluxo_gastroesofagico_cronico' => 'Refluxo gastroesofágico crônico (GERD)',
                    'sindrome_do_intestino_irritavel' => 'Síndrome do intestino irritável (SII)',
                    'gastrite_cronica' => 'Gastrite crônica',
                    'cancer' => 'Câncer (em acompanhamento ou histórico)',
                    'hiv' => 'HIV',
                    'doencas_hematologicas' => 'Doenças hematológicas'
                ];
                $doencas_lista[] = $doencas_nomes[$doenca] ?? $doenca;
            }
        }
    }
}
$doencas_cronicas = !empty($doencas_lista) ? implode(', ', $doencas_lista) : '';

// Processar medicações (array)
$medicacoes_array = $_POST['medicacao'] ?? [];
$medicacao_continua = '';
if (is_array($medicacoes_array)) {
    $medicacoes_filtradas = array_filter(array_map('trim', $medicacoes_array), function($m) {
        return !empty($m);
    });
    $medicacao_continua = !empty($medicacoes_filtradas) ? implode(', ', $medicacoes_filtradas) : '';
}

// Processar alergias (array)
$alergias_array = $_POST['alergias'] ?? [];
$descricoes_alergias = $_POST['descricaoAlergia'] ?? [];
$alergias_lista = [];

if (is_array($alergias_array)) {
    foreach ($alergias_array as $index => $alergia) {
        $alergia = trim($alergia);
        if (!empty($alergia) && $alergia !== '') {
            // Mapear valores para nomes legíveis
            $alergias_nomes = [
                'alimentar' => 'Alergia alimentar',
                'medicamentos' => 'Alergia medicamentosa',
                'respiratoria' => 'Alergia respiratória',
                'dermatologica' => 'Alergia dermatológica',
                'inseto' => 'Alergia a picada de inseto',
                'quimica' => 'Alergia química',
                'fisica' => 'Alergia física',
                'outra' => 'Outra'
            ];
            
            $tipo_alergia = $alergias_nomes[$alergia] ?? $alergia;
            
            // Se houver descrição, adicionar
            $descricao = isset($descricoes_alergias[$index]) ? trim($descricoes_alergias[$index]) : '';
            if (!empty($descricao)) {
                $alergias_lista[] = $tipo_alergia . ': ' . $descricao;
            } else {
                $alergias_lista[] = $tipo_alergia;
            }
        }
    }
}
$alergias = !empty($alergias_lista) ? implode('; ', $alergias_lista) : '';

// Processar doenças mentais (array)
$doencas_mentais_array = $_POST['doenca_mental'] ?? [];
$outras_doencas_mentais = $_POST['outraDoencaMental'] ?? [];
$doencas_mentais_lista = [];

if (is_array($doencas_mentais_array)) {
    foreach ($doencas_mentais_array as $index => $doenca_mental) {
        $doenca_mental = trim($doenca_mental);
        if (!empty($doenca_mental) && $doenca_mental !== '') {
            if ($doenca_mental === 'outra') {
                // Se for "outra", usar o texto do campo correspondente
                $outra_texto = isset($outras_doencas_mentais[$index]) ? trim($outras_doencas_mentais[$index]) : '';
                if (!empty($outra_texto)) {
                    $doencas_mentais_lista[] = $outra_texto;
                }
            } else {
                // Mapear valores para nomes legíveis
                $doencas_mentais_nomes = [
                    'depressao' => 'Depressão',
                    'ansiedade' => 'Transtorno de Ansiedade',
                    'bipolaridade' => 'Transtorno Bipolar',
                    'esquizofrenia' => 'Esquizofrenia',
                    'tdah' => 'TDAH (Transtorno do Déficit de Atenção e Hiperatividade)',
                    'toc' => 'TOC (Transtorno Obsessivo-Compulsivo)',
                    'transtorno_estresse_pos_traumatico' => 'Transtorno de Estresse Pós-Traumático'
                ];
                $doencas_mentais_lista[] = $doencas_mentais_nomes[$doenca_mental] ?? $doenca_mental;
            }
        }
    }
}
$doenca_mental = !empty($doencas_mentais_lista) ? implode(', ', $doencas_mentais_lista) : '';

// Processar dispositivos implantados (array)
$dispositivos_array = $_POST['dispositivo'] ?? [];
$outros_dispositivos = $_POST['outroDispositivo'] ?? [];
$dispositivos_lista = [];

if (is_array($dispositivos_array)) {
    foreach ($dispositivos_array as $index => $dispositivo) {
        $dispositivo = trim($dispositivo);
        if (!empty($dispositivo) && $dispositivo !== '') {
            if ($dispositivo === 'outro') {
                // Se for "outro", usar o texto do campo correspondente
                $outro_texto = isset($outros_dispositivos[$index]) ? trim($outros_dispositivos[$index]) : '';
                if (!empty($outro_texto)) {
                    $dispositivos_lista[] = $outro_texto;
                }
            } else {
                // Mapear valores para nomes legíveis
                $dispositivos_nomes = [
                    'marca_passo' => 'Marca-passo',
                    'stent_cardiaco' => 'Stent cardíaco',
                    'valvula_cardiaca' => 'Prótese de válvula cardíaca',
                    'derivacao_cerebral' => 'Derivação ventricular (shunt)',
                    'implante_cochlear' => 'Implante coclear',
                    'proteses_ortopedicas' => 'Próteses ortopédicas',
                    'dispositivo_contraceptivo' => 'Dispositivo contraceptivo'
                ];
                $dispositivos_lista[] = $dispositivos_nomes[$dispositivo] ?? $dispositivo;
            }
        }
    }
}
$dispositivo_implantado = !empty($dispositivos_lista) ? implode(', ', $dispositivos_lista) : '';
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
    header('Location: form_dependentes.php');
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
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Formatar data de nascimento
    $data_nascimento_formatada = $data_nascimento ? date('Y-m-d', strtotime($data_nascimento)) : null;
    
    // Formatar CPF (com máscara)
    $cpf_formatado = !empty($cpf) ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2) : null;

    if ($editar) {
        // Verificar se o dependente pertence ao paciente
        $stmt = $pdo->prepare("SELECT id FROM dependentes WHERE id = ? AND paciente_id = ?");
        $stmt->execute([$dependente_id, $paciente_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Dependente não encontrado ou não pertence a você.");
        }
        
        // Buscar foto antiga se houver
        $stmt = $pdo->prepare("SELECT foto_perfil FROM dependentes WHERE id = ?");
        $stmt->execute([$dependente_id]);
        $foto_antiga = $stmt->fetchColumn();
        
        // Atualizar dependente
        if ($foto_perfil) {
            $stmt = $pdo->prepare("
                UPDATE dependentes 
                SET nome = ?, data_nascimento = ?, sexo = ?, cpf = ?, 
                    telefone = ?, email = ?, foto_perfil = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $nome, $data_nascimento_formatada, $sexo, $cpf_formatado,
                $telefone, $email, $foto_perfil, $dependente_id
            ]);
            
            // Deletar foto antiga se houver
            if ($foto_antiga && file_exists('uploads/fotos/' . $foto_antiga)) {
                unlink('uploads/fotos/' . $foto_antiga);
            }
        } else {
            $stmt = $pdo->prepare("
                UPDATE dependentes 
                SET nome = ?, data_nascimento = ?, sexo = ?, cpf = ?, 
                    telefone = ?, email = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $nome, $data_nascimento_formatada, $sexo, $cpf_formatado,
                $telefone, $email, $dependente_id
            ]);
        }
    } else {
        // Verificar se já existe dependente com mesmo CPF (se fornecido)
        if (!empty($cpf_formatado)) {
            $stmt = $pdo->prepare("SELECT id FROM dependentes WHERE cpf = ? AND paciente_id = ?");
            $stmt->execute([$cpf_formatado, $paciente_id]);
            if ($stmt->fetch()) {
                throw new Exception("Já existe um dependente cadastrado com este CPF.");
            }
        }

        // Inserir dependente
        $stmt = $pdo->prepare("
            INSERT INTO dependentes 
            (paciente_id, nome, data_nascimento, sexo, cpf, telefone, email, foto_perfil) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $paciente_id,
            $nome,
            $data_nascimento_formatada,
            $sexo,
            $cpf_formatado,
            $telefone,
            $email,
            $foto_perfil
        ]);

        $dependente_id = $pdo->lastInsertId();
    }

    // Verificar se já existe perfil médico
    $stmt = $pdo->prepare("SELECT id FROM perfis_medicos WHERE dependente_id = ?");
    $stmt->execute([$dependente_id]);
    $perfil_existente = $stmt->fetch();
    
    // Verificar se a coluna autorizacao_usuario existe
    $coluna_autorizacao_existe = false;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos LIKE 'autorizacao_usuario'");
        $coluna_autorizacao_existe = $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        $coluna_autorizacao_existe = false;
    }
    
    if ($perfil_existente) {
        // Atualizar perfil médico
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
            $data_nascimento_formatada, $sexo, $cpf_formatado, $telefone, $email,
            $tipo_sanguineo, $doencas_cronicas ?: null, $alergias ?: null,
            $medicacao_continua ?: null, $doenca_mental ?: null, $dispositivo_implantado ?: null,
            $info_relevantes ?: null, $cirurgias ?: null
        ];
        
        if ($foto_perfil) {
            $set_parts[] = 'foto_perfil = ?';
            $valores_update[] = $foto_perfil;
        }
        
        if ($coluna_autorizacao_existe) {
            $set_parts[] = 'autorizacao_usuario = ?';
            $valores_update[] = $autorizacao_usuario;
        }
        
        $set_parts[] = 'data_atualizacao = CURRENT_TIMESTAMP';
        $valores_update[] = $dependente_id;
        
        $set_clause = implode(', ', $set_parts);
        
        $stmt = $pdo->prepare("
            UPDATE perfis_medicos 
            SET $set_clause
            WHERE dependente_id = ?
        ");
        $stmt->execute($valores_update);
    } else {
        // Inserir perfil médico do dependente
        $campos_insert = ['dependente_id', 'data_nascimento', 'sexo', 'cpf', 'telefone', 'email', 'tipo_sanguineo', 
                         'doencas_cronicas', 'alergias', 'medicacao_continua', 'doenca_mental', 
                         'dispositivo_implantado', 'info_relevantes', 'cirurgias'];
        $valores_insert = [
            $dependente_id,
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
        
        if ($foto_perfil) {
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
    $stmt = $pdo->prepare("SELECT id FROM contatos_emergencia WHERE dependente_id = ?");
    $stmt->execute([$dependente_id]);
    $contato_existente = $stmt->fetch();
    
    if ($contato_existente) {
        // Atualizar contato de emergência
        $stmt = $pdo->prepare("
            UPDATE contatos_emergencia 
            SET nome = ?, parentesco = ?, telefone = ? 
            WHERE dependente_id = ?
        ");
        $stmt->execute([$contato_nome, $parentesco, $contato_telefone, $dependente_id]);
    } else {
        // Inserir contato de emergência do dependente
        $stmt = $pdo->prepare("
            INSERT INTO contatos_emergencia (dependente_id, nome, parentesco, telefone) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$dependente_id, $contato_nome, $parentesco, $contato_telefone]);
    }

    // Confirmar transação
    $pdo->commit();

    // Sucesso
    $_SESSION['sucesso'] = $editar ? "Dependente atualizado com sucesso!" : "Dependente cadastrado com sucesso!";
    header('Location: dependentes.php');
    exit;

} catch(PDOException $e) {
    // Reverter transação em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['erros'] = ["Erro ao cadastrar dependente: " . $e->getMessage()];
    $_SESSION['dados_form'] = $_POST;
    header('Location: form_dependentes.php');
    exit;
} catch(Exception $e) {
    $_SESSION['erros'] = ["Erro: " . $e->getMessage()];
    $_SESSION['dados_form'] = $_POST;
    header('Location: form_dependentes.php');
    exit;
}
?>

