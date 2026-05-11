<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$dependente_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$dependente_id) {
    header('Location: dependentes.php');
    exit;
}

$dependente = null;
$perfil = null;
$contato_emergencia = null;
$tem_dados = false;

// Buscar dados do dependente e perfil médico
if ($pdo && $usuario_id && $dependente_id) {
    try {
        // Verificar se o dependente pertence ao usuário
        $stmt = $pdo->prepare("SELECT * FROM dependentes WHERE id = ? AND paciente_id = ?");
        $stmt->execute([$dependente_id, $usuario_id]);
        $dependente = $stmt->fetch();
        
        if (!$dependente) {
            $_SESSION['erro'] = "Dependente não encontrado ou não pertence a você.";
            header('Location: dependentes.php');
            exit;
        }
        
        // Buscar perfil médico do dependente
        // IMPORTANTE: Usamos dependente_id (não usuario_id) para evitar conflitos
        // A coluna dependente_id é específica para dependentes, enquanto usuario_id é para usuários
        $stmt = $pdo->prepare("SELECT * FROM perfis_medicos WHERE dependente_id = ? AND usuario_id IS NULL");
        $stmt->execute([$dependente_id]);
        $perfil = $stmt->fetch();
        
        // Buscar contato de emergência
        if ($perfil) {
            $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE dependente_id = ?");
            $stmt->execute([$dependente_id]);
            $contato_emergencia = $stmt->fetch();
            $tem_dados = true;
        }
    } catch(PDOException $e) {
        $_SESSION['erro'] = "Erro ao buscar dados do dependente.";
        header('Location: dependentes.php');
        exit;
    }
}

// Calcular idade
$idade = null;
if ($perfil && $perfil['data_nascimento']) {
    $data_nasc = new DateTime($perfil['data_nascimento']);
    $hoje = new DateTime();
    $idade = $hoje->diff($data_nasc)->y;
}

// Formatar data
function formatarData($data) {
    if (!$data) return '';
    return date('d/m/Y', strtotime($data));
}

