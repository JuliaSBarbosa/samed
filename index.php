<?php require_once 'verificar_login.php'; ?>
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

        <nav class="menu">
            <a href="index.php" class="ativo">INÍCIO</a>
            <span class="divisor">|</span>
            <a href="perfil.php">MEU PERFIL</a>
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
        <h2 class="titulo">OLÁ, <?php echo strtoupper(htmlspecialchars($_SESSION['usuario_nome'])); ?>!</h2>

        <div class="opcoes">
            <a href="perfil.php" class="link-card">
                <div class="card">
                    <img src="img/usuario.svg" alt="Usuário" class="icone">
                    <p>Acesse a sua ficha de informações</p>
                </div>
            </a>

            <a href="dependentes.php" class="link-card">
                <div class="card">
                    <img src="img/dependentes.svg" alt="Dependentes" class="icone">
                    <p>Dados dos Dependentes</p>
                </div>
            </a>
            
            <a href="hospital.php" class="link-card">
                <div class="card">
                    <img src="img/local.svg" alt="Hospitais" class="icone">
                    <p>Confira lista de hospitais próximos</p>
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
</body>

</html>