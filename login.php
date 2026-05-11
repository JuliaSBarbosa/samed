<?php
session_start();
?>
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
        
        <div class="menu-actions">
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
      
      <div style="text-align: right; margin-bottom: 15px;">
        <a href="#" onclick="alert('Funcionalidade em desenvolvimento'); return false;" style="color: #4ca9c7; text-decoration: none; font-size: 0.9rem;">
          Esqueci minha senha
        </a>
      </div>
      
      <input type="submit" value="ENTRAR">
    </form>
    
    <?php
    if (isset($_SESSION['erro_login'])) {
        echo '<div class="mensagem-erro">' . htmlspecialchars($_SESSION['erro_login']) . '</div>';
        unset($_SESSION['erro_login']);
    }
    if (!empty($_SESSION['sucesso_profissional_kyc'])) {
        unset($_SESSION['sucesso_profissional_kyc']);
        ?>
        <div class="login-kyc-sucesso">
            <div class="login-kyc-sucesso-icon" aria-hidden="true">✓</div>
            <h3 class="login-kyc-sucesso-titulo">Cadastro recebido</h3>
            <p class="login-kyc-sucesso-texto">Suas fotos de validação foram enviadas. O próximo passo é igual ao de bancos e portais do governo: um <strong>administrador</strong> confere documento e selfie antes de liberar o acesso.</p>
            <ul class="login-kyc-passos">
                <li><span>1</span> Faça login com o e-mail e a senha que você cadastrou.</li>
                <li><span>2</span> Você verá a área de acompanhamento até a aprovação.</li>
                <li><span>3</span> Quando for aprovado, o sistema libera o painel completo automaticamente.</li>
            </ul>
        </div>
        <?php
    }
    if (isset($_SESSION['sucesso'])) {
        echo '<div class="mensagem-sucesso">' . htmlspecialchars($_SESSION['sucesso']) . '</div>';
        unset($_SESSION['sucesso']);
    }
    ?>
    
    <div class="link-login" style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
      <p style="color: #666; font-size: 0.9rem; margin: 0;">
        Não tem uma conta? <a href="registrar.php" style="color: #4ca9c7; text-decoration: none; font-weight: 600;">Registre-se aqui</a>
      </p>
    </div>
  </div>
</main>

<script src="js/toast.js"></script>
</body>

</html>