<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Perfil</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.png" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <a href="hospital.php">UNIDADES DE SAÚDE</a>
        </nav>

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <!-- Conteúdo principal -->
    <main>
        <section class="ficha-medica">
            <h2>FICHA MÉDICA</h2>
            <hr>

            <!-- Início do Carrossel Bootstrap -->
            <div id="fichaCarousel" class="carousel slide" data-bs-ride="false">
                <!-- data-bs-ride="false" impede de girar sozinho -->

                <!-- Indicadores (barrinhas embaixo) -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#fichaCarousel" data-bs-slide-to="0" class="active"
                        aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#fichaCarousel" data-bs-slide-to="1"
                        aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#fichaCarousel" data-bs-slide-to="2"
                        aria-label="Slide 3"></button>
                </div>

                <!-- Wrapper dos Slides -->
                <div class="carousel-inner">

                    <!-- Slide 1 -->
                    <div class="carousel-item active">
                        <div class="card-ficha">
                            <div class="perfil">
                                <img src="img/perfil.svg" alt="Foto do usuário">
                                <div>
                                    <h3>JOANA DARK</h3>
                                    <p><strong>IDADE:</strong> 23 ANOS</p>
                                </div>
                            </div>
                            <div class="info-basica">
                                <h4>INFORMAÇÕES BÁSICAS</h4>
                                <p><strong>DATA DE NASCIMENTO:</strong> 03/04/2002</p>
                                <p><strong>SEXO:</strong> FEMININO</p>
                                <p><strong>CPF:</strong> 489.069.228-25</p>
                                <p><strong>TELEFONE:</strong> (19) 97112-0245</p>
                                <p><strong>E-MAIL:</strong> JOANA@GMAIL.COM</p>
                            </div>
                            <div class="contato-emergencia">
                                <h4>CONTATO DE EMERGÊNCIA</h4>
                                <p><strong>CONTATO:</strong> PATRÍCIA</p>
                                <p><strong>PARENTESCO:</strong> MÃE</p>
                                <p><strong>TELEFONE:</strong> (19) 99695-1292</p>
                            </div>
                        </div>
                    </div>
                    <!-- Fim do Slide 1 -->

                    <!-- Slide 2 -->
                    <div class="carousel-item">
                        <div class="card-ficha">
                            <div class="perfil">
                            <img src="img/perfil.svg" alt="Foto do usuário">
                                <div>
                                    <h3>JOANA DARK</h3>
                                    <p><strong>IDADE:</strong> 23 ANOS</p>
                                </div>
                            </div>
                            <div class="info-basica">
                                <!-- CORREÇÃO: Faltava fechar o H4 aqui -->
                                <h4>INFORMAÇÕES MÉDICAS</h4>
                                <p><strong>DOENÇAS CRÔNICAS:</strong> DIABETE TIPO 1 | HIPERTENSÃO</p>
                                <p><strong>ALERGIA:</strong> AZITROMICINA</p>
                                <p><strong>TIPO SANGUÍNEO:</strong> A+</p>
                                <p><strong>MEDICAÇÃO DE USO CONTÍNUO:</strong> CAPTOPRIL | INSULINA</p>
                                <p><strong>DOENÇA MENTAL:</strong> NÃO</p>
                                <p><strong>DISPOSITIVO IMPLANTADOS:</strong> MARCA PASSO | BOMBA DE INSULINA</p>
                                <p><strong>INFORMAÇÕES RELEVANTES:</strong> GRÁVIDA</p>
                            </div>
                        </div>
                    </div>
                    <!-- Fim do Slide 2 -->

                    <!-- Slide 3 -->
                    <div class="carousel-item">
                        <div class="card-ficha">
                            <div class="perfil">
                            <img src="img/perfil.svg" alt="Foto do usuário">
                                <div>
                                    <h3>JOANA DARK</h3>
                                    <p><strong>IDADE:</strong> 23 ANOS</p>
                                </div>
                            </div>
                            <div class="info-basica">
                                <h4>HISTÓRICO MÉDICO</h4>
                                <p><strong>CIRURGIA:</strong> RETIRADA AMIDALA EM 2016</p>
                            </div>
                        </div>
                    </div>
                    <!-- Fim do Slide 3 -->

                </div>
                <!-- Fim do Wrapper dos Slides -->

                <!-- Controles (Setas) -->
                <button class="carousel-control-prev" type="button" data-bs-target="#fichaCarousel"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#fichaCarousel"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>

            </div>
            <!-- Fim do Carrossel Bootstrap -->

        </section>
    </main>

    <!-- Rodapé -->
    <footer>
        <div class="footer-logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>DESENVOLVIDO POR GRUPO AINDA SEM NOME.</p>
        <div class="lojas">
            <img src="img/appstore.png" alt="App Store">
            <img src="img/googleplay.png" alt="Google Play">
        </div>
    </footer>

    <!-- Bootstrap JS Bundle (sempre no final do body) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>