<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Registro</title>
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
            
            <form action="registrar_process.php" method="post" id="formRegistro">
                <label for="nome">NOME COMPLETO</label>
                <input type="text" id="nome" name="nome" required placeholder="Digite seu nome completo" value="<?php echo isset($_SESSION['dados_form']['nome']) ? htmlspecialchars($_SESSION['dados_form']['nome']) : ''; ?>">

                <label for="email">E-MAIL</label>
                <input type="email" id="email" name="email" required placeholder="Digite seu e-mail" value="<?php echo isset($_SESSION['dados_form']['email']) ? htmlspecialchars($_SESSION['dados_form']['email']) : ''; ?>">

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
                            <input type="radio" id="tipo_medico" name="tipo" value="medico" required <?php echo (isset($_SESSION['dados_form']['tipo']) && $_SESSION['dados_form']['tipo'] == 'medico') ? 'checked' : ''; ?>>
                            <label for="tipo_medico">Médico</label>
                        </div>
                        <div class="tipo-opcao">
                            <input type="radio" id="tipo_enfermeiro" name="tipo" value="enfermeiro" required <?php echo (isset($_SESSION['dados_form']['tipo']) && $_SESSION['dados_form']['tipo'] == 'enfermeiro') ? 'checked' : ''; ?>>
                            <label for="tipo_enfermeiro">Enfermeiro</label>
                        </div>
                    </div>
                </div>

                <div id="campo_crm" style="display: none;">
                    <label for="crm">CRM (Conselho Regional de Medicina)</label>
                    <input type="text" id="crm" name="crm" placeholder="Digite seu CRM" value="<?php echo isset($_SESSION['dados_form']['crm']) ? htmlspecialchars($_SESSION['dados_form']['crm']) : ''; ?>">
                </div>

                <div id="campo_coren" style="display: none;">
                    <label for="coren">COREN (Conselho Regional de Enfermagem)</label>
                    <input type="text" id="coren" name="coren" placeholder="Digite seu COREN" value="<?php echo isset($_SESSION['dados_form']['coren']) ? htmlspecialchars($_SESSION['dados_form']['coren']) : ''; ?>">
                </div>

                <input type="submit" value="CRIAR CONTA">
            </form>

            <script>
                // Mostrar/ocultar campos CRM e COREN conforme o tipo selecionado
                document.querySelectorAll('input[name="tipo"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        const tipo = this.value;
                        const campoCRM = document.getElementById('campo_crm');
                        const campoCOREN = document.getElementById('campo_coren');
                        const inputCRM = document.getElementById('crm');
                        const inputCOREN = document.getElementById('coren');

                        if (tipo === 'medico') {
                            campoCRM.style.display = 'block';
                            inputCRM.required = true;
                            campoCOREN.style.display = 'none';
                            inputCOREN.required = false;
                            inputCOREN.value = '';
                        } else if (tipo === 'enfermeiro') {
                            campoCOREN.style.display = 'block';
                            inputCOREN.required = true;
                            campoCRM.style.display = 'none';
                            inputCRM.required = false;
                            inputCRM.value = '';
                        } else {
                            campoCRM.style.display = 'none';
                            inputCRM.required = false;
                            inputCRM.value = '';
                            campoCOREN.style.display = 'none';
                            inputCOREN.required = false;
                            inputCOREN.value = '';
                        }
                    });
                });

                // Verificar tipo selecionado ao carregar a página
                document.addEventListener('DOMContentLoaded', function() {
                    const tipoSelecionado = document.querySelector('input[name="tipo"]:checked');
                    if (tipoSelecionado) {
                        tipoSelecionado.dispatchEvent(new Event('change'));
                    }
                });
            </script>
            
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

