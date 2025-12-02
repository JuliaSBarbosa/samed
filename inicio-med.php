<?php 
require_once 'verificar_login.php';
require_once 'config.php';

// Verificar se é profissional de saúde (médico ou enfermeiro)
$eh_profissional = false;
if (isset($_SESSION['usuario_tipo']) && in_array($_SESSION['usuario_tipo'], ['medico', 'enfermeiro'])) {
    $eh_profissional = true;
}

if (!$eh_profissional) {
    header('Location: index.php');
    exit;
}
?>
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
            <span class="divisor">|</span>
            <a href="inicio-med.php" class="ativo">ESCANEAR PULSEIRA</a>
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

            <!-- Busca por CPF -->
            <div class="scanner-manual">
                <p class="manual-text">Digite o CPF do paciente para consultar:</p>
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
                        >
                        <button type="submit" class="btn-scanner">
                            <span>🔍</span>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Busca por ID da Ficha Médica (alternativa) -->
            <div class="scanner-manual" style="margin-top: 20px;">
                <p class="manual-text">Ou digite o ID da ficha médica:</p>
                <form method="GET" action="visualizar_paciente.php" class="scanner-form">
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
            </div>
            
            <!-- Botão Voltar -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn-voltar-scanner" style="display: inline-block;">
                    ← Voltar
                </a>
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

        // Máscara de CPF no campo de busca
        document.addEventListener('DOMContentLoaded', () => {
            const cpfInput = document.getElementById('cpfBusca');
            if (cpfInput) {
                cpfInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    }
                });
            }
        });
    </script>
    
    <script src="js/toast.js"></script>

    <style>
        @keyframes scanLine {
            0% { top: 0; opacity: 1; }
            50% { top: 100%; opacity: 0.8; }
            100% { top: 0; opacity: 1; }
        }
    </style>

</body>

</html>