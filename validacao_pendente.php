<?php
require_once 'verificar_login.php';
require_once 'config.php';

$tipo = $_SESSION['usuario_tipo'] ?? '';
if (!in_array($tipo, ['medico', 'enfermeiro'])) {
    header('Location: index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$dados_validacao = null;
if ($pdo && $usuario_id) {
    $stmt = $pdo->prepare("SELECT foto_documento, foto_selfie, status_validacao, data_validacao FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $dados_validacao = $stmt->fetch();
}

$status_atual = $dados_validacao['status_validacao'] ?? 'pendente';
$tem_fotos = !empty($dados_validacao['foto_documento']) && !empty($dados_validacao['foto_selfie']);

if ($status_atual === 'aprovado') {
    $_SESSION['status_validacao'] = 'aprovado';
}

$mostrar_flash_info = !empty($_SESSION['flash_validacao_info']);
if ($mostrar_flash_info) {
    unset($_SESSION['flash_validacao_info']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED — Validação de identidade</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Magra:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" href="img/logo.svg" type="image/png">
</head>
<body class="validacao-kyc-body">
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <nav class="menu">
            <span class="validacao-kyc-nav">Validação de identidade profissional</span>
        </nav>
        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <main class="validacao-kyc-main">
        <?php if (isset($_SESSION['sucesso_validacao'])): ?>
            <div class="mensagem-sucesso validacao-kyc-msg-top"><?php echo htmlspecialchars($_SESSION['sucesso_validacao']); unset($_SESSION['sucesso_validacao']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['erros_validacao'])): ?>
            <div class="mensagem-erro validacao-kyc-msg-top"><?php foreach ($_SESSION['erros_validacao'] as $erro) echo '<p>' . htmlspecialchars($erro) . '</p>'; unset($_SESSION['erros_validacao']); ?></div>
        <?php endif; ?>

        <?php if ($mostrar_flash_info): ?>
            <div class="validacao-kyc-flash">
                <strong>Bem-vindo(a).</strong> Seu acesso completo será liberado após a análise dos documentos — processo semelhante ao de bancos e serviços públicos digitais.
            </div>
        <?php endif; ?>

        <?php if ($status_atual === 'aprovado'): ?>
            <div class="validacao-kyc-card validacao-kyc-card--ok">
                <div class="validacao-kyc-card-icon" aria-hidden="true">✓</div>
                <h2 class="validacao-kyc-h2">Identidade validada</h2>
                <p class="validacao-kyc-lead">Seu cadastro como <strong><?php echo htmlspecialchars(strtoupper($tipo)); ?></strong> foi aprovado. Você já pode usar o SAMED com acesso completo.</p>
                <a href="index.php" class="btn-editar-perfil validacao-kyc-cta">Ir para o início</a>
            </div>

        <?php elseif ($status_atual === 'pendente' && $tem_fotos): ?>
            <div class="validacao-kyc-card">
                <div class="validacao-kyc-badge validacao-kyc-badge--pendente">Em análise</div>
                <h2 class="validacao-kyc-h2">Aguardando aprovação</h2>
                <p class="validacao-kyc-lead">Recebemos seu documento e sua selfie. Um administrador está conferindo as informações antes de liberar o login completo, como em validações de identidade em bancos e órgãos governamentais.</p>

                <ol class="validacao-kyc-timeline">
                    <li class="validacao-kyc-timeline-item validacao-kyc-timeline-item--done">
                        <span class="validacao-kyc-step-num">1</span>
                        <div>
                            <strong>Cadastro realizado</strong>
                            <span>Conta criada e fotos enviadas.</span>
                        </div>
                    </li>
                    <li class="validacao-kyc-timeline-item validacao-kyc-timeline-item--done">
                        <span class="validacao-kyc-step-num">2</span>
                        <div>
                            <strong>Documentos recebidos</strong>
                            <span>Documento e selfie estão no sistema.</span>
                        </div>
                    </li>
                    <li class="validacao-kyc-timeline-item validacao-kyc-timeline-item--active">
                        <span class="validacao-kyc-step-num">3</span>
                        <div>
                            <strong>Análise manual</strong>
                            <span>Equipe verifica CRM/COREN e correspondência com as imagens.</span>
                        </div>
                    </li>
                    <li class="validacao-kyc-timeline-item">
                        <span class="validacao-kyc-step-num">4</span>
                        <div>
                            <strong>Liberação</strong>
                            <span>Após aprovação, faça login de novo (ou atualize) e o painel completo será liberado.</span>
                        </div>
                    </li>
                </ol>

                <p class="validacao-kyc-dica">Enquanto isso, você pode sair e voltar depois. Não é necessário reenviar as fotos, salvo se um administrador solicitar ou se quiser substituir por imagens mais nítidas.</p>

                <details class="validacao-kyc-details">
                    <summary>Enviar novas imagens (substituir documento e selfie)</summary>
                    <form action="salvar_validacao_profissional.php" method="post" enctype="multipart/form-data" class="validacao-kyc-form">
                        <label for="foto_documento_alt">Nova foto do documento</label>
                        <input type="file" id="foto_documento_alt" name="foto_documento" accept="image/*" required>
                        <label for="foto_selfie_alt">Nova selfie com o documento</label>
                        <input type="file" id="foto_selfie_alt" name="foto_selfie" accept="image/*" required>
                        <input type="submit" value="Substituir e reenviar para análise">
                    </form>
                </details>
            </div>

        <?php elseif ($status_atual === 'reprovado'): ?>
            <div class="validacao-kyc-card validacao-kyc-card--alert">
                <div class="validacao-kyc-badge validacao-kyc-badge--erro">Reprovado</div>
                <h2 class="validacao-kyc-h2">Validação não aprovada</h2>
                <p class="validacao-kyc-lead">Sua última submissão não foi aprovada. Envie novamente imagens nítidas do documento profissional e uma selfie segurando o mesmo documento, como exigido em processos de KYC.</p>
            </div>
            <div class="validacao-kyc-card validacao-kyc-card--form">
                <form action="salvar_validacao_profissional.php" method="post" enctype="multipart/form-data" class="validacao-kyc-form">
                    <label for="foto_documento">Foto do documento (CRM/COREN ou RG com registro)</label>
                    <input type="file" id="foto_documento" name="foto_documento" accept="image/*" required>
                    <p class="validacao-kyc-hint">Imagem legível, sem cortes importantes.</p>
                    <label for="foto_selfie">Selfie segurando o documento</label>
                    <input type="file" id="foto_selfie" name="foto_selfie" accept="image/*" required>
                    <p class="validacao-kyc-hint">Rosto e documento visíveis na mesma foto.</p>
                    <input type="submit" value="Enviar novamente para análise">
                </form>
            </div>

        <?php else: ?>
            <div class="validacao-kyc-card">
                <div class="validacao-kyc-badge validacao-kyc-badge--pendente">Documentação pendente</div>
                <h2 class="validacao-kyc-h2">Envie as fotos para validação</h2>
                <p class="validacao-kyc-lead">Para continuar, é necessário o envio do documento profissional e da selfie com o documento — padrão usado em bancos e cadastros oficiais.</p>
            </div>
            <div class="validacao-kyc-card validacao-kyc-card--form">
                <form action="salvar_validacao_profissional.php" method="post" enctype="multipart/form-data" class="validacao-kyc-form">
                    <label for="foto_documento">Foto do documento (CRM/COREN ou RG com registro)</label>
                    <input type="file" id="foto_documento" name="foto_documento" accept="image/*" required>
                    <p class="validacao-kyc-hint">Documento aberto e legível.</p>
                    <label for="foto_selfie">Selfie segurando o documento</label>
                    <input type="file" id="foto_selfie" name="foto_selfie" accept="image/*" required>
                    <p class="validacao-kyc-hint">Mesmo documento da foto anterior, visível ao lado do rosto.</p>
                    <input type="submit" value="Enviar para análise">
                </form>
            </div>
        <?php endif; ?>
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
