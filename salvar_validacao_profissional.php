<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$tipo = $_SESSION['usuario_tipo'] ?? '';

if (!$usuario_id || !in_array($tipo, ['medico', 'enfermeiro'])) {
    header('Location: index.php');
    exit;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: validacao_pendente.php');
    exit;
}

// Verificar se os arquivos foram enviados
if (!isset($_FILES['foto_documento']) || $_FILES['foto_documento']['error'] !== UPLOAD_ERR_OK) {
    $erros[] = "Envie a foto do documento profissional.";
}

if (!isset($_FILES['foto_selfie']) || $_FILES['foto_selfie']['error'] !== UPLOAD_ERR_OK) {
    $erros[] = "Envie a selfie com o documento.";
}

if (!empty($erros)) {
    $_SESSION['erros_validacao'] = $erros;
    header('Location: validacao_pendente.php');
    exit;
}

// Pasta de upload (reaproveitando a pasta de fotos existente)
$pasta_upload = __DIR__ . '/uploads/fotos/';
if (!is_dir($pasta_upload)) {
    mkdir($pasta_upload, 0775, true);
}

function salvarArquivo($arquivo, $prefixo, $usuario_id, $pasta_upload)
{
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $extensao = strtolower($extensao);

    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extensao, $permitidas)) {
        return [false, "Formato de arquivo inválido. Use JPG, PNG, GIF ou WEBP."];
    }

    $nome_arquivo = $prefixo . '_' . $usuario_id . '_' . time() . '.' . $extensao;
    $destino = $pasta_upload . $nome_arquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        return [false, "Erro ao salvar o arquivo de upload."];
    }

    return [true, $nome_arquivo];
}

list($ok_doc, $resultado_doc) = salvarArquivo($_FILES['foto_documento'], 'doc_validacao', $usuario_id, $pasta_upload);
if (!$ok_doc) {
    $erros[] = $resultado_doc;
}

list($ok_selfie, $resultado_selfie) = salvarArquivo($_FILES['foto_selfie'], 'selfie_validacao', $usuario_id, $pasta_upload);
if (!$ok_selfie) {
    $erros[] = $resultado_selfie;
}

if (!empty($erros)) {
    $_SESSION['erros_validacao'] = $erros;
    header('Location: validacao_pendente.php');
    exit;
}

// Atualizar dados de validação no banco
if ($pdo && $usuario_id) {
    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET foto_documento = ?, foto_selfie = ?, status_validacao = 'pendente'
        WHERE id = ?
    ");
    $stmt->execute([$resultado_doc, $resultado_selfie, $usuario_id]);

    $_SESSION['status_validacao'] = 'pendente';
    $_SESSION['sucesso_validacao'] = "Imagens enviadas com sucesso! Aguarde a análise do administrador.";
}

header('Location: validacao_pendente.php');
exit;

