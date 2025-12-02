<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$dependentes = [];

// Buscar dependentes do banco
if ($pdo && $usuario_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM dependentes WHERE paciente_id = ? ORDER BY nome");
        $stmt->execute([$usuario_id]);
        $dependentes = $stmt->fetchAll();
    } catch(PDOException $e) {
        // Erro ao buscar dados
        error_log("Erro ao buscar dependentes: " . $e->getMessage());
        $_SESSION['erro'] = "Não foi possível carregar a lista de dependentes. Por favor, tente novamente.";
    }
}

// Calcular idade
function calcularIdade($data_nascimento) {
    if (!$data_nascimento) return null;
    $data_nasc = new DateTime($data_nascimento);
    $hoje = new DateTime();
    return $hoje->diff($data_nasc)->y;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Dependentes</title>
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
            <a href="dependentes.php" class="ativo">DEPENDENTES</a>
            <span class="divisor">|</span>
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
        <section class="dependentes">
            <h2>DEPENDENTES</h2>
            <hr>
            
            <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="mensagem-sucesso"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
                <?php unset($_SESSION['sucesso']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['erro'])): ?>
                <div class="mensagem-erro"><?= htmlspecialchars($_SESSION['erro']) ?></div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>
            
            <div class="opcoes">
                <a href="form_dependentes.php" class="link-card">
                    <div class="card">
                        <img src="img/mais.svg" alt="Adicionar" class="icone">
                        <p>Adicionar dependente</p>
                    </div>
                </a>
                
                <?php foreach ($dependentes as $dependente): 
                    $idade = calcularIdade($dependente['data_nascimento']);
                    $foto_perfil = $dependente['foto_perfil'] ?? null;
                    $foto_src = $foto_perfil ? 'uploads/fotos/' . $foto_perfil : 'img/perfil.svg';
                ?>
                    <div class="card-dependente">
                        <div class="card-dependente-header">
                            <img src="<?= htmlspecialchars($foto_src) ?>" alt="Foto" class="foto-dependente" style="object-fit: cover;">
                            <div class="info-dependente">
                                <h3><?= htmlspecialchars($dependente['nome']) ?></h3>
                                <?php if ($idade): ?>
                                    <p><strong>Idade:</strong> <?= $idade ?> anos</p>
                                <?php endif; ?>
                                <?php if ($dependente['sexo']): ?>
                                    <p><strong>Sexo:</strong> <?= ucfirst($dependente['sexo']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="perfil_dependente.php?id=<?= $dependente['id'] ?>" class="btn-editar-dependente" style="background-color: #6ec1e4;">👁️ Ver Perfil</a>
                            <a href="form_dependentes.php?editar=<?= $dependente['id'] ?>" class="btn-editar-dependente">✏️ Editar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
             <img src="img/googleplay.webp" alt="App Store">
        </div>
    </footer>
    <script src="js/toast.js"></script>
</body>

</html>