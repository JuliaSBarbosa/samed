<?php
// Configuração de conexão com o banco de dados
// Ajuste estas configurações conforme seu ambiente XAMPP

$host = 'localhost';
$dbname = 'samed';
$username = 'root';
$password = '';

// Tentar conectar ao banco de dados (não obrigatório se usar usuários padrão)
$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Banco de dados não disponível, mas sistema pode funcionar com usuários padrão
    $pdo = null;
}

// Iniciar sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// USUÁRIOS PADRÃO PARA TESTE
// ============================================
// Estes usuários funcionam mesmo sem banco de dados
// Use estes logins para testar o sistema:
//
// PACIENTE:
//   Email: paciente@samed.com
//   Senha: 123456
//
// MÉDICO:
//   Email: medico@samed.com
//   Senha: 123456
//
// ENFERMEIRO:
//   Email: enfermeiro@samed.com
//   Senha: 123456
// ============================================

$usuarios_padrao = [
    [
        'id' => 1,
        'nome' => 'João Silva',
        'email' => 'paciente@samed.com',
        'senha' => '123456', // Senha em texto simples para comparação
        'tipo' => 'paciente',
        'crm' => null,
        'coren' => null
    ],
    [
        'id' => 2,
        'nome' => 'Dr. Maria Santos',
        'email' => 'medico@samed.com',
        'senha' => '123456',
        'tipo' => 'medico',
        'crm' => '123456-SP',
        'coren' => null
    ],
    [
        'id' => 3,
        'nome' => 'Enf. Carlos Oliveira',
        'email' => 'enfermeiro@samed.com',
        'senha' => '123456',
        'tipo' => 'enfermeiro',
        'crm' => null,
        'coren' => '654321-SP'
    ]
];
?>

