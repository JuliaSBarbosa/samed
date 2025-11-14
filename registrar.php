<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Registro</title>
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
        <div class="registro-container">
            <h2>REGISTRE-SE</h2>
            
            <?php
            session_start();
            if (isset($_SESSION['erros'])) {
                echo '<div class="mensagem-erro">';
                foreach ($_SESSION['erros'] as $erro) {
                    echo '<p>' . htmlspecialchars($erro) . '</p>';
                }
                echo '</div>';
                unset($_SESSION['erros']);
            }
            if (isset($_SESSION['sucesso'])) {
                echo '<div class="mensagem-sucesso">' . htmlspecialchars($_SESSION['sucesso']) . '</div>';
                unset($_SESSION['sucesso']);
            }
            ?>
            
            <form action="registrar_process.php" method="post">
                <label for="nome">NOME COMPLETO</label>
                <input type="text" id="nome" name="nome" required placeholder="Digite seu nome completo" value="<?php echo isset($_SESSION['dados_form']['nome']) ? htmlspecialchars($_SESSION['dados_form']['nome']) : ''; ?>">

                <label for="email">E-MAIL</label>
                <input type="email" id="email" name="email" required placeholder="Digite seu e-mail" value="<?php echo isset($_SESSION['dados_form']['email']) ? htmlspecialchars($_SESSION['dados_form']['email']) : ''; ?>">

                <label for="username">NOME DO USUÁRIO</label>
                <input type="text" id="username" name="username" required placeholder="Digite um nome de usuário" value="<?php echo isset($_SESSION['dados_form']['username']) ? htmlspecialchars($_SESSION['dados_form']['username']) : ''; ?>">

                <label for="password">SENHA</label>
                <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres">

                <label>EU SOU:</label>
                <div class="tipo-usuario-container">
                    <div class="tipo-opcoes">
                        <div class="tipo-opcao">
                            <input type="radio" id="tipo_paciente" name="tipo" value="paciente" required <?php echo (isset($_SESSION['dados_form']['tipo']) && $_SESSION['dados_form']['tipo'] == 'paciente') ? 'checked' : ''; ?>>
                            <label for="tipo_paciente">Paciente</label>
                        </div>
                        <div class="tipo-opcao">
                            <input type="radio" id="tipo_medica" name="tipo" value="medica" required <?php echo (isset($_SESSION['dados_form']['tipo']) && $_SESSION['dados_form']['tipo'] == 'medica') ? 'checked' : ''; ?>>
                            <label for="tipo_medica">Médica</label>
                        </div>
                    </div>
                </div>

                <input type="submit" value="CRIAR CONTA">
            </form>
            
            <div class="link-login">
                Já tem uma conta? <a href="login.php">Faça login aqui</a>
            </div>
        </div>
    </main>

</body>

</html>
<?php
// Limpar dados do formulário após exibir
if (isset($_SESSION['dados_form'])) {
    unset($_SESSION['dados_form']);
}
?>

