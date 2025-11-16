<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Início</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
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


        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <!-- Conteúdo principal -->
     <main>
        <h2 class="titulo">OLÁ, <?php echo strtoupper(htmlspecialchars($_SESSION['usuario_nome'])); ?>!</h2>

        <div class="opcoes">
            <a href="perfil.php" class="link-card">
                <div class="card">
                    <img src="img/usuario.svg" alt="Usuário" class="icone">
                    <p>Consultar dados</p>
                </div>
            </a>
        </div>
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