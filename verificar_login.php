<?php
// Arquivo para verificar se o usuário está logado
// Inclua este arquivo no topo de páginas que requerem autenticação

require_once 'config.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: login.php');
    exit;
}

// Impedir que médicos/enfermeiros acessem o sistema completo antes da validação
$tipo_usuario = $_SESSION['usuario_tipo'] ?? '';
$status_validacao = $_SESSION['status_validacao'] ?? 'aprovado';
$script_atual = basename($_SERVER['PHP_SELF'] ?? '');

if (in_array($tipo_usuario, ['medico', 'enfermeiro']) 
    && $status_validacao !== 'aprovado'
    && !in_array($script_atual, ['validacao_pendente.php', 'sair.php'])) {
    header('Location: validacao_pendente.php');
    exit;
}
?>

