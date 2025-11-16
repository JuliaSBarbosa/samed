<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Início</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Magra:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" href="img/logo.svg" type="image/png">
</head>

<body>
    <!-- Cabeçalho -->
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>

        <div class="menu-actions">
            <nav class="menu">
                <a href="index.php" class="ativo">INÍCIO</a>
                <span class="divisor">|</span>
                <a href="perfil.php">MEU PERFIL</a>
                <span class="divisor">|</span>
                <a href="dependentes.php">DEPENDENTES</a>
                <span class="divisor">|</span>
                <a href="hospital.php">UNIDADES DE SAÚDE</a>
            </nav>

            <a href="login.php" class="botao-login">
                LOGIN
            </a>
            <a href="registrar.php" class="botao-registrar">
                REGISTRE-SE
            </a>
        </div>
    </header>
    
    <main class="hero">
  <div class="login-container">
    <h2>LOGIN</h2>
    <br>
    <?php
    session_start();
    if (isset($_SESSION['erro_login'])) {
        echo '<div style="background-color: #fee; color: #c33; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center;">' . htmlspecialchars($_SESSION['erro_login']) . '</div>';
        unset($_SESSION['erro_login']);
    }
    if (isset($_SESSION['sucesso'])) {
        echo '<div style="background-color: #efe; color: #3c3; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center;">' . htmlspecialchars($_SESSION['sucesso']) . '</div>';
        unset($_SESSION['sucesso']);
    }
    ?>
    <form action="login_process.php" method="post">
      <label for="email">E-MAIL</label>
      <input type="email" id="email" name="email" required placeholder="Digite seu e-mail" autocomplete="email">

      <label for="password">SENHA</label>
      <input type="password" id="password" name="password" required placeholder="Digite sua senha" autocomplete="current-password">
      
      <input type="submit" value="ENTRAR">
    </form>
    <br>
    <p style="text-align: center; margin-top: 15px;">
        Não tem uma conta? <a href="registrar.php" style="color: #9ad2ea; text-decoration: none;">Registre-se aqui</a>
    </p>
  </div>
</main>

</body>

</html>