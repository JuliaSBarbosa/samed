<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Início</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Magra:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" href="img/logo.png" type="image/png">
</head>

<body>
    <!-- Cabeçalho -->
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
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
    <form action="login_process.php" method="post">
      <label for="email">E-MAIL</label>
      <input type="email" id="email" name="email" required placeholder="Digite seu e-mail" autocomplete="email">

      <label for="password">SENHA</label>
      <input type="password" id="password" name="password" required placeholder="Digite sua senha" autocomplete="current-password">
      
      <input type="submit" value="ENTRAR">
    </form>
  </div>
</main>

</body>

</html>