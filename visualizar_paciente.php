<?php
require_once 'verificar_login.php';

// Verificar se é profissional de saúde
$eh_profissional = false;
if (isset($_SESSION['usuario_tipo']) && in_array($_SESSION['usuario_tipo'], ['medico', 'enfermeiro', 'tecnico'])) {
    $eh_profissional = true;
}

if (!$eh_profissional) {
    header('Location: index.php');
    exit;
}

// Receber código da pulseira
$codigo_pulseira = $_GET['codigo_pulseira'] ?? '';

if (empty($codigo_pulseira)) {
    header('Location: inicio-med.php');
    exit;
}

// TODO: Buscar dados do paciente no banco de dados usando o código da pulseira
// Por enquanto, vamos simular dados de exemplo
$paciente_encontrado = null;
$dados_paciente = null;

// Simulação: se o código começar com "SAMED-", vamos mostrar dados de exemplo
if (preg_match('/^SAMED-\d+$/i', $codigo_pulseira)) {
    $paciente_encontrado = true;
    
    // Dados simulados do paciente
    $dados_paciente = [
        'nome' => 'Joana Dark',
        'idade' => 23,
        'data_nascimento' => '03/04/2002',
        'sexo' => 'Feminino',
        'cpf' => '489.069.228-25',
        'telefone' => '(19) 97112-0245',
        'email' => 'joana@gmail.com',
        'contato_emergencia' => 'Patrícia',
        'parentesco' => 'Mãe',
        'telefone_emergencia' => '(19) 99695-1292',
        'doencas_cronicas' => 'Diabete Tipo 1 | Hipertensão',
        'alergias' => 'Azitromicina',
        'tipo_sanguineo' => 'A+',
        'medicacoes' => 'Captopril | Insulina',
        'doenca_mental' => 'Não',
        'dispositivos' => 'Marca Passo | Bomba de Insulina',
        'informacoes_relevantes' => 'Grávida',
        'historico_cirurgias' => 'Retirada Amídala em 2016'
    ];
    
    // TODO: Registrar acesso no histórico
    // Registrar que este profissional acessou os dados deste paciente
    // Isso deve ser salvo no banco de dados para aparecer no histórico.php
}

// Determinar tipo de profissional
$tipo_profissional = '';
$registro = '';
if ($_SESSION['usuario_tipo'] === 'medico') {
    $tipo_profissional = 'Médico';
    $registro = $_SESSION['usuario_crm'] ?? '';
} elseif ($_SESSION['usuario_tipo'] === 'enfermeiro') {
    $tipo_profissional = 'Enfermeiro(a)';
    $registro = $_SESSION['usuario_coren'] ?? '';
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

        <a href="inicio-med.php" class="botao-sair" style="background: #666;">
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
                        <span class="badge-pulseira">📱 Código: <?= htmlspecialchars($codigo_pulseira) ?></span>
                    </div>
                </div>
                <hr>

                <div class="carousel" id="fichaCarousel">
                    <div class="carousel-inner">
                        <!-- Slide 1: Informações Básicas -->
                        <div class="carousel-item active">
                            <div class="card-ficha">
                                <div class="perfil">
                                    <img src="img/perfil.svg" alt="Foto do paciente">
                                    <div>
                                        <h3><?= htmlspecialchars($dados_paciente['nome']) ?></h3>
                                        <p><strong>IDADE:</strong> <?= htmlspecialchars($dados_paciente['idade']) ?> ANOS</p>
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

                        <!-- Slide 2: Informações Médicas -->
                        <div class="carousel-item">
                            <div class="card-ficha">
                                <div class="perfil">
                                    <img src="img/perfil.svg" alt="Foto do paciente">
                                    <div>
                                        <h3><?= htmlspecialchars($dados_paciente['nome']) ?></h3>
                                        <p><strong>IDADE:</strong> <?= htmlspecialchars($dados_paciente['idade']) ?> ANOS</p>
                                    </div>
                                </div>
                                <div class="info-basica">
                                    <h4>INFORMAÇÕES MÉDICAS</h4>
                                    <p><strong>DOENÇAS CRÔNICAS:</strong> <?= htmlspecialchars($dados_paciente['doencas_cronicas']) ?></p>
                                    <p><strong>ALERGIA:</strong> <?= htmlspecialchars($dados_paciente['alergias']) ?></p>
                                    <p><strong>TIPO SANGUÍNEO:</strong> <?= htmlspecialchars($dados_paciente['tipo_sanguineo']) ?></p>
                                    <p><strong>MEDICAÇÃO DE USO CONTÍNUO:</strong> <?= htmlspecialchars($dados_paciente['medicacoes']) ?></p>
                                    <p><strong>DOENÇA MENTAL:</strong> <?= htmlspecialchars($dados_paciente['doenca_mental']) ?></p>
                                    <p><strong>DISPOSITIVOS IMPLANTADOS:</strong> <?= htmlspecialchars($dados_paciente['dispositivos']) ?></p>
                                    <p><strong>INFORMAÇÕES RELEVANTES:</strong> <?= htmlspecialchars($dados_paciente['informacoes_relevantes']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3: Histórico Médico -->
                        <div class="carousel-item">
                            <div class="card-ficha">
                                <div class="perfil">
                                    <img src="img/perfil.svg" alt="Foto do paciente">
                                    <div>
                                        <h3><?= htmlspecialchars($dados_paciente['nome']) ?></h3>
                                        <p><strong>IDADE:</strong> <?= htmlspecialchars($dados_paciente['idade']) ?> ANOS</p>
                                    </div>
                                </div>
                                <div class="info-basica">
                                    <h4>HISTÓRICO MÉDICO</h4>
                                    <p><strong>CIRURGIA:</strong> <?= htmlspecialchars($dados_paciente['historico_cirurgias']) ?></p>
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