// Formatar sexo
function formatarSexo($sexo) {
    $sexos = [
        'masculino' => 'MASCULINO',
        'feminino' => 'FEMININO',
        'outro' => 'OUTRO'
    ];
    return $sexos[$sexo] ?? strtoupper($sexo);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Perfil do Dependente</title>
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
            <a href="dependentes.php" class="ativo">DEPENDENTES</a>
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

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <!-- Conteúdo principal -->
    <main>
        <section class="ficha-medica">
            <div class="ficha-medica">
                <h2>FICHA MÉDICA - <?= htmlspecialchars($dependente['nome'] ?? 'Dependente') ?></h2>
            </div>
            <hr>
            
            <?php if (isset($_SESSION['sucesso_perfil'])): ?>
                <div class="mensagem-sucesso"><?= htmlspecialchars($_SESSION['sucesso_perfil']) ?></div>
                <?php unset($_SESSION['sucesso_perfil']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['erro_perfil'])): ?>
                <div class="mensagem-erro"><?= htmlspecialchars($_SESSION['erro_perfil']) ?></div>
                <?php unset($_SESSION['erro_perfil']); ?>
            <?php endif; ?>
            
            <?php if ($tem_dados): ?>
                    <div class="perfil-actions-container">
                        <div class="perfil-buttons">
                            <a href="form_dependentes.php?editar=<?= $dependente_id ?>" class="btn-editar-perfil">
                                <span class="btn-icon">✏️</span>
                                <span>Editar Perfil</span>
                            </a>
                            <button type="button" class="btn-vincular-pulseira" onclick="document.getElementById('modalPulseira').classList.add('aberto'); document.body.style.overflow='hidden';">
                                <span class="btn-icon">📿</span>
                                <span>Vincular Pulseira</span>
                            </button>
                            <a href="dependentes.php" class="btn-voltar-perfil">
                                <span class="btn-icon">←</span>
                                <span>Voltar</span>
                            </a>
                        </div>
                        
                        <!-- Configurações de Privacidade -->
                        <div class="privacidade-card">
                            <div class="privacidade-header">
                                <span class="privacidade-icon">🔒</span>
                                <h3>Configurações de Privacidade</h3>
                            </div>
                            <form method="POST" action="atualizar_privacidade_dependente.php" class="privacidade-form">
                                <input type="hidden" name="dependente_id" value="<?= $dependente_id ?>">
                                <label class="privacidade-checkbox">
                                    <input type="checkbox" name="autorizacao_usuario" value="sim"
                                        <?= ($perfil['autorizacao_usuario'] ?? 'nao') === 'sim' ? 'checked' : '' ?>
                                        onchange="this.form.submit()">
                                    <span class="checkmark"></span>
                                    <span class="checkbox-label">Permitir que usuários comuns consultem informações básicas</span>
                                </label>
                            </form>
                        </div>
                    </div>
            <?php endif; ?>
            
            <?php if (!$tem_dados): ?>
                <!-- Mensagem quando não tem dados -->
                <div class="mensagem-sem-dados">
                    <div class="mensagem-icon">📋</div>
                    <h3>Nenhum dado cadastrado</h3>
                    <p>Este dependente ainda não possui informações médicas cadastradas no sistema.</p>
                    <a href="form_dependentes.php?editar=<?= $dependente_id ?>" class="btn-cadastrar-perfil">➕ Cadastrar Perfil Médico</a>
                </div>
            <?php else: ?>

            <div class="carousel" id="fichaCarousel">
                <div class="carousel-inner">
                    <!-- Slide 1 -->
                    <div class="carousel-item active">
                        <div class="card-ficha">
                            <div class="perfil">
                                <?php 
                                $foto_perfil = $dependente['foto_perfil'] ?? null;
                                $foto_src = $foto_perfil ? 'uploads/fotos/' . $foto_perfil : 'img/perfil.svg';
                                ?>
                                <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do dependente" style="object-fit: cover;">
                                <div>
                                    <h3><?= htmlspecialchars(strtoupper($dependente['nome'])) ?></h3>
                                    <?php if ($idade): ?>
                                        <p><strong>IDADE:</strong> <?= $idade ?> ANOS</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info-basica">
                                <h4>INFORMAÇÕES BÁSICAS</h4>
                                <?php if ($perfil['data_nascimento']): ?>
                                    <p><strong>DATA DE NASCIMENTO:</strong> <?= formatarData($perfil['data_nascimento']) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['sexo']): ?>
                                    <p><strong>SEXO:</strong> <?= formatarSexo($perfil['sexo']) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['cpf']): ?>
                                    <p><strong>CPF:</strong> <?= htmlspecialchars($perfil['cpf']) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['telefone']): ?>
                                    <p><strong>TELEFONE:</strong> <?= htmlspecialchars($perfil['telefone']) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['email']): ?>
                                    <p><strong>E-MAIL:</strong> <?= htmlspecialchars(strtoupper($perfil['email'])) ?></p>
                                <?php endif; ?>
                                <?php
                                    $endereco_partes = [];
                                    if (!empty($perfil['cep'])) {
                                        $endereco_partes[] = 'CEP: ' . $perfil['cep'];
                                    }
                                    if (!empty($perfil['rua'])) {
                                        $rua_numero = htmlspecialchars($perfil['rua']);
                                        if (!empty($perfil['numero'])) {
                                            $rua_numero .= ', ' . htmlspecialchars($perfil['numero']);
                                        }
                                        $endereco_partes[] = $rua_numero;
                                    }
                                    if (!empty($perfil['complemento'])) {
                                        $endereco_partes[] = 'Complemento: ' . htmlspecialchars($perfil['complemento']);
                                    }
                                    if (!empty($perfil['bairro'])) {
                                        $endereco_partes[] = 'Bairro: ' . htmlspecialchars($perfil['bairro']);
                                    }
                                    if (!empty($perfil['cidade'])) {
                                        $cidade_estado = htmlspecialchars($perfil['cidade']);
                                        if (!empty($perfil['estado'])) {
                                            $cidade_estado .= ' - ' . htmlspecialchars($perfil['estado']);
                                        }
                                        $endereco_partes[] = $cidade_estado;
                                    } elseif (!empty($perfil['estado'])) {
                                        $endereco_partes[] = htmlspecialchars($perfil['estado']);
                                    }
                                    $endereco_exibicao = !empty($endereco_partes)
                                        ? implode(' | ', $endereco_partes)
                                        : (!empty($perfil['endereco']) ? htmlspecialchars($perfil['endereco']) : '');
                                ?>
                                <?php if ($endereco_exibicao): ?>
                                    <p><strong>ENDEREÇO:</strong> <?= $endereco_exibicao ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($contato_emergencia): ?>
                            <div class="contato-emergencia">
                                <h4>CONTATO DE EMERGÊNCIA</h4>
                                <p><strong>CONTATO:</strong> <?= htmlspecialchars(strtoupper($contato_emergencia['nome'])) ?></p>
                                <?php if ($contato_emergencia['parentesco']): ?>
                                    <p><strong>PARENTESCO:</strong> <?= htmlspecialchars(strtoupper($contato_emergencia['parentesco'])) ?></p>
                                <?php endif; ?>
                                <p><strong>TELEFONE:</strong> <?= htmlspecialchars($contato_emergencia['telefone']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="carousel-item">
                        <div class="card-ficha">
                            <div class="perfil">
                                <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do dependente" style="object-fit: cover;">
                                <div>
                                    <h3><?= htmlspecialchars(strtoupper($dependente['nome'])) ?></h3>
                                    <?php if ($idade): ?>
                                        <p><strong>IDADE:</strong> <?= $idade ?> ANOS</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info-basica">
                                <h4>INFORMAÇÕES MÉDICAS</h4>
                                <?php if ($perfil['doencas_cronicas']): ?>
                                    <p><strong>DOENÇAS CRÔNICAS:</strong> <?= htmlspecialchars(strtoupper($perfil['doencas_cronicas'])) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['alergias']): ?>
                                    <p><strong>ALERGIA:</strong> <?= htmlspecialchars(strtoupper($perfil['alergias'])) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['tipo_sanguineo']): ?>
                                    <p><strong>TIPO SANGUÍNEO:</strong> <?= htmlspecialchars($perfil['tipo_sanguineo']) ?></p>
                                <?php endif; ?>
                                <?php if (isset($perfil['fuma']) && $perfil['fuma'] !== ''): ?>

                                    <p><strong>FUMA:</strong> <?= htmlspecialchars(strtoupper($perfil['fuma'])) ?></p>
                                <?php endif; ?>
                                <?php if (isset($perfil['bebe']) && $perfil['bebe'] !== ''): ?>
                                    <p><strong>CONSOME ÁLCOOL:</strong> <?= htmlspecialchars(strtoupper($perfil['bebe'])) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['medicacao_continua']): ?>
                                    <p><strong>MEDICAÇÃO DE USO CONTÍNUO:</strong> <?= htmlspecialchars(strtoupper($perfil['medicacao_continua'])) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['doenca_mental']): ?>
                                    <p><strong>DOENÇA MENTAL:</strong> <?= htmlspecialchars(strtoupper($perfil['doenca_mental'])) ?></p>
                                <?php else: ?>
                                    <p><strong>DOENÇA MENTAL:</strong> NÃO</p>
                                <?php endif; ?>
                                <?php if ($perfil['dispositivo_implantado']): ?>
                                    <p><strong>DISPOSITIVO IMPLANTADOS:</strong> <?= htmlspecialchars(strtoupper($perfil['dispositivo_implantado'])) ?></p>
                                <?php endif; ?>
                                <?php if ($perfil['info_relevantes']): ?>
                                    <p><strong>INFORMAÇÕES RELEVANTES:</strong> <?= htmlspecialchars(strtoupper($perfil['info_relevantes'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="carousel-item">
                        <div class="card-ficha">
                            <div class="perfil">
                                <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do dependente" style="object-fit: cover;">
                                <div>
                                    <h3><?= htmlspecialchars(strtoupper($dependente['nome'])) ?></h3>
                                    <?php if ($idade): ?>
                                        <p><strong>IDADE:</strong> <?= $idade ?> ANOS</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info-basica">
                                <h4>HISTÓRICO MÉDICO</h4>
                                <?php if (!empty($perfil['cirurgias'] ?? '')): ?>
                                    <p><strong>CIRURGIAS:</strong> <?= htmlspecialchars(strtoupper($perfil['cirurgias'])) ?></p>
                                <?php else: ?>
                                    <p><strong>CIRURGIAS:</strong> NENHUMA REGISTRADA</p>
                                <?php endif; ?>
                                <?php if (!empty($perfil['historico_emergencias'] ?? '')): ?>
                                    <p><strong>HISTÓRICO DE EMERGÊNCIAS:</strong> <?= htmlspecialchars(strtoupper($perfil['historico_emergencias'] ?? '')) ?></p>
                                <?php else: ?>
                                    <p><strong>HISTÓRICO DE EMERGÊNCIAS:</strong> NENHUMA REGISTRADA</p>
                                <?php endif; ?>
                                <?php if (!empty($perfil['habitos_importantes'] ?? '')): ?>
                                    <p><strong>HÁBITOS IMPORTANTES:</strong> <?= htmlspecialchars(strtoupper($perfil['habitos_importantes'] ?? '')) ?></p>
                                <?php else: ?>
                                    <p><strong>HÁBITOS IMPORTANTES:</strong> NENHUMA REGISTRADA</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles -->
                <button class="carousel-control prev">❮</button>
                <button class="carousel-control next">❯</button>

                <!-- Indicadores -->
                <div class="carousel-indicators">
                    <span data-slide="0" class="active"></span>
                    <span data-slide="1"></span>
                    <span data-slide="2"></span>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <!-- Modal Vincular Pulseira -->
        <div class="modal-pulseira" id="modalPulseira" aria-hidden="true">
            <div class="modal-pulseira-overlay" onclick="document.getElementById('modalPulseira').classList.remove('aberto'); document.body.style.overflow='';"></div>
            <div class="modal-pulseira-card" role="dialog" aria-modal="true" aria-labelledby="modalPulseiraTitulo">
                <div class="modal-pulseira-header">
                    <h3 id="modalPulseiraTitulo">📿 Vincular pulseira de <?= htmlspecialchars($dependente['nome'] ?? 'Dependente') ?></h3>
                    <button type="button" class="modal-pulseira-fechar" aria-label="Fechar"
                        onclick="document.getElementById('modalPulseira').classList.remove('aberto'); document.body.style.overflow='';">×</button>
                </div>
                <div class="modal-pulseira-body">
                    <div class="modal-pulseira-icon">📿</div>
                    <p><strong>Aproxime a pulseira NFC do dependente</strong> no leitor para gravar os dados da ficha médica e permitir leitura em emergências.</p>
                    <p class="modal-pulseira-aviso">Funcionalidade em desenvolvimento — em breve a gravação será feita diretamente pelo aplicativo.</p>
                </div>
                <div class="modal-pulseira-acoes">
                    <button type="button" class="btn-pulseira-fechar"
                        onclick="document.getElementById('modalPulseira').classList.remove('aberto'); document.body.style.overflow='';">Fechar</button>
                </div>
            </div>
        </div>
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
            <img src="img/googleplay.webp" alt="App Store">
        </div>
    </footer>
    
    <script src="js/toast.js"></script>
    <script>
        // Script do carrossel (mesmo do perfil.php)
        const slides = document.querySelectorAll("#fichaCarousel .carousel-item");
        const indicators = document.querySelectorAll("#fichaCarousel .carousel-indicators span");
        const inner = document.querySelector("#fichaCarousel .carousel-inner");

        if (slides.length > 0) {
            let index = 0;

            function updateCarousel() {
                inner.style.transform = `translateX(-${index * 100}%)`;

                // Atualiza os indicadores
                indicators.forEach(ind => ind.classList.remove("active"));
                if (indicators[index]) {
                    indicators[index].classList.add("active");
                }

                // Atualiza a classe active nos slides
                slides.forEach(slide => slide.classList.remove("active"));
                slides[index].classList.add("active");
            }

            // Inicializa o carrossel
            updateCarousel();

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
</body>

</html>

