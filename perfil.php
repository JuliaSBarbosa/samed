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
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <a href="historico.php">HISTÓRICO</a>
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

            <div class="carousel" id="fichaCarousel">

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