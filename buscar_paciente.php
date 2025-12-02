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
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['paciente', 'medico', 'enfermeiro'])): ?>
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <?php endif; ?>
            <a href="historico.php">HISTÓRICO</a>
            <span class="divisor">|</span>
            <a href="hospital.php">UNIDADES DE SAÚDE</a>
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['medico', 'enfermeiro'])): ?>
            <span class="divisor">|</span>
            <a href="inicio-med.php">ESCANEAR PULSEIRA</a>
            <?php endif; ?>
        </nav>

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <!-- Conteúdo principal -->
    <main class="scanner-container">
        <div class="scanner-wrapper">
            <h2 class="scanner-titulo">
                <span class="scanner-icon">🔍</span>
                BUSCAR FICHA DE PACIENTE
            </h2>
            <p class="scanner-subtitulo">Digite o CPF do paciente para visualizar informações básicas</p>

            <div class="scanner-manual">
                <p class="manual-text">
                    <strong>Nota:</strong> Apenas pacientes que autorizaram o compartilhamento de dados básicos poderão ser visualizados.
                    <br>Você poderá ver apenas: nome e contato de emergência.
                </p>
                <form method="GET" action="visualizar_paciente.php" class="scanner-form">
                    <div class="input-group">
                        <input 
                            type="text" 
                            name="cpf" 
                            id="cpfBusca" 
                            placeholder="Digite o CPF (ex: 123.456.789-00)"
                            class="scanner-input"
                            maxlength="14"
                            pattern="[0-9.-]+"
                            required
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2')"
                        >
                        <button type="submit" class="btn-scanner">
                            <span>🔍</span>
                            Buscar
                        </button>
                    </div>
                </form>
                <p style="margin-top: 15px; font-size: 0.9rem; color: #666;">
                    Ou busque por <a href="?busca=id" style="color: #6ec1e4;">ID da ficha médica</a>
                </p>
                <?php if (isset($_GET['busca']) && $_GET['busca'] === 'id'): ?>
                <form method="GET" action="visualizar_paciente.php" class="scanner-form" style="margin-top: 15px;">
                    <div class="input-group">
                        <input 
                            type="number" 
                            name="id_ficha" 
                            id="idFicha" 
                            placeholder="Digite o ID da ficha médica (ex: 1)"
                            class="scanner-input"
                            min="1"
                        >
                        <button type="submit" class="btn-scanner">
                            <span>🔍</span>
                            Buscar
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
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
            <img src="img/googleplay.webp" alt="Google Play">
        </div>
    </footer>
</body>

</html>

