<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$perfil = null;
$contato_emergencia = null;
$tem_dados = false;

// Buscar dados do perfil médico
if ($pdo && $usuario_id) {
    try {
        // Buscar perfil médico
        $stmt = $pdo->prepare("
            SELECT pm.*, u.nome as nome_usuario 
            FROM perfis_medicos pm
            INNER JOIN usuarios u ON pm.usuario_id = u.id
            WHERE pm.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $perfil = $stmt->fetch();
        
        // Buscar contato de emergência
        if ($perfil) {
            $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            $contato_emergencia = $stmt->fetch();
            $tem_dados = true;
        }
    } catch(PDOException $e) {
        // Erro ao buscar dados
        error_log("Erro ao buscar perfil: " . $e->getMessage());
        $_SESSION['erro_perfil'] = "Não foi possível carregar seus dados. Por favor, tente novamente.";
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
    <title>SAMED - Perfil</title>
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
            <a href="perfil.php" class="ativo">MEU PERFIL</a>
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

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <!-- Conteúdo principal -->
    <main>
        <section class="ficha-medica">
            <div class="ficha-medica">
                <h2>FICHA MÉDICA</h2>
            </div>
            <hr>

            <?php 
            // Exibir mensagens de sucesso/erro (apenas relacionadas ao perfil, não de cadastro/login)
            if (isset($_SESSION['sucesso_perfil'])) {
                echo '<div class="mensagem-sucesso">' . htmlspecialchars($_SESSION['sucesso_perfil']) . '</div>';
                unset($_SESSION['sucesso_perfil']);
            }
            if (isset($_SESSION['erro_perfil'])) {
                echo '<div class="mensagem-erro">' . htmlspecialchars($_SESSION['erro_perfil']) . '</div>';
                unset($_SESSION['erro_perfil']);
            }
            // Limpar mensagens de cadastro/login que não devem aparecer aqui
            unset($_SESSION['sucesso']);
            unset($_SESSION['erro_login']);
            ?>
            
            <?php if ($tem_dados): ?>
                <div class="perfil-actions-container">
                    <div class="perfil-buttons">
                        <a href="form_perfil.php?editar=1" class="btn-editar-perfil">
                            <span class="btn-icon">✏️</span>
                            <span>Editar Perfil</span>
                        </a>
                        <button type="button" class="btn-vincular-pulseira" onclick="document.getElementById('modalPulseira').classList.add('aberto'); document.body.style.overflow='hidden';">
                            <span class="btn-icon">📿</span>
                            <span>Vincular Pulseira</span>
                        </button>
                    </div>
                    
                    <!-- Configurações de Privacidade - para pacientes, médicos e enfermeiros -->
                    <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['paciente', 'medico', 'enfermeiro'])): ?>
                    <div class="privacidade-card">
                        <div class="privacidade-header">
                            <span class="privacidade-icon">🔒</span>
                            <h3>Configurações de Privacidade</h3>
                        </div>
                        <form method="POST" action="atualizar_privacidade.php" class="privacidade-form">
                            <label class="privacidade-checkbox">
                                <input type="checkbox" name="autorizacao_usuario" value="sim"
                                    <?= ($perfil['autorizacao_usuario'] ?? 'nao') === 'sim' ? 'checked' : '' ?>
                                    onchange="this.form.submit()">
                                <span class="checkmark"></span>
                                <span class="checkbox-label">Permitir que usuários comuns consultem informações básicas</span>
                            </label>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$tem_dados): ?>
                <!-- Mensagem quando não tem dados -->
                <div class="mensagem-sem-dados">
                    <div class="mensagem-icon">📋</div>
                    <h3>Nenhum dado cadastrado</h3>
                    <p>Você ainda não possui informações médicas cadastradas no sistema.</p>
                    <p>Cadastre suas informações para que profissionais de saúde possam acessá-las em caso de emergência.</p>
                    <a href="form_perfil.php" class="btn-cadastrar-perfil">➕ Cadastrar Meu Perfil</a>
                </div>
            <?php else: ?>

            <div class="carousel" id="fichaCarousel">
                <div class="carousel-inner">
                    <!-- Slide 1 -->
                    <div class="carousel-item active">
                        <div class="card-ficha">
                            <div class="perfil">
                                <?php 
                                $foto_perfil = $perfil['foto_perfil'] ?? null;
                                $foto_src = $foto_perfil ? 'uploads/fotos/' . $foto_perfil : 'img/perfil.svg';
                                ?>
                                <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do usuário" style="object-fit: cover;">
                                <div>
                                    <h3><?= htmlspecialchars(strtoupper($perfil['nome_usuario'] ?? $_SESSION['usuario_nome'])) ?></h3>
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
                                <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do usuário" style="object-fit: cover;">
                                <div>
                                    <h3><?= htmlspecialchars(strtoupper($perfil['nome_usuario'] ?? $_SESSION['usuario_nome'])) ?></h3>
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

                    <!-- Slide 3 -->
                    <div class="carousel-item">
                        <div class="card-ficha">
                            <div class="perfil">
                                <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto do usuário" style="object-fit: cover;">
                                <div>
                                    <h3><?= htmlspecialchars(strtoupper($perfil['nome_usuario'] ?? $_SESSION['usuario_nome'])) ?></h3>
                                    <?php if ($idade): ?>
                                        <p><strong>IDADE:</strong> <?= $idade ?> ANOS</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info-basica">
                                <h4>HISTÓRICO MÉDICO</h4>
                                <?php if ($perfil['cirurgias']): ?>
                                    <p><strong>CIRURGIA:</strong> <?= htmlspecialchars(strtoupper($perfil['cirurgias'])) ?></p>
                                <?php else: ?>
                                    <p><strong>CIRURGIA:</strong> NENHUMA REGISTRADA</p>
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
                    <h3 id="modalPulseiraTitulo">📿 Vincular pulseira SAMED</h3>
                    <button type="button" class="modal-pulseira-fechar" aria-label="Fechar"
                        onclick="document.getElementById('modalPulseira').classList.remove('aberto'); document.body.style.overflow='';">×</button>
                </div>
                <div class="modal-pulseira-body">
                    <div class="modal-pulseira-icon">📿</div>
                    <p><strong>Aproxime sua pulseira NFC do leitor</strong> para gravar os dados da sua ficha médica e permitir leitura em emergências.</p>
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

    <script>
        const slides = document.querySelectorAll("#fichaCarousel .carousel-item");
        const indicators = document.querySelectorAll("#fichaCarousel .carousel-indicators span");
        const inner = document.querySelector("#fichaCarousel .carousel-inner");

        let index = 0;

        function updateCarousel() {
            inner.style.transform = `translateX(-${index * 100}%)`;

            // Atualiza os indicadores
            indicators.forEach(ind => ind.classList.remove("active"));
            indicators[index].classList.add("active");

            // Atualiza a classe active nos slides
            slides.forEach(slide => slide.classList.remove("active"));
            slides[index].classList.add("active");
        }

        document.querySelector(".carousel-control.next").addEventListener("click", () => {
            index = (index + 1) % slides.length;
            updateCarousel();
        });

        document.querySelector(".carousel-control.prev").addEventListener("click", () => {
            index = (index - 1 + slides.length) % slides.length;
            updateCarousel();
        });

        indicators.forEach(ind => {
            ind.addEventListener("click", () => {
                index = Number(ind.dataset.slide);
                updateCarousel();
            });
        });
    </script>


</body>

</html>