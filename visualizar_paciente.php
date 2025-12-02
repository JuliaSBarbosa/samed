<?php
require_once 'verificar_login.php';
require_once 'config.php';

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

    <!-- Conteúdo principal -->
    <main>
        <?php if ($paciente_encontrado && $dados_paciente && ($pode_ver_dados_basicos || $pode_ver_dados_completos)): ?>
            <section class="ficha-medica">
                <!-- Alerta de Doenças Críticas -->
                <?php if ($pode_ver_dados_completos && (!empty($dados_paciente['doencas_cronicas']) || !empty($dados_paciente['alergias']))): ?>
                <div class="alerta-doencas" style="background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
                    <h3 style="color: #856404; margin: 0 0 10px 0; font-size: 1.1rem;">⚠️ ALERTAS MÉDICOS</h3>
                    <?php if (!empty($dados_paciente['alergias'])): ?>
                        <div style="margin-bottom: 8px;">
                            <strong style="color: #d32f2f;">🚨 ALERGIAS:</strong>
                            <span style="color: #856404;"><?= htmlspecialchars($dados_paciente['alergias']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($dados_paciente['doencas_cronicas'])): ?>
                        <div>
                            <strong style="color: #d32f2f;">💊 DOENÇAS CRÔNICAS:</strong>
                            <span style="color: #856404;"><?= htmlspecialchars($dados_paciente['doencas_cronicas']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="header-paciente">
                    <h2>FICHA MÉDICA DO PACIENTE</h2>
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
                <hr>

                <div class="carousel" id="fichaCarousel">
                    <div class="carousel-inner">
                        <!-- Slide 1: Informações Básicas -->
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
                        <!-- Slide 2: Informações Médicas -->
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
                                        <p><strong>DOENÇAS CRÔNICAS:</strong> <?= htmlspecialchars($dados_paciente['doencas_cronicas']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['alergias']): ?>
                                        <p><strong>ALERGIA:</strong> <?= htmlspecialchars($dados_paciente['alergias']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['tipo_sanguineo']): ?>
                                        <p><strong>TIPO SANGUÍNEO:</strong> <?= htmlspecialchars($dados_paciente['tipo_sanguineo']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['medicacoes']): ?>
                                        <p><strong>MEDICAÇÃO DE USO CONTÍNUO:</strong> <?= htmlspecialchars($dados_paciente['medicacoes']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['doenca_mental']): ?>
                                        <p><strong>DOENÇA MENTAL:</strong> <?= htmlspecialchars($dados_paciente['doenca_mental']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['dispositivos']): ?>
                                        <p><strong>DISPOSITIVOS IMPLANTADOS:</strong> <?= htmlspecialchars($dados_paciente['dispositivos']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($dados_paciente['informacoes_relevantes']): ?>
                                        <p><strong>INFORMAÇÕES RELEVANTES:</strong> <?= htmlspecialchars($dados_paciente['informacoes_relevantes']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3: Histórico Médico -->
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

                    <!-- Controles -->
                    <button class="carousel-control prev">❮</button>
                    <button class="carousel-control next">❯</button>

                    <!-- Indicadores -->
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
            <!-- Usuário comum tentando acessar paciente não autorizado -->
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
            <!-- Paciente não encontrado -->
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

    <!-- Rodapé -->
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
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .codigo-pulseira {
            display: flex;
            align-items: center;
        }

        .badge-pulseira {
            background: linear-gradient(135deg, #6ec1e4 0%, #9ad2ea 100%);
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
            background: #6ec1e4;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-voltar-scanner:hover {
            background: #5bb0d1;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(110, 193, 228, 0.3);
        }
    </style>
    
    <script src="js/toast.js"></script>

</body>

</html>

