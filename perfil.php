<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Início</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.png" type="image/png">
</head>

<body>
    <!-- Cabeçalho -->
    <header class="topo">
        <div class="logo">
            <img src="img/logo.png" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>

        <nav class="menu">
            <a href="index.php">INÍCIO</a>
            <span class="divisor">|</span>
            <a href="perfil.php" class="ativo">MEU PERFIL</a>
            <span class="divisor">|</span>
            <a href="hospital.php">HOSPITAL</a>
            <span class="divisor">|</span>
            <a href="dependentes.php">DEPENDENTES</a>
        </nav>

        <a href="sair.php" class="botao-sair">
            <img src="img/sair-icon.png" alt="Sair">
            SAIR
        </a>
    </header>

    <!-- Conteúdo principal -->
    <main>
        <section class="ficha-medica">
            <h2>FICHA MÉDICA</h2>
            <hr>
            <div class="card-ficha">
                <div class="perfil">
                    <img src="img/user-icon.png" alt="Foto do usuário">
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
        </section>
    </main>

    <!-- Rodapé -->
    <footer>
        <div class="footer-logo">
            <img src="img/logo.png" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>DESENVOLVIDO POR GRUPO AINDA SEM NOME.</p>
        <div class="lojas">
            <img src="img/appstore.png" alt="App Store">
            <img src="img/googleplay.png" alt="Google Play">
        </div>
    </footer>
</body>

</html>