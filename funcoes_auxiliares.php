<?php
/**
 * Funções auxiliares para o sistema SAMED
 */

/**
 * Valida se a senha atende aos critérios de segurança
 * Requisitos:
 * - Mínimo de 8 caracteres
 * - Pelo menos 1 letra maiúscula
 * - Pelo menos 1 letra minúscula
 * - Pelo menos 1 número
 * - Pelo menos 1 caractere especial
 * 
 * @param string $senha Senha a ser validada
 * @return array ['valido' => bool, 'mensagem' => string]
 */
function validarSenhaSegura($senha) {
    $erros = [];
    
    // Verificar comprimento mínimo
    if (strlen($senha) < 8) {
        $erros[] = "A senha deve ter no mínimo 8 caracteres.";
    }
    
    // Verificar se tem letra maiúscula
    if (!preg_match('/[A-Z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra maiúscula.";
    }
    
    // Verificar se tem letra minúscula
    if (!preg_match('/[a-z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra minúscula.";
    }
    
    // Verificar se tem número
    if (!preg_match('/[0-9]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos um número.";
    }
    
    // Verificar se tem caractere especial
    if (!preg_match('/[^A-Za-z0-9]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos um caractere especial (!@#$%^&*).";
    }
    
    if (empty($erros)) {
        return ['valido' => true, 'mensagem' => 'Senha válida.'];
    } else {
        return ['valido' => false, 'mensagem' => implode(' ', $erros)];
    }
}

/**
 * Gera um token único para recuperação de senha
 * @return string Token único
 */
function gerarTokenRecuperacao() {
    return bin2hex(random_bytes(32));
}

/**
 * Valida CPF com algoritmo completo de validação
 * @param string $cpf CPF a ser validado (com ou sem formatação)
 * @return bool True se válido, False caso contrário
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) !== 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Valida primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = ($soma * 10) % 11;
    if ($resto == 10 || $resto == 11) {
        $resto = 0;
    }
    if ($resto != intval($cpf[9])) {
        return false;
    }
    
    // Valida segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = ($soma * 10) % 11;
    if ($resto == 10 || $resto == 11) {
        $resto = 0;
    }
    if ($resto != intval($cpf[10])) {
        return false;
    }
    
    return true;
}

