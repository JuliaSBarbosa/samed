<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Início</title>
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
            <img src="img/logo-branco.png" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>&copy; 2025 Grupo SAMED. Todos os direitos reservados.</p>
        <div class="lojas">
            <img src="img/appstore.webp" alt="App Store">
             <img src="img/googleplay.webp" alt="App Store">
        </div>
    </footer>

</body>

</html>