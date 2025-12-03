<?php require_once 'verificar_login.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Início</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <!-- Cabeçalho -->
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>

        <nav class="menu">
            <a href="index.php" class="ativo">INÍCIO</a>
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
            <a href="buscar_paciente.php">BUSCAR PACIENTE</a>
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
    <main>
        <?php
        // Limpar mensagens de cadastro/login que não devem aparecer na página inicial após primeiro acesso
        if (isset($_SESSION['sucesso']) && strpos($_SESSION['sucesso'], 'cadastro realizado') !== false) {
            // Se já fez login, não mostrar mais a mensagem de cadastro
            unset($_SESSION['sucesso']);
        }
        ?>
        <h2 class="titulo">OLÁ, <?php echo strtoupper(htmlspecialchars($_SESSION['usuario_nome'])); ?>!</h2>

        <div class="opcoes">
            <a href="perfil.php" class="link-card">
                <div class="card">
                    <i class="fas fa-user-md icone icone-perfil"></i>
                    <p>Acesse a sua ficha de informações</p>
                </div>
            </a>
            <a href="dependentes.php" class="link-card">
                <div class="card">
                    <i class="fas fa-user-friends icone icone-dependentes"></i>
                    <p>Dados dos Dependentes</p>
                </div>
            </a>
            <a href="historico.php" class="link-card">
                <div class="card">
                    <i class="fas fa-file-medical-alt icone icone-historico"></i>
                    <p>Histórico de Acessos</p>
                </div>
            </a>
            <a href="hospital.php" class="link-card">
                <div class="card">
                    <i class="fas fa-hospital icone icone-hospital"></i>
                    <p>Confira lista de hospitais próximos</p>
                </div>
            </a>
            
            <?php if ($_SESSION['usuario_tipo'] === 'paciente'): ?>
            <a href="buscar_paciente.php" class="link-card">
                <div class="card">
                    <i class="fas fa-stethoscope icone icone-buscar"></i>
                    <p>Buscar Paciente por ID</p>
                </div>
            </a>
            <?php endif; ?>
            
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['medico', 'enfermeiro'])): ?>
            <a href="inicio-med.php" class="link-card">
                <div class="card">
                    <i class="fas fa-qrcode icone icone-qrcode"></i>
                    <p>Escanear Pulseira / Buscar Ficha</p>
                </div>
            </a>
            <?php endif; ?>

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
             <img src="img/googleplay.webp" alt="App Store">
        </div>
    </footer>
   
</body>

</html>