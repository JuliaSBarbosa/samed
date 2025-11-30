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

// Receber ID da ficha ou código da pulseira
$id_ficha = isset($_GET['id_ficha']) ? (int)$_GET['id_ficha'] : null;
$codigo_pulseira = $_GET['codigo_pulseira'] ?? '';

if (empty($id_ficha) && empty($codigo_pulseira)) {
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
        // Buscar por ID da ficha
        if ($id_ficha) {
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
                $pode_ver_dados_completos = $eh_profissional;
                $autorizacao_usuario = $perfil['autorizacao_usuario'] ?? 'nao';
                if ($eh_paciente && $autorizacao_usuario === 'nao') {
                    $pode_ver_dados_completos = false;
                }
                
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
                
                // Registrar acesso no histórico (para profissionais e pacientes comuns)
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
                        // Paciente comum visualizando outro paciente
                        $tipo_acesso = 'Consulta por Usuário';
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
            // Por enquanto, redirecionar para busca por ID
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

        <nav class="menu-profissional">
            <span class="profissional-info">
                <strong><?= htmlspecialchars($tipo_profissional) ?></strong>
                <?php if ($registro): ?>
                    <span class="registro-profissional"><?= htmlspecialchars($registro) ?></span>
                <?php endif; ?>
            </span>
        </nav>

        <a href="<?= $eh_profissional ? 'inicio-med.php' : 'index.php' ?>" class="botao-sair" style="background: #666;">
            <span>←</span>
            VOLTAR
        </a>
    </header>

    <!-- Conteúdo principal -->
    <main>
        <?php if ($paciente_encontrado && $dados_paciente): ?>
            <section class="ficha-medica">
                <div class="header-paciente">
                    <h2>FICHA MÉDICA DO PACIENTE</h2>
                    <div class="codigo-pulseira">
                        <?php if ($id_ficha): ?>
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
                                <div class="info-basica">
                                    <h4>INFORMAÇÕES BÁSICAS</h4>
                                    <p><strong>DATA DE NASCIMENTO:</strong> <?= htmlspecialchars($dados_paciente['data_nascimento']) ?></p>
                                    <p><strong>SEXO:</strong> <?= htmlspecialchars($dados_paciente['sexo']) ?></p>
                                    <p><strong>CPF:</strong> <?= htmlspecialchars($dados_paciente['cpf']) ?></p>
                                    <p><strong>TELEFONE:</strong> <?= htmlspecialchars($dados_paciente['telefone']) ?></p>
                                    <p><strong>E-MAIL:</strong> <?= htmlspecialchars($dados_paciente['email']) ?></p>
                                </div>
                                <div class="contato-emergencia">
                                    <h4>CONTATO DE EMERGÊNCIA</h4>
                                    <p><strong>CONTATO:</strong> <?= htmlspecialchars($dados_paciente['contato_emergencia']) ?></p>
                                    <p><strong>PARENTESCO:</strong> <?= htmlspecialchars($dados_paciente['parentesco']) ?></p>
                                    <p><strong>TELEFONE:</strong> <?= htmlspecialchars($dados_paciente['telefone_emergencia']) ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if ($eh_profissional || ($eh_paciente && isset($perfil['autorizacao_usuario']) && $perfil['autorizacao_usuario'] === 'sim')): ?>
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
                        <?php if ($eh_profissional || ($eh_paciente && isset($autorizacao_usuario) && $autorizacao_usuario === 'sim')): ?>
                        <span data-slide="1"></span>
                        <span data-slide="2"></span>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="ficha-medica">
                <div class="mensagem-erro-scanner">
                    <div class="erro-icon">❌</div>
                    <h2>Paciente não encontrado</h2>
                    <p>O código da pulseira "<strong><?= htmlspecialchars($codigo_pulseira) ?></strong>" não foi encontrado no sistema.</p>
                    <p>Verifique se o código está correto e tente novamente.</p>
                    <a href="inicio-med.php" class="btn-voltar-scanner">← Voltar ao Scanner</a>
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

</body>

</html>

