<?php require_once 'verificar_login.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Scanner de Pulseira</title>
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

        <nav class="menu-profissional">
            <span class="profissional-info">
                <?php 
                $tipo_profissional = '';
                $registro = '';
                if (isset($_SESSION['usuario_tipo'])) {
                    if ($_SESSION['usuario_tipo'] === 'medico') {
                        $tipo_profissional = 'Médico';
                        $registro = $_SESSION['usuario_crm'] ?? '';
                    } elseif ($_SESSION['usuario_tipo'] === 'enfermeiro') {
                        $tipo_profissional = 'Enfermeiro(a)';
                        $registro = $_SESSION['usuario_coren'] ?? '';
                    } else {
                        $tipo_profissional = 'Profissional de Saúde';
                    }
                }
                ?>
                <strong><?= htmlspecialchars($tipo_profissional) ?></strong>
                <?php if ($registro): ?>
                    <span class="registro-profissional"><?= htmlspecialchars($registro) ?></span>
                <?php endif; ?>
            </span>
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
                <span class="scanner-icon">📱</span>
                ESCANEAR PULSEIRA
            </h2>
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

            <!-- Botão de Scanner Manual (para testes) -->
            <div class="scanner-manual">
                <p class="manual-text">Ou digite o código da pulseira manualmente:</p>
                <form method="GET" action="visualizar_paciente.php" class="scanner-form">
                    <div class="input-group">
                        <input 
                            type="text" 
                            name="codigo_pulseira" 
                            id="codigoPulseira" 
                            placeholder="Digite o código da pulseira (ex: SAMED-123456)"
                            class="scanner-input"
                            pattern="[A-Z0-9-]+"
                            required
                        >
                        <button type="submit" class="btn-scanner">
                            <span>🔍</span>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Informações do Profissional -->
            <div class="info-profissional">
                <div class="info-card">
                    <h3>Informações do Profissional</h3>
                    <p><strong>Nome:</strong> <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'N/A') ?></p>
                    <p><strong>Tipo:</strong> <?= htmlspecialchars($tipo_profissional) ?></p>
                    <?php if ($registro): ?>
                        <p><strong>Registro:</strong> <?= htmlspecialchars($registro) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Aviso sobre funcionalidade futura -->
            <div class="aviso-futuro">
                <p><strong>⚠️ Nota:</strong> A funcionalidade de escaneamento por NFC/QR Code será implementada quando a pulseira física estiver disponível. Por enquanto, use a busca manual acima.</p>
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

        // Foco automático no campo de código quando a página carrega
        document.addEventListener('DOMContentLoaded', () => {
            const codigoInput = document.getElementById('codigoPulseira');
            if (codigoInput) {
                // Não focar automaticamente para não abrir o teclado no mobile
                // codigoInput.focus();
            }
        });
    </script>

    <style>
        @keyframes scanLine {
            0% { top: 0; opacity: 1; }
            50% { top: 100%; opacity: 0.8; }
            100% { top: 0; opacity: 1; }
        }
    </style>

</body>

</html>