<?php
require_once 'verificar_login.php';
require_once 'config.php';

// Verificar se é paciente
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'paciente') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Buscar Paciente</title>
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
            <a href="perfil.php">MEU PERFIL</a>
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
        <section class="secao-busca">
            <h2>BUSCAR PACIENTE</h2>
            <hr>
            <p style="margin: 20px 0; color: #666;">
                Digite o ID da ficha médica para consultar informações básicas do paciente (nome e contato de emergência).
                <br><strong>Nota:</strong> Apenas pacientes que autorizaram o compartilhamento de dados básicos poderão ser visualizados.
            </p>
            
            <form method="GET" action="visualizar_paciente.php" class="form-busca">
                <div class="campo-busca">
                    <label for="id_ficha">ID da Ficha Médica</label>
                    <input 
                        type="number" 
                        id="id_ficha" 
                        name="id_ficha" 
                        placeholder="Digite o ID da ficha médica (ex: 1)"
                        class="input-busca"
                        min="1"
                        required
                    >
                </div>
                <button type="submit" class="btn-buscar">
                    <span>🔍</span>
                    Buscar
                </button>
            </form>
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
            <img src="img/googleplay.webp" alt="Google Play">
        </div>
    </footer>
</body>

</html>

