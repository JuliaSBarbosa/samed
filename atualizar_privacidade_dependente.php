<?php
require_once 'verificar_login.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dependentes.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$dependente_id = isset($_POST['dependente_id']) ? (int)$_POST['dependente_id'] : null;

if (!$usuario_id || !$dependente_id || !$pdo) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações.";
    header('Location: dependentes.php');
    exit;
}

try {
    // Verificar se o dependente pertence ao usuário
    $stmt = $pdo->prepare("SELECT id FROM dependentes WHERE id = ? AND paciente_id = ?");
    $stmt->execute([$dependente_id, $usuario_id]);
    $dependente_existe = $stmt->fetch();
    
    if (!$dependente_existe) {
        $_SESSION['erro_perfil'] = "Dependente não encontrado ou não pertence a você.";
        header('Location: dependentes.php');
        exit;
    }
    
    $compartilhar_localizacao = isset($_POST['compartilhar_localizacao']) && $_POST['compartilhar_localizacao'] === 'sim' ? 'sim' : 'nao';
    $autorizacao_usuario = isset($_POST['autorizacao_usuario']) && $_POST['autorizacao_usuario'] === 'sim' ? 'sim' : 'nao';
    
    // Verificar se existe perfil médico do dependente
    // IMPORTANTE: Usamos dependente_id (não usuario_id) para evitar conflitos
    // A coluna dependente_id é específica para dependentes, enquanto usuario_id é para usuários
    $stmt = $pdo->prepare("SELECT id FROM perfis_medicos WHERE dependente_id = ? AND usuario_id IS NULL");
    $stmt->execute([$dependente_id]);
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
            
            // Adicionar dependente_id no final para o WHERE
            $valores[] = $dependente_id;
            
            // Garantir que estamos atualizando apenas perfis de dependentes (não de usuários)
            $stmt = $pdo->prepare("UPDATE perfis_medicos SET $set_clause WHERE dependente_id = ? AND usuario_id IS NULL");
            $stmt->execute($valores);
        }
    } else {
        // Se não existe perfil médico, criar um básico com as configurações de privacidade
        $colunas_existentes = [];
        $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $campos_insert = ['dependente_id'];
        $valores_insert = [$dependente_id];
        
        if (in_array('compartilhar_localizacao', $colunas)) {
            $campos_insert[] = 'compartilhar_localizacao';
            $valores_insert[] = $compartilhar_localizacao;
        }
        
        if (in_array('autorizacao_usuario', $colunas)) {
            $campos_insert[] = 'autorizacao_usuario';
            $valores_insert[] = $autorizacao_usuario;
        }
        
        $placeholders = str_repeat('?, ', count($valores_insert) - 1) . '?';
        $campos_str = implode(', ', $campos_insert);
        
        $stmt = $pdo->prepare("INSERT INTO perfis_medicos ($campos_str) VALUES ($placeholders)");
        $stmt->execute($valores_insert);
    }
    
    $_SESSION['sucesso_perfil'] = "Configurações de privacidade atualizadas com sucesso!";
    
} catch(PDOException $e) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações: " . $e->getMessage();
}

header('Location: perfil_dependente.php?id=' . $dependente_id);
exit;
?>


require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dependentes.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$dependente_id = isset($_POST['dependente_id']) ? (int)$_POST['dependente_id'] : null;

if (!$usuario_id || !$dependente_id || !$pdo) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações.";
    header('Location: dependentes.php');
    exit;
}

try {
    // Verificar se o dependente pertence ao usuário
    $stmt = $pdo->prepare("SELECT id FROM dependentes WHERE id = ? AND paciente_id = ?");
    $stmt->execute([$dependente_id, $usuario_id]);
    $dependente_existe = $stmt->fetch();
    
    if (!$dependente_existe) {
        $_SESSION['erro_perfil'] = "Dependente não encontrado ou não pertence a você.";
        header('Location: dependentes.php');
        exit;
    }
    
    $compartilhar_localizacao = isset($_POST['compartilhar_localizacao']) && $_POST['compartilhar_localizacao'] === 'sim' ? 'sim' : 'nao';
    $autorizacao_usuario = isset($_POST['autorizacao_usuario']) && $_POST['autorizacao_usuario'] === 'sim' ? 'sim' : 'nao';
    
    // Verificar se existe perfil médico do dependente
    // IMPORTANTE: Usamos dependente_id (não usuario_id) para evitar conflitos
    // A coluna dependente_id é específica para dependentes, enquanto usuario_id é para usuários
    $stmt = $pdo->prepare("SELECT id FROM perfis_medicos WHERE dependente_id = ? AND usuario_id IS NULL");
    $stmt->execute([$dependente_id]);
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
            
            // Adicionar dependente_id no final para o WHERE
            $valores[] = $dependente_id;
            
            // Garantir que estamos atualizando apenas perfis de dependentes (não de usuários)
            $stmt = $pdo->prepare("UPDATE perfis_medicos SET $set_clause WHERE dependente_id = ? AND usuario_id IS NULL");
            $stmt->execute($valores);
        }
    } else {
        // Se não existe perfil médico, criar um básico com as configurações de privacidade
        $colunas_existentes = [];
        $stmt = $pdo->query("SHOW COLUMNS FROM perfis_medicos");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $campos_insert = ['dependente_id'];
        $valores_insert = [$dependente_id];
        
        if (in_array('compartilhar_localizacao', $colunas)) {
            $campos_insert[] = 'compartilhar_localizacao';
            $valores_insert[] = $compartilhar_localizacao;
        }
        
        if (in_array('autorizacao_usuario', $colunas)) {
            $campos_insert[] = 'autorizacao_usuario';
            $valores_insert[] = $autorizacao_usuario;
        }
        
        $placeholders = str_repeat('?, ', count($valores_insert) - 1) . '?';
        $campos_str = implode(', ', $campos_insert);
        
        $stmt = $pdo->prepare("INSERT INTO perfis_medicos ($campos_str) VALUES ($placeholders)");
        $stmt->execute($valores_insert);
    }
    
    $_SESSION['sucesso_perfil'] = "Configurações de privacidade atualizadas com sucesso!";
    
} catch(PDOException $e) {
    $_SESSION['erro_perfil'] = "Erro ao atualizar configurações: " . $e->getMessage();
}

header('Location: perfil_dependente.php?id=' . $dependente_id);
exit;
?>

