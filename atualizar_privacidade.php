<?php
require_once 'verificar_login.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id || !$pdo) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações.";
    header('Location: perfil.php');
    exit;
}

try {
    $compartilhar_localizacao = isset($_POST['compartilhar_localizacao']) && $_POST['compartilhar_localizacao'] === 'sim' ? 'sim' : 'nao';
    $autorizacao_usuario = isset($_POST['autorizacao_usuario']) && $_POST['autorizacao_usuario'] === 'sim' ? 'sim' : 'nao';
    
    // Verificar se existe perfil médico
    $stmt = $pdo->prepare("SELECT id FROM perfis_medicos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $perfil_existente = $stmt->fetch();
    
    if ($perfil_existente) {
        // Verificar se as colunas existem antes de atualizar
        $colunas_existentes = [];
        $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $set_parts = [];
        $valores = [];
        
        if (in_array('compartilhar_localizacao', $colunas)) {
            $set_parts[] = 'compartilhar_localizacao = ?';
            $valores[] = $compartilhar_localizacao;
        }
        
        if (in_array('autorizacao_usuario', $colunas)) {
            $set_parts[] = 'autorizacao_usuario = ?';
            $valores[] = $autorizacao_usuario;
        }
        
        if (!empty($set_parts)) {
            $set_clause = implode(', ', $set_parts);
            
            // Adicionar usuario_id no final para o WHERE
            $valores[] = $usuario_id;
            
            $stmt = $pdo->prepare("UPDATE perfis_medicos SET $set_clause WHERE usuario_id = ?");
            $stmt->execute($valores);
        }
    }
    
    $_SESSION['sucesso_perfil'] = "Configurações de privacidade atualizadas com sucesso!";
    
} catch(PDOException $e) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações: " . $e->getMessage();
}

header('Location: perfil.php');
exit;
?>

