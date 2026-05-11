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
            <?php if ($_SESSION['usuario_tipo'] === 'paciente'): ?>
            <span class="divisor">|</span>
            <a href="buscar_paciente.php" class="ativo">BUSCAR PACIENTE</a>
            <?php endif; ?>
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
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 class="scanner-titulo" style="text-align: center;">
                    <span class="scanner-icon">📱</span>
                    ESCANEAR PULSEIRA
                </h2>
            </div>
            <p class="scanner-subtitulo">Aproxime o celular da pulseira do paciente para acessar os dados médicos</p>

            <!-- Área do Scanner -->
            <div class="scanner-area" id="scannerArea">
                <div class="scanner-frame">
                    <div class="scanner-corners">
                        <div class="corner corner-tl"></div>
                        <div class="corner corner-tr"></div>
                        <div class="corner corner-bl"></div>
                        <div class="corner corner-br"></div>
                    </div>
                    <div class="scanner-line"></div>
                    <div class="scanner-instructions">
                        <p class="instruction-text">Posicione a pulseira dentro do quadro</p>
                    </div>
                </div>
            </div>

            <!-- Busca por Número de Série da Pulseira -->
            <div class="scanner-manual">
                <p class="manual-text">Digite o número de série da pulseira para consultar:</p>
                <form method="GET" action="visualizar_paciente.php" class="scanner-form">
                    <div class="input-group">
                        <input 
                            type="number" 
                            name="id_ficha" 
                            id="idFicha" 
                            placeholder="Digite o número de série da pulseira (ex: 1)"
                            class="scanner-input"
                            min="1"
                            required
                        >
                        <button type="submit" class="btn-scanner">
                            <span>🔍</span>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Botão Voltar -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn-voltar-scanner" style="display: inline-block;">
                    ← Voltar
                </a>
            </div>

            <!-- Aviso sobre funcionalidade e limitações para pacientes -->
            <div class="aviso-futuro">
                <p><strong>⚠️ Nota:</strong> A funcionalidade de escaneamento por NFC/QR Code será implementada quando a pulseira física estiver disponível. Por enquanto, use a busca manual acima.</p>
                <p style="margin-top: 10px;"><strong>ℹ️ Informação:</strong> Como paciente, você poderá visualizar apenas informações básicas de outros pacientes que autorizaram o compartilhamento: nome e contato de emergência.</p>
            </div>
        </div>
    </main>

    <script>
        // Simulação de animação do scanner (será substituído pela funcionalidade real)
        const scannerArea = document.getElementById('scannerArea');
        const scannerLine = document.querySelector('.scanner-line');
        
        // Animação da linha do scanner
        if (scannerLine) {
            setInterval(() => {
                scannerLine.style.animation = 'none';
                setTimeout(() => {
                    scannerLine.style.animation = 'scanLine 2s ease-in-out infinite';
                }, 10);
            }, 2000);
        }
    </script>

    <style>
        @keyframes scanLine {
            0% { top: 0; opacity: 1; }
            50% { top: 100%; opacity: 0.8; }
            100% { top: 0; opacity: 1; }
        }
    </style>

    <!-- Rodapé -->
    <footer>
        <div class="footer-logo">
            <img src="img/logo-branco.png" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>&copy; 2025 Grupo SAMED. Todos os direitos reservados.</p>
    </footer>
</body>

</html>

