<?php
require_once 'verificar_login.php';
require_once 'config.php';

$tipo_user = $_SESSION['usuario_tipo'] ?? '';
if (strtolower($tipo_user) !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'], $_POST['acao'])) {
    $usuario_id = (int) $_POST['usuario_id'];
    $acao = $_POST['acao'] === 'aprovar' ? 'aprovar' : 'reprovar';
    if ($pdo && $usuario_id > 0) {
        $novo_status = $acao === 'aprovar' ? 'aprovado' : 'reprovado';
        $stmt = $pdo->prepare("UPDATE usuarios SET status_validacao = ?, data_validacao = NOW(), validado_por = ? WHERE id = ? AND tipo IN ('medico', 'enfermeiro')");
        $stmt->execute([$novo_status, $_SESSION['usuario_id'], $usuario_id]);
    }
    header('Location: admin_validacoes.php');
    exit;
}

$profissionais = [];
if ($pdo) {
    $stmt = $pdo->query("
        SELECT id, nome, email, tipo, crm, coren, foto_documento, foto_selfie, status_validacao, data_validacao
        FROM usuarios
        WHERE tipo IN ('medico', 'enfermeiro')
        ORDER BY (status_validacao = 'pendente') DESC, nome
    ");
    $profissionais = $stmt->fetchAll();
}

$qtd_pendente = 0;
$qtd_aprovado = 0;
$qtd_reprovado = 0;
foreach ($profissionais as $p) {
    $s = $p['status_validacao'] ?? 'pendente';
    if ($s === 'pendente') $qtd_pendente++;
    elseif ($s === 'aprovado') $qtd_aprovado++;
    else $qtd_reprovado++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Validações</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
</head>
<body>
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <nav class="menu">
            <a href="index.php">INÍCIO</a>
            <span class="divisor">|</span>
            <a href="admin_validacoes.php" class="ativo">VALIDAÇÕES</a>
        </nav>
        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <main>
        <section class="dependentes">
            <h2>VALIDAÇÃO DE PROFISSIONAIS</h2>
            <hr>
            <p class="admin-intro">Analise documento e selfie enviados no cadastro (fluxo tipo KYC de bancos e órgãos públicos). Ao aprovar, o profissional passa a ter acesso completo ao SAMED.</p>
            <?php if (!empty($profissionais)): ?>
            <div class="admin-painel-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-num"><?php echo (int) $qtd_pendente; ?></div>
                    <div class="admin-stat-label">Em análise</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-num"><?php echo (int) $qtd_aprovado; ?></div>
                    <div class="admin-stat-label">Aprovados</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-num"><?php echo (int) $qtd_reprovado; ?></div>
                    <div class="admin-stat-label">Reprovados</div>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <div class="form-container">
            <?php if (empty($profissionais)): ?>
                <div class="mensagem-sem-dados">
                    <div class="mensagem-icon">📋</div>
                    <h3>Nenhum profissional cadastrado</h3>
                    <p>Ainda não há médicos ou enfermeiros no sistema. Quando houver cadastros, eles aparecerão aqui para validação.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>CRM / COREN</th>
                                <th>Status</th>
                                <th>Documento</th>
                                <th>Selfie</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($profissionais as $p): ?>
                                <tr>
                                    <td class="admin-td-center"><?php echo (int) $p['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($p['nome']); ?></strong>
                                        <span class="admin-email"><?php echo htmlspecialchars($p['email']); ?></span>
                                    </td>
                                    <td class="admin-td-uppercase"><?php echo htmlspecialchars($p['tipo']); ?></td>
                                    <td><?php echo htmlspecialchars($p['tipo'] === 'medico' ? ($p['crm'] ?? '–') : ($p['coren'] ?? '–')); ?></td>
                                    <td>
                                        <?php
                                        $st = $p['status_validacao'] ?? 'pendente';
                                        if ($st === 'aprovado') echo '<span class="admin-badge admin-badge-ok">Aprovado</span>';
                                        elseif ($st === 'reprovado') echo '<span class="admin-badge admin-badge-erro">Reprovado</span>';
                                        else echo '<span class="admin-badge admin-badge-pendente">Pendente</span>';
                                        ?>
                                        <?php if (!empty($p['data_validacao'])): ?>
                                            <span class="admin-data"><?php echo date('d/m/Y H:i', strtotime($p['data_validacao'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['foto_documento'])): ?>
                                            <a href="uploads/fotos/<?php echo htmlspecialchars($p['foto_documento']); ?>" target="_blank" rel="noopener" class="admin-link">Ver</a>
                                        <?php else: ?>–<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['foto_selfie'])): ?>
                                            <a href="uploads/fotos/<?php echo htmlspecialchars($p['foto_selfie']); ?>" target="_blank" rel="noopener" class="admin-link">Ver</a>
                                        <?php else: ?>–<?php endif; ?>
                                    </td>
                                    <td class="admin-td-acoes">
                                        <div class="admin-acoes-inline">
                                            <form method="post" class="admin-form-btn">
                                                <input type="hidden" name="usuario_id" value="<?php echo (int) $p['id']; ?>">
                                                <input type="hidden" name="acao" value="aprovar">
                                                <button type="submit" class="btn-editar-perfil admin-btn aprovar">Aprovar</button>
                                            </form>
                                            <form method="post" class="admin-form-btn">
                                                <input type="hidden" name="usuario_id" value="<?php echo (int) $p['id']; ?>">
                                                <input type="hidden" name="acao" value="reprovar">
                                                <button type="submit" class="btn-small remove admin-btn reprovar">Reprovar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

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
