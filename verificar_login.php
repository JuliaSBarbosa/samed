<?php
// Arquivo para verificar se o usuário está logado
// Inclua este arquivo no topo de páginas que requerem autenticação

require_once 'config.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: login.php');
    exit;
}
?>

