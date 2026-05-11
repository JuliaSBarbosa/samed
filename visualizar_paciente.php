<<<<<<< HEAD
﻿<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_tipo = $_SESSION['usuario_tipo'] ?? '';
$eh_profissional = in_array($usuario_tipo, ['medico', 'enfermeiro']);
$eh_paciente = $usuario_tipo === 'paciente';

$paciente_encontrado = false;
$perfil = null;
$dependente = null;
$contato_emergencia = null;
$dados_paciente = null;
$cpf_busca = '';
$codigo_pulseira = '';
$id_ficha = null;
$autorizacao_usuario = 'nao';
$pode_ver_dados_completos = false;
$pode_ver_dados_basicos = false;
$pode_acessar = false;
$nome_paciente = '';

if ($pdo && $usuario_id) {
    if (isset($_GET['id_ficha']) && is_numeric($_GET['id_ficha'])) {
        $id_ficha = (int) $_GET['id_ficha'];
    } elseif (isset($_GET['codigo_pulseira'])) {
        $codigo_pulseira = trim($_GET['codigo_pulseira']);
        if (is_numeric($codigo_pulseira)) {
            $id_ficha = (int) $codigo_pulseira;
        }
    } elseif (isset($_GET['cpf'])) {
        $cpf_busca = trim($_GET['cpf']);
    }

    try {
        if ($id_ficha) {
            $stmt = $pdo->prepare("SELECT pm.*, u.nome AS nome_usuario, u.id AS usuario_id FROM perfis_medicos pm LEFT JOIN usuarios u ON pm.usuario_id = u.id WHERE pm.id = ?");
            $stmt->execute([$id_ficha]);
            $perfil = $stmt->fetch();
        } elseif ($cpf_busca) {
            $stmt = $pdo->prepare("SELECT pm.*, u.nome AS nome_usuario, u.id AS usuario_id FROM perfis_medicos pm LEFT JOIN usuarios u ON pm.usuario_id = u.id WHERE pm.cpf = ?");
            $stmt->execute([$cpf_busca]);
            $perfil = $stmt->fetch();
        }

        if ($perfil) {
            $paciente_encontrado = true;

            if (!empty($perfil['dependente_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM dependentes WHERE id = ?");
                $stmt->execute([$perfil['dependente_id']]);
                $dependente = $stmt->fetch();

                $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE dependente_id = ?");
                $stmt->execute([$perfil['dependente_id']]);
                $contato_emergencia = $stmt->fetch();

                $nome_paciente = $dependente['nome'] ?? $perfil['nome_usuario'] ?? '';
                $usuario_id_paciente = $dependente['paciente_id'] ?? null;
            } else {
                $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE usuario_id = ?");
                $stmt->execute([$perfil['usuario_id']]);
                $contato_emergencia = $stmt->fetch();

                $nome_paciente = $perfil['nome_usuario'] ?? '';
                $usuario_id_paciente = $perfil['usuario_id'] ?? null;
            }

            $autorizacao_usuario = $perfil['autorizacao_usuario'] ?? 'nao';
            $eh_dono = $usuario_id === $usuario_id_paciente || ($dependente && $usuario_id === ($dependente['paciente_id'] ?? null));
            $pode_ver_dados_completos = $eh_profissional || $eh_dono;
            $pode_ver_dados_basicos = $pode_ver_dados_completos || ($eh_paciente && $autorizacao_usuario === 'sim');
            $pode_acessar = $pode_ver_dados_basicos || $pode_ver_dados_completos;

            $dados_paciente = [
                'nome' => $nome_paciente,
                'idade' => $perfil['data_nascimento'] ? (new DateTime($perfil['data_nascimento']))->diff(new DateTime())->y : null,
                'data_nascimento' => $perfil['data_nascimento'] ? date('d/m/Y', strtotime($perfil['data_nascimento'])) : '',
                'sexo' => ucfirst($perfil['sexo'] ?? ''),
                'cpf' => $perfil['cpf'] ?? '',
                'telefone' => $perfil['telefone'] ?? '',
                'email' => $perfil['email'] ?? '',
                'cep' => $perfil['cep'] ?? '',
                'rua' => $perfil['rua'] ?? '',
                'numero' => $perfil['numero'] ?? '',
                'complemento' => $perfil['complemento'] ?? '',
                'bairro' => $perfil['bairro'] ?? '',
                'cidade' => $perfil['cidade'] ?? '',
                'estado' => $perfil['estado'] ?? '',
                'contato_emergencia' => $contato_emergencia['nome'] ?? '',
                'parentesco' => $contato_emergencia['parentesco'] ?? '',
                'telefone_emergencia' => $contato_emergencia['telefone'] ?? '',
                'doencas_cronicas' => $pode_ver_dados_completos ? ($perfil['doencas_cronicas'] ?? '') : '',
                'alergias' => $pode_ver_dados_completos ? ($perfil['alergias'] ?? '') : '',
                'tipo_sanguineo' => $pode_ver_dados_completos ? ($perfil['tipo_sanguineo'] ?? '') : '',
                'fuma' => $pode_ver_dados_completos ? ($perfil['fuma'] ?? '') : '',
                'bebe' => $pode_ver_dados_completos ? ($perfil['bebe'] ?? '') : '',
                'medicacoes' => $pode_ver_dados_completos ? ($perfil['medicacao_continua'] ?? '') : '',
                'doenca_mental' => $pode_ver_dados_completos ? ($perfil['doenca_mental'] ?? 'Não') : '',
                'dispositivos' => $pode_ver_dados_completos ? ($perfil['dispositivo_implantado'] ?? '') : '',
                'informacoes_relevantes' => $pode_ver_dados_completos ? ($perfil['info_relevantes'] ?? '') : '',
                'historico_cirurgias' => $pode_ver_dados_completos ? ($perfil['cirurgias'] ?? '') : '',
                'foto_perfil' => $perfil['foto_perfil'] ?? null
            ];

            if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $usuario_id_paciente) {
                $visualizador_id = $_SESSION['usuario_id'];
                $tipo_acesso = 'Consulta';
                $registro_profissional = '';

                if ($eh_profissional) {
                    if ($_SESSION['usuario_tipo'] === 'medico') {
                        $registro_profissional = 'MÉDICO';
                    } elseif ($_SESSION['usuario_tipo'] === 'enfermeiro') {
                        $registro_profissional = 'ENFERMEIRO';
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO historico_acessos (profissional_id, paciente_id, dependente_id, tipo_acesso, registro_profissional) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $visualizador_id,
                    !$dependente ? $usuario_id_paciente : null,
                    $dependente['id'] ?? null,
                    $tipo_acesso,
                    $registro_profissional
                ]);
            }
        }
    } catch (PDOException $e) {
        error_log('Erro ao carregar ficha médica: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Visualizar Ficha Médica</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
</head>
<body>
=======
<?php
require_once 'verificar_login.php';
require_once 'config.php';

/**
 * Formata valores de doenças, alergias, dispositivos etc. removendo underscores e convertendo para nomes legíveis
 */
function formatarNomeLegivel($valor) {
    if (empty($valor)) {
        return $valor;
    }
    
    // Se já estiver formatado (sem underscores), retornar como está
    if (strpos($valor, '_') === false) {
        return $valor;
    }
    
    // Mapeamento de códigos para nomes legíveis
    $mapeamento = [
        // Doenças crônicas
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
        'doencas_hematologicas' => 'Doenças hematológicas',
        'outra_nao_listada' => 'Outra doença não listada',
        
        // Alergias
        'alimentar' => 'Alergia alimentar',
        'medicamentos' => 'Alergia medicamentosa',
        'respiratoria' => 'Alergia respiratória',
        'dermatologica' => 'Alergia dermatológica',
        'inseto' => 'Alergia a picada de inseto',
        'quimica' => 'Alergia química',
        'fisica' => 'Alergia física',
        'outra' => 'Outra',
        
        // Doenças mentais
        'depressao' => 'Depressão',
        'ansiedade' => 'Transtorno de Ansiedade',
        'bipolaridade' => 'Transtorno Bipolar',
        'esquizofrenia' => 'Esquizofrenia',
        'tdah' => 'TDAH (Transtorno do Déficit de Atenção e Hiperatividade)',
        'toc' => 'TOC (Transtorno Obsessivo-Compulsivo)',
        'transtorno_estresse_pos_traumatico' => 'Transtorno de Estresse Pós-Traumático',
        
        // Dispositivos
        'marca_passo' => 'Marca-passo',
        'stent_cardiaco' => 'Stent cardíaco',
        'valvula_cardiaca' => 'Prótese de válvula cardíaca',
        'derivacao_cerebral' => 'Derivação ventricular (shunt)',
        'implante_cochlear' => 'Implante coclear',
        'proteses_ortopedicas' => 'Próteses ortopédicas',
        'dispositivo_contraceptivo' => 'Dispositivo contraceptivo',
    ];
    
    // Se o valor contém vírgulas ou ponto e vírgula, processar cada item
    if (strpos($valor, ',') !== false || strpos($valor, ';') !== false) {
        $separador = strpos($valor, ';') !== false ? ';' : ',';
        $itens = explode($separador, $valor);
        $itens_formatados = [];
        
        foreach ($itens as $item) {
            $item = trim($item);
            if (!empty($item)) {
                // Verificar se há dois pontos (ex: "Alergia alimentar: descrição")
                if (strpos($item, ':') !== false) {
                    $partes = explode(':', $item, 2);
                    $tipo = trim($partes[0]);
                    $descricao = trim($partes[1]);
                    
                    // Formatar o tipo
                    $tipo_formatado = $mapeamento[$tipo] ?? ucwords(str_replace('_', ' ', $tipo));
                    $itens_formatados[] = $tipo_formatado . ': ' . $descricao;
                } else {
                    // Verificar se está no mapeamento
                    if (isset($mapeamento[$item])) {
                        $itens_formatados[] = $mapeamento[$item];
                    } else {
                        // Se não estiver no mapeamento, formatar removendo underscores e capitalizando
                        $itens_formatados[] = ucwords(str_replace('_', ' ', $item));
                    }
                }
            }
        }
        
        return implode($separador === ';' ? '; ' : ', ', $itens_formatados);
    }
    
    // Valor único
    if (isset($mapeamento[$valor])) {
        return $mapeamento[$valor];
    }
    
    // Se não estiver no mapeamento, formatar removendo underscores e capitalizando
    return ucwords(str_replace('_', ' ', $valor));
}

// Verificar se é profissional de saúde
$eh_profissional = false;
if (isset($_SESSION['usuario_tipo']) && in_array($_SESSION['usuario_tipo'], ['medico', 'enfermeiro'])) {
    $eh_profissional = true;
}

// Verificar se é usuário comum (paciente)
$eh_paciente = isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'paciente';

if (!$eh_profissional && !$eh_paciente) {
    header('Location: index.php');
    exit;
}

// Receber ID da ficha, CPF ou código da pulseira
$id_ficha = isset($_GET['id_ficha']) ? (int)$_GET['id_ficha'] : null;
$cpf_busca = isset($_GET['cpf']) ? preg_replace('/[^0-9]/', '', $_GET['cpf']) : '';
$codigo_pulseira = $_GET['codigo_pulseira'] ?? '';

if (empty($id_ficha) && empty($cpf_busca) && empty($codigo_pulseira)) {
    if ($eh_profissional) {
        header('Location: inicio-med.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$paciente_encontrado = false;
$dados_paciente = null;
$perfil_id = null;
$usuario_id_paciente = null;
$dependente_id = null;
$eh_dependente = false;
$autorizacao_usuario = 'nao';

if ($pdo) {
    try {
        // Buscar por CPF primeiro (prioridade)
        if (!empty($cpf_busca)) {
            // Buscar perfil médico por CPF
            $stmt = $pdo->prepare("
                SELECT pm.*, u.nome as nome_usuario, u.id as usuario_id_paciente, pm.dependente_id
                FROM perfis_medicos pm
                LEFT JOIN usuarios u ON pm.usuario_id = u.id
                WHERE REPLACE(REPLACE(REPLACE(pm.cpf, '.', ''), '-', ''), ' ', '') = ?
                ORDER BY pm.id DESC
                LIMIT 1
            ");
            $stmt->execute([$cpf_busca]);
            $perfil = $stmt->fetch();
            
            if ($perfil) {
                $paciente_encontrado = true;
                $perfil_id = $perfil['id'];
                $usuario_id_paciente = $perfil['usuario_id_paciente'];
                $dependente_id = $perfil['dependente_id'];
                $eh_dependente = !empty($dependente_id);
                
                // Buscar contato de emergência e dados
                $contato_emergencia = null;
                if ($eh_dependente) {
                    // Se for dependente, buscar o paciente_id (titular) do dependente
                    $stmt = $pdo->prepare("SELECT paciente_id FROM dependentes WHERE id = ?");
                    $stmt->execute([$dependente_id]);
                    $dependente_data = $stmt->fetch();
                    
                    // Usar o paciente_id do dependente como usuario_id_paciente para o histórico
                    if ($dependente_data && $dependente_data['paciente_id']) {
                        $usuario_id_paciente = $dependente_data['paciente_id'];
                    }
                    
                    $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE dependente_id = ?");
                    $stmt->execute([$dependente_id]);
                    $contato_emergencia = $stmt->fetch();
                    
                    $stmt = $pdo->prepare("SELECT * FROM dependentes WHERE id = ?");
                    $stmt->execute([$dependente_id]);
                    $dependente = $stmt->fetch();
                    $nome_paciente = $dependente['nome'] ?? $perfil['nome_usuario'];
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE usuario_id = ?");
                    $stmt->execute([$usuario_id_paciente]);
                    $contato_emergencia = $stmt->fetch();
                    $nome_paciente = $perfil['nome_usuario'];
                }
                
                // Calcular idade
                $idade = null;
                if ($perfil['data_nascimento']) {
                    $data_nasc = new DateTime($perfil['data_nascimento']);
                    $hoje = new DateTime();
                    $idade = $hoje->diff($data_nasc)->y;
                }
                
                $autorizacao_usuario = $perfil['autorizacao_usuario'] ?? 'nao';
                $pode_ver_dados_completos = $eh_profissional;
                $pode_ver_dados_basicos = $eh_profissional || ($eh_paciente && $autorizacao_usuario === 'sim');
                
                // Verificar se pode acessar antes de registrar
                $pode_acessar = $pode_ver_dados_basicos || $pode_ver_dados_completos;
                
                // Montar dados do paciente
                $dados_paciente = [
                    'nome' => $nome_paciente,
                    'idade' => $idade,
                    'data_nascimento' => $perfil['data_nascimento'] ? date('d/m/Y', strtotime($perfil['data_nascimento'])) : '',
                    'sexo' => ucfirst($perfil['sexo'] ?? ''),
                    'cpf' => $perfil['cpf'] ?? '',
                    'telefone' => $perfil['telefone'] ?? '',
                    'email' => $perfil['email'] ?? '',
                    'contato_emergencia' => $contato_emergencia['nome'] ?? '',
                    'parentesco' => $contato_emergencia['parentesco'] ?? '',
                    'telefone_emergencia' => $contato_emergencia['telefone'] ?? '',
                    'doencas_cronicas' => $pode_ver_dados_completos ? ($perfil['doencas_cronicas'] ?? '') : '',
                    'alergias' => $pode_ver_dados_completos ? ($perfil['alergias'] ?? '') : '',
                    'tipo_sanguineo' => $pode_ver_dados_completos ? ($perfil['tipo_sanguineo'] ?? '') : '',
                    'medicacoes' => $pode_ver_dados_completos ? ($perfil['medicacao_continua'] ?? '') : '',
                    'doenca_mental' => $pode_ver_dados_completos ? ($perfil['doenca_mental'] ?? 'Não') : '',
                    'dispositivos' => $pode_ver_dados_completos ? ($perfil['dispositivo_implantado'] ?? '') : '',
                    'informacoes_relevantes' => $pode_ver_dados_completos ? ($perfil['info_relevantes'] ?? '') : '',
                    'historico_cirurgias' => $pode_ver_dados_completos ? ($perfil['cirurgias'] ?? '') : '',
                    'foto_perfil' => $perfil['foto_perfil'] ?? null
                ];
                
                // Registrar acesso no histórico (sempre registra, tanto quando permitido quanto quando bloqueado)
                if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $usuario_id_paciente) {
                    $visualizador_id = $_SESSION['usuario_id'];
                    $tipo_acesso = 'Consulta';
                    $registro_profissional = '';
                    
                    if ($eh_profissional) {
                        if ($_SESSION['usuario_tipo'] === 'medico') {
                            $registro_profissional = $_SESSION['usuario_crm'] ?? '';
                            $tipo_acesso = 'Consulta Médica';
                        } elseif ($_SESSION['usuario_tipo'] === 'enfermeiro') {
                            $registro_profissional = $_SESSION['usuario_coren'] ?? '';
                            $tipo_acesso = 'Consulta Enfermagem';
                        }
                    } else {
                        $tipo_acesso = 'Consulta por Usuário';
                    }
                    
                    // Se acesso foi bloqueado, registrar como tentativa bloqueada
                    if (!$pode_acessar && $eh_paciente && $autorizacao_usuario === 'nao') {
                        $tipo_acesso = 'Tentativa de Acesso Bloqueada - Usuário não autorizou compartilhamento';
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO historico_acessos 
                        (profissional_id, paciente_id, dependente_id, tipo_acesso, registro_profissional)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $visualizador_id,
                        $usuario_id_paciente,
                        $eh_dependente ? $dependente_id : null,
                        $tipo_acesso,
                        $registro_profissional
                    ]);
                }
            }
        }
        // Buscar por ID da ficha
        elseif ($id_ficha) {
            $stmt = $pdo->prepare("
                SELECT pm.*, u.nome as nome_usuario, u.id as usuario_id_paciente, pm.dependente_id
                FROM perfis_medicos pm
                LEFT JOIN usuarios u ON pm.usuario_id = u.id
                WHERE pm.id = ?
            ");
            $stmt->execute([$id_ficha]);
            $perfil = $stmt->fetch();
            
            if ($perfil) {
                $paciente_encontrado = true;
                $perfil_id = $id_ficha;
                $usuario_id_paciente = $perfil['usuario_id_paciente'];
                $dependente_id = $perfil['dependente_id'];
                $eh_dependente = !empty($dependente_id);
                
                // Buscar contato de emergência
                $contato_emergencia = null;
                if ($eh_dependente) {
                    // Se for dependente, buscar o paciente_id (titular) do dependente
                    $stmt = $pdo->prepare("SELECT paciente_id FROM dependentes WHERE id = ?");
                    $stmt->execute([$dependente_id]);
                    $dependente_data = $stmt->fetch();
                    
                    // Usar o paciente_id do dependente como usuario_id_paciente para o histórico
                    if ($dependente_data && $dependente_data['paciente_id']) {
                        $usuario_id_paciente = $dependente_data['paciente_id'];
                    }
                    
                    $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE dependente_id = ?");
                    $stmt->execute([$dependente_id]);
                    $contato_emergencia = $stmt->fetch();
                    
                    // Buscar dados do dependente
                    $stmt = $pdo->prepare("SELECT * FROM dependentes WHERE id = ?");
                    $stmt->execute([$dependente_id]);
                    $dependente = $stmt->fetch();
                    $nome_paciente = $dependente['nome'] ?? $perfil['nome_usuario'];
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE usuario_id = ?");
                    $stmt->execute([$usuario_id_paciente]);
                    $contato_emergencia = $stmt->fetch();
                    $nome_paciente = $perfil['nome_usuario'];
                }
                
                // Calcular idade
                $idade = null;
                if ($perfil['data_nascimento']) {
                    $data_nasc = new DateTime($perfil['data_nascimento']);
                    $hoje = new DateTime();
                    $idade = $hoje->diff($data_nasc)->y;
                }
                
                // Verificar autorização para usuários comuns
                $autorizacao_usuario = $perfil['autorizacao_usuario'] ?? 'nao';
                $pode_ver_dados_completos = $eh_profissional; // Profissionais sempre podem ver tudo
                $pode_ver_dados_basicos = $eh_profissional || ($eh_paciente && $autorizacao_usuario === 'sim'); // Usuários comuns só veem se autorizado
                
                // Montar dados do paciente
                $dados_paciente = [
                    'nome' => $nome_paciente,
                    'idade' => $idade,
                    'data_nascimento' => $perfil['data_nascimento'] ? date('d/m/Y', strtotime($perfil['data_nascimento'])) : '',
                    'sexo' => ucfirst($perfil['sexo'] ?? ''),
                    'cpf' => $perfil['cpf'] ?? '',
                    'telefone' => $perfil['telefone'] ?? '',
                    'email' => $perfil['email'] ?? '',
                    'contato_emergencia' => $contato_emergencia['nome'] ?? '',
                    'parentesco' => $contato_emergencia['parentesco'] ?? '',
                    'telefone_emergencia' => $contato_emergencia['telefone'] ?? '',
                    'doencas_cronicas' => $pode_ver_dados_completos ? ($perfil['doencas_cronicas'] ?? '') : '',
                    'alergias' => $pode_ver_dados_completos ? ($perfil['alergias'] ?? '') : '',
                    'tipo_sanguineo' => $pode_ver_dados_completos ? ($perfil['tipo_sanguineo'] ?? '') : '',
                    'medicacoes' => $pode_ver_dados_completos ? ($perfil['medicacao_continua'] ?? '') : '',
                    'doenca_mental' => $pode_ver_dados_completos ? ($perfil['doenca_mental'] ?? 'Não') : '',
                    'dispositivos' => $pode_ver_dados_completos ? ($perfil['dispositivo_implantado'] ?? '') : '',
                    'informacoes_relevantes' => $pode_ver_dados_completos ? ($perfil['info_relevantes'] ?? '') : '',
                    'historico_cirurgias' => $pode_ver_dados_completos ? ($perfil['cirurgias'] ?? '') : '',
                    'foto_perfil' => $perfil['foto_perfil'] ?? null
                ];
                
                // Verificar se pode acessar antes de registrar
                $pode_acessar = $pode_ver_dados_basicos || $pode_ver_dados_completos;
                
                // Registrar acesso no histórico (incluindo tentativas bloqueadas)
                if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $usuario_id_paciente) {
                    $visualizador_id = $_SESSION['usuario_id'];
                    $tipo_acesso = 'Consulta';
                    $registro_profissional = '';
                    
                    if ($eh_profissional) {
                        if ($_SESSION['usuario_tipo'] === 'medico') {
                            $registro_profissional = $_SESSION['usuario_crm'] ?? '';
                            $tipo_acesso = 'Consulta Médica';
                        } elseif ($_SESSION['usuario_tipo'] === 'enfermeiro') {
                            $registro_profissional = $_SESSION['usuario_coren'] ?? '';
                            $tipo_acesso = 'Consulta Enfermagem';
                        }
                    } else {
                        $tipo_acesso = 'Consulta por Usuário';
                    }
                    
                    // Se acesso foi bloqueado, registrar como tentativa bloqueada
                    if (!$pode_acessar && $eh_paciente && $autorizacao_usuario === 'nao') {
                        $tipo_acesso = 'Tentativa de Acesso Bloqueada - Usuário não autorizou compartilhamento';
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO historico_acessos 
                        (profissional_id, paciente_id, dependente_id, tipo_acesso, registro_profissional)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $visualizador_id,
                        $usuario_id_paciente,
                        $eh_dependente ? $dependente_id : null,
                        $tipo_acesso,
                        $registro_profissional
                    ]);
                }
            }
        } elseif (!empty($codigo_pulseira)) {
            // Buscar por código da pulseira (implementação futura)
            // Por enquanto, redirecionar para busca por CPF
            header('Location: inicio-med.php');
            exit;
        }
    } catch(PDOException $e) {
        // Erro ao buscar dados
        error_log("Erro ao buscar paciente: " . $e->getMessage());
    }
}

// Determinar tipo de profissional ou usuário
$tipo_profissional = '';
$registro = '';
if ($_SESSION['usuario_tipo'] === 'medico') {
    $tipo_profissional = 'Médico';
    $registro = $_SESSION['usuario_crm'] ?? '';
} elseif ($_SESSION['usuario_tipo'] === 'enfermeiro') {
    $tipo_profissional = 'Enfermeiro(a)';
    $registro = $_SESSION['usuario_coren'] ?? '';
} elseif ($_SESSION['usuario_tipo'] === 'paciente') {
    $tipo_profissional = 'Paciente';
} else {
    $tipo_profissional = 'Profissional de Saúde';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Dados do Paciente</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
</head>

<body>
    <!-- Cabeçalho -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>

        <nav class="menu">
            <a href="index.php">INÍCIO</a>
            <span class="divisor">|</span>
            <a href="perfil.php">MEU PERFIL</a>
            <span class="divisor">|</span>
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['paciente', 'medico', 'enfermeiro'])): ?>
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <?php endif; ?>
            <a href="historico.php">HISTÓRICO</a>
            <span class="divisor">|</span>
            <a href="hospital.php">UNIDADES DE SAÚDE</a>
            <?php if ($_SESSION['usuario_tipo'] === 'paciente'): ?>
            <span class="divisor">|</span>
            <a href="buscar_paciente.php">BUSCAR PACIENTE</a>
            <?php endif; ?>
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['medico', 'enfermeiro'])): ?>
            <span class="divisor">|</span>
            <a href="inicio-med.php">ESCANEAR PULSEIRA</a>
            <?php endif; ?>
        </nav>

        <a href="<?= $eh_profissional ? 'inicio-med.php' : 'index.php' ?>" class="botao-sair" style="background: #666;">
            <span>←</span>
            VOLTAR
        </a>
    </header>

<<<<<<< HEAD
    <main>
        <?php if ($paciente_encontrado && $dados_paciente && ($pode_ver_dados_basicos || $pode_ver_dados_completos)): ?>
            <section class="ficha-medica">
=======
    <!-- Conteúdo principal -->
    <main>
        <?php if ($paciente_encontrado && $dados_paciente && ($pode_ver_dados_basicos || $pode_ver_dados_completos)): ?>
            <section class="ficha-medica">
                <!-- Card de Informações Críticas para Socorro -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                <?php if ($pode_ver_dados_completos): ?>
                <div class="card-info-critica" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); border-radius: 16px; padding: 25px; margin-bottom: 30px; box-shadow: 0 8px 24px rgba(255, 107, 107, 0.3); color: white;">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <span style="font-size: 2rem; margin-right: 15px;">🚨</span>
                        <h2 style="margin: 0; color: white; font-size: 1.5rem; text-transform: uppercase; letter-spacing: 1px;">INFORMAÇÕES CRÍTICAS PARA SOCORRO</h2>
                    </div>
<<<<<<< HEAD
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
=======
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <!-- Tipo Sanguíneo -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">TIPO SANGUÍNEO</div>
                            <div style="font-size: 1.8rem; font-weight: 700; text-align: center; margin-top: 10px;">
                                <?= !empty($dados_paciente['tipo_sanguineo']) ? htmlspecialchars($dados_paciente['tipo_sanguineo']) : '<span style="opacity: 0.6;">Não informado</span>' ?>
                            </div>
                        </div>
<<<<<<< HEAD
                        <?php if ($dados_paciente['alergias']): ?>
=======
                        
                        <!-- Alergias -->
                        <?php if (!empty($dados_paciente['alergias'])): ?>
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.3);">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">🚨 ALERGIAS</div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-top: 10px; line-height: 1.4;">
                                <?= htmlspecialchars(formatarNomeLegivel($dados_paciente['alergias'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
<<<<<<< HEAD
                        <?php if ($dados_paciente['doencas_cronicas']): ?>
=======
                        
                        <!-- Doenças Crônicas -->
                        <?php if (!empty($dados_paciente['doencas_cronicas'])): ?>
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">💊 DOENÇAS CRÔNICAS</div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-top: 10px; line-height: 1.4;">
                                <?= htmlspecialchars(formatarNomeLegivel($dados_paciente['doencas_cronicas'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
<<<<<<< HEAD
                        <?php if ($dados_paciente['fuma']): ?>
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">🚬 FUMA</div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-top: 10px; line-height: 1.4;">
                                <?= htmlspecialchars(strtoupper($dados_paciente['fuma'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($dados_paciente['bebe']): ?>
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">🍺 BEBE ÁLCOOL</div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-top: 10px; line-height: 1.4;">
                                <?= htmlspecialchars(strtoupper($dados_paciente['bebe'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($dados_paciente['medicacoes']): ?>
=======
                        
                        <!-- Medicamentos -->
                        <?php if (!empty($dados_paciente['medicacoes'])): ?>
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">💉 MEDICAÇÕES EM USO</div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-top: 10px; line-height: 1.4;">
                                <?= htmlspecialchars($dados_paciente['medicacoes']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
<<<<<<< HEAD
                        <?php if ($dados_paciente['dispositivos']): ?>
=======
                        
                        <!-- Dispositivos Implantados -->
                        <?php if (!empty($dados_paciente['dispositivos'])): ?>
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">⚡ DISPOSITIVOS IMPLANTADOS</div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-top: 10px; line-height: 1.4;">
                                <?= htmlspecialchars(formatarNomeLegivel($dados_paciente['dispositivos'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
<<<<<<< HEAD
                        <?php if ($dados_paciente['informacoes_relevantes']): ?>
=======
                        
                        <!-- Informações Relevantes -->
                        <?php if (!empty($dados_paciente['informacoes_relevantes'])): ?>
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px); grid-column: 1 / -1;">
                            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 5px;">📋 INFORMAÇÕES RELEVANTES</div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-top: 10px; line-height: 1.4;">
                                <?= htmlspecialchars($dados_paciente['informacoes_relevantes']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
<<<<<<< HEAD
                    <?php if (empty($dados_paciente['alergias']) && empty($dados_paciente['doencas_cronicas']) && empty($dados_paciente['fuma']) && empty($dados_paciente['bebe']) && empty($dados_paciente['medicacoes']) && empty($dados_paciente['dispositivos']) && empty($dados_paciente['informacoes_relevantes'])): ?>
=======
                    
                    <!-- Mensagem se não houver informações críticas -->
                    <?php if (empty($dados_paciente['alergias']) && empty($dados_paciente['doencas_cronicas']) && empty($dados_paciente['medicacoes']) && empty($dados_paciente['dispositivos']) && empty($dados_paciente['informacoes_relevantes'])): ?>
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                    <div style="text-align: center; padding: 20px; opacity: 0.8;">
                        <p style="margin: 0; font-size: 1.1rem;">Nenhuma informação crítica registrada</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
<<<<<<< HEAD

=======
                
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                <div class="header-paciente">
                    <h2>FICHA MÉDICA DO PACIENTE</h2>
                    <hr>
                    <div class="codigo-pulseira">
                        <?php if (!empty($cpf_busca) && $dados_paciente): ?>
                            <span class="badge-pulseira">🆔 CPF: <?= htmlspecialchars($dados_paciente['cpf'] ?: $cpf_busca) ?></span>
                        <?php elseif ($id_ficha): ?>
                            <span class="badge-pulseira">🆔 ID Ficha: <?= htmlspecialchars($id_ficha) ?></span>
                        <?php elseif ($codigo_pulseira): ?>
                            <span class="badge-pulseira">📱 Código: <?= htmlspecialchars($codigo_pulseira) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
<<<<<<< HEAD

                <div class="carousel" id="fichaCarousel">
                    <div class="carousel-inner">
=======
                

                <div class="carousel" id="fichaCarousel">
                    <div class="carousel-inner">
                        <!-- Slide 1: Informações Básicas -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div class="carousel-item active">
                            <div class="card-ficha">
                                <div class="perfil">
                                    <?php 
                                    $foto_src = 'img/perfil.svg';
                                    if (!empty($dados_paciente['foto_perfil']) && file_exists('uploads/fotos/' . $dados_paciente['foto_perfil'])) {
                                        $foto_src = 'uploads/fotos/' . $dados_paciente['foto_perfil'];
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do paciente" style="object-fit: cover;">
                                    <div>
                                        <h3><?= htmlspecialchars($dados_paciente['nome']) ?></h3>
                                        <?php if ($dados_paciente['idade']): ?>
                                            <p><strong>IDADE:</strong> <?= htmlspecialchars($dados_paciente['idade']) ?> ANOS</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($pode_ver_dados_completos): ?>
                                <div class="info-basica">
                                    <h4>INFORMAÇÕES BÁSICAS</h4>
                                    <p><strong>DATA DE NASCIMENTO:</strong> <?= htmlspecialchars($dados_paciente['data_nascimento']) ?></p>
                                    <p><strong>SEXO:</strong> <?= htmlspecialchars($dados_paciente['sexo']) ?></p>
                                    <p><strong>CPF:</strong> <?= htmlspecialchars($dados_paciente['cpf']) ?></p>
                                    <p><strong>TELEFONE:</strong> <?= htmlspecialchars($dados_paciente['telefone']) ?></p>
                                    <p><strong>E-MAIL:</strong> <?= htmlspecialchars($dados_paciente['email']) ?></p>
<<<<<<< HEAD
                                    <?php if ($dados_paciente['cep'] || $dados_paciente['rua'] || $dados_paciente['numero'] || $dados_paciente['complemento'] || $dados_paciente['bairro'] || $dados_paciente['cidade'] || $dados_paciente['estado']): ?>
                                        <h4 style="margin-top: 15px;">ENDEREÇO</h4>
                                        <?php if ($dados_paciente['cep']): ?>
                                            <p><strong>CEP:</strong> <?= htmlspecialchars($dados_paciente['cep']) ?></p>
                                        <?php endif; ?>
                                        <?php if ($dados_paciente['rua']): ?>
                                            <p><strong>RUA:</strong> <?= htmlspecialchars($dados_paciente['rua']) ?></p>
                                        <?php endif; ?>
                                        <?php if ($dados_paciente['numero']): ?>
                                            <p><strong>NÚMERO:</strong> <?= htmlspecialchars($dados_paciente['numero']) ?></p>
                                        <?php endif; ?>
                                        <?php if ($dados_paciente['complemento']): ?>
                                            <p><strong>COMPLEMENTO:</strong> <?= htmlspecialchars($dados_paciente['complemento']) ?></p>
                                        <?php endif; ?>
                                        <?php if ($dados_paciente['bairro']): ?>
                                            <p><strong>BAIRRO:</strong> <?= htmlspecialchars($dados_paciente['bairro']) ?></p>
                                        <?php endif; ?>
                                        <?php if ($dados_paciente['cidade']): ?>
                                            <p><strong>CIDADE:</strong> <?= htmlspecialchars($dados_paciente['cidade']) ?></p>
                                        <?php endif; ?>
                                        <?php if ($dados_paciente['estado']): ?>
                                            <p><strong>ESTADO:</strong> <?= htmlspecialchars($dados_paciente['estado']) ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
=======
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                                </div>
                                <?php endif; ?>
                                <?php if ($pode_ver_dados_basicos): ?>
                                <div class="contato-emergencia">
                                    <h4>CONTATO DE EMERGÊNCIA</h4>
                                    <p><strong>CONTATO:</strong> <?= htmlspecialchars($dados_paciente['contato_emergencia']) ?></p>
                                    <p><strong>PARENTESCO:</strong> <?= htmlspecialchars($dados_paciente['parentesco']) ?></p>
                                    <p><strong>TELEFONE:</strong> <?= htmlspecialchars($dados_paciente['telefone_emergencia']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($pode_ver_dados_completos): ?>
<<<<<<< HEAD
=======
                        <!-- Slide 2: Informações Médicas -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div class="carousel-item">
                            <div class="card-ficha">
                                <div class="perfil">
                                    <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do paciente" style="object-fit: cover;">
                                    <div>
                                        <h3><?= htmlspecialchars($dados_paciente['nome']) ?></h3>
                                        <?php if ($dados_paciente['idade']): ?>
                                            <p><strong>IDADE:</strong> <?= htmlspecialchars($dados_paciente['idade']) ?> ANOS</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="info-basica">
                                    <h4>INFORMAÇÕES MÉDICAS</h4>
                                    <?php if ($dados_paciente['doencas_cronicas']): ?>
                                        <p><strong>DOENÇAS CRÔNICAS:</strong> <?= htmlspecialchars(formatarNomeLegivel($dados_paciente['doencas_cronicas'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['alergias']): ?>
                                        <p><strong>ALERGIA:</strong> <?= htmlspecialchars(formatarNomeLegivel($dados_paciente['alergias'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['tipo_sanguineo']): ?>
                                        <p><strong>TIPO SANGUÍNEO:</strong> <?= htmlspecialchars($dados_paciente['tipo_sanguineo']) ?></p>
                                    <?php endif; ?>
<<<<<<< HEAD
                                    <?php if ($dados_paciente['fuma']): ?>
                                        <p><strong>FUMA:</strong> <?= htmlspecialchars(strtoupper($dados_paciente['fuma'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['bebe']): ?>
                                        <p><strong>BEBE ÁLCOOL:</strong> <?= htmlspecialchars(strtoupper($dados_paciente['bebe'])) ?></p>
                                    <?php endif; ?>
=======
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                                    <?php if ($dados_paciente['medicacoes']): ?>
                                        <p><strong>MEDICAÇÃO DE USO CONTÍNUO:</strong> <?= htmlspecialchars($dados_paciente['medicacoes']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['doenca_mental']): ?>
                                        <p><strong>DOENÇA MENTAL:</strong> <?= htmlspecialchars(formatarNomeLegivel($dados_paciente['doenca_mental'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['dispositivos']): ?>
                                        <p><strong>DISPOSITIVOS IMPLANTADOS:</strong> <?= htmlspecialchars(formatarNomeLegivel($dados_paciente['dispositivos'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['informacoes_relevantes']): ?>
                                        <p><strong>INFORMAÇÕES RELEVANTES:</strong> <?= htmlspecialchars($dados_paciente['informacoes_relevantes']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

<<<<<<< HEAD
=======
                        <!-- Slide 3: Histórico Médico -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                        <div class="carousel-item">
                            <div class="card-ficha">
                                <div class="perfil">
                                    <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do paciente" style="object-fit: cover;">
                                    <div>
                                        <h3><?= htmlspecialchars($dados_paciente['nome']) ?></h3>
                                        <?php if ($dados_paciente['idade']): ?>
                                            <p><strong>IDADE:</strong> <?= htmlspecialchars($dados_paciente['idade']) ?> ANOS</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="info-basica">
                                    <h4>HISTÓRICO MÉDICO</h4>
                                    <?php if ($dados_paciente['historico_cirurgias']): ?>
                                        <p><strong>CIRURGIA:</strong> <?= htmlspecialchars($dados_paciente['historico_cirurgias']) ?></p>
                                    <?php else: ?>
                                        <p><strong>CIRURGIA:</strong> NENHUMA REGISTRADA</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

<<<<<<< HEAD
                    <button class="carousel-control prev">❮</button>
                    <button class="carousel-control next">❯</button>

=======
                    <!-- Controles -->
                    <button class="carousel-control prev">❮</button>
                    <button class="carousel-control next">❯</button>

                    <!-- Indicadores -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
                    <div class="carousel-indicators">
                        <span data-slide="0" class="active"></span>
                        <?php if ($pode_ver_dados_completos): ?>
                        <span data-slide="1"></span>
                        <span data-slide="2"></span>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php elseif ($paciente_encontrado && $eh_paciente && isset($autorizacao_usuario) && $autorizacao_usuario === 'nao'): ?>
<<<<<<< HEAD
=======
            <!-- Usuário comum tentando acessar paciente não autorizado -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
            <section class="ficha-medica">
                <div class="mensagem-erro-scanner">
                    <div class="erro-icon">🔒</div>
                    <h2>Acesso Negado</h2>
                    <p>Este paciente não autorizou o compartilhamento de dados básicos.</p>
                    <p>Você não pode visualizar as informações desta ficha médica.</p>
                    <a href="buscar_paciente.php" class="btn-voltar-scanner">← Voltar</a>
                </div>
            </section>
        <?php else: ?>
<<<<<<< HEAD
=======
            <!-- Paciente não encontrado -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
            <section class="ficha-medica">
                <div class="mensagem-erro-scanner">
                    <div class="erro-icon">❌</div>
                    <h2>Paciente não encontrado</h2>
                    <?php if (!empty($codigo_pulseira)): ?>
                        <p>O código da pulseira "<strong><?= htmlspecialchars($codigo_pulseira) ?></strong>" não foi encontrado no sistema.</p>
                    <?php else: ?>
                        <p>A ficha médica informada não foi encontrada no sistema.</p>
                    <?php endif; ?>
                    <p>Verifique se o ID está correto e tente novamente.</p>
                    <a href="<?= $eh_profissional ? 'inicio-med.php' : 'buscar_paciente.php' ?>" class="btn-voltar-scanner">← Voltar</a>
                </div>
            </section>
        <?php endif; ?>
    </main>

<<<<<<< HEAD
=======
    <!-- Rodapé -->
>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
    <footer>
        <div class="footer-logo">
            <img src="img/logo-branco.png" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>&copy; 2025 Grupo SAMED. Todos os direitos reservados.</p>
        <div class="lojas">
            <img src="img/appstore.webp" alt="App Store">
            <img src="img/googleplay.webp" alt="Google Play">
        </div>
    </footer>

    <script>
<<<<<<< HEAD
        const slides = document.querySelectorAll("#fichaCarousel .carousel-item");
        const indicators = document.querySelectorAll("#fichaCarousel .carousel-indicators span");
        let activeSlide = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === index);
            });
            activeSlide = index;
        }

        document.querySelector('.carousel-control.prev').addEventListener('click', function() {
            const nextIndex = activeSlide === 0 ? slides.length - 1 : activeSlide - 1;
            showSlide(nextIndex);
        });

        document.querySelector('.carousel-control.next').addEventListener('click', function() {
            const nextIndex = activeSlide === slides.length - 1 ? 0 : activeSlide + 1;
            showSlide(nextIndex);
        });

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => showSlide(index));
        });
    </script>
</body>
=======
        // Script do carrossel (mesmo do perfil.php)
        const slides = document.querySelectorAll("#fichaCarousel .carousel-item");
        const indicators = document.querySelectorAll("#fichaCarousel .carousel-indicators span");
        const inner = document.querySelector("#fichaCarousel .carousel-inner");

        if (slides.length > 0) {
            let index = 0;

            function updateCarousel() {
                inner.style.transform = `translateX(-${index * 100}%)`;

                indicators.forEach(ind => ind.classList.remove("active"));
                indicators[index].classList.add("active");

                slides.forEach(slide => slide.classList.remove("active"));
                slides[index].classList.add("active");
            }

            document.querySelector(".carousel-control.next")?.addEventListener("click", () => {
                index = (index + 1) % slides.length;
                updateCarousel();
            });

            document.querySelector(".carousel-control.prev")?.addEventListener("click", () => {
                index = (index - 1 + slides.length) % slides.length;
                updateCarousel();
            });

            indicators.forEach(ind => {
                ind.addEventListener("click", () => {
                    index = Number(ind.dataset.slide);
                    updateCarousel();
                });
            });
        }
    </script>

    <style>
        .header-paciente {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .header-paciente h2 {
            text-align: center;
            margin: 0;
        }

        .codigo-pulseira {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-pulseira {
            background: linear-gradient(135deg, #4ca9c7 0%, #4ca9c7 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(110, 193, 228, 0.3);
        }

        .mensagem-erro-scanner {
            text-align: center;
            padding: 3rem 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 2rem auto;
        }

        .erro-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .mensagem-erro-scanner h2 {
            color: #c62828;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .mensagem-erro-scanner p {
            color: #666;
            margin: 0.5rem 0;
            line-height: 1.6;
        }

        .btn-voltar-scanner {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 12px 24px;
            background: #4ca9c7;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-voltar-scanner:hover {
            background: #4ca9c7;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(110, 193, 228, 0.3);
        }
    </style>
    
    <script src="js/toast.js"></script>

</body>

>>>>>>> 8b655687d72825d3eabf6da834727bc41de77ae8
</html>

