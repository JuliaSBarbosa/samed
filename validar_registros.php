<?php
/**
 * Funções para validação de CRM e COREN usando APIs dos conselhos
 */

/**
 * Valida CRM usando API do Consultar.IO ou similar
 * @param string $crm - CRM no formato "123456-SP"
 * @return array - ['valido' => bool, 'mensagem' => string, 'dados' => array|null]
 */
function validarCRM($crm) {
    // Limpar e formatar o CRM
    $crm = strtoupper(trim($crm));
    
    // Validar formato básico
    if (!preg_match('/^([0-9]{4,10})-?([A-Z]{2})$/', $crm, $matches)) {
        return [
            'valido' => false,
            'mensagem' => 'Formato de CRM inválido. Use o formato: 123456-SP',
            'dados' => null
        ];
    }
    
    $numero = $matches[1];
    $estado = $matches[2];
    
    // Normalizar formato (garantir hífen)
    $crm_formatado = $numero . '-' . $estado;
    
    // Opção 1: API Consultar.IO (requer chave de API)
    // Descomente e configure se tiver chave de API
    /*
    $api_key = 'SUA_CHAVE_API_AQUI'; // Configure no config.php
    $url = "https://api.consultar.io/v1/crm/{$estado}/{$numero}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$api_key}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $dados = json_decode($response, true);
        if ($dados && isset($dados['situacao']) && $dados['situacao'] === 'Ativo') {
            return [
                'valido' => true,
                'mensagem' => 'CRM válido e ativo',
                'dados' => $dados
            ];
        } else {
            return [
                'valido' => false,
                'mensagem' => 'CRM encontrado mas não está ativo',
                'dados' => $dados
            ];
        }
    } else if ($http_code === 404) {
        return [
            'valido' => false,
            'mensagem' => 'CRM não encontrado no conselho regional',
            'dados' => null
        ];
    }
    */
    
    // Opção 2: Validação básica (sem API - apenas formato)
    // Por enquanto, retorna validação de formato apenas
    // Você pode implementar scraping do site do conselho se necessário
    
    // Lista de estados válidos
    $estados_validos = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 
                        'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 
                        'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
    
    if (!in_array($estado, $estados_validos)) {
        return [
            'valido' => false,
            'mensagem' => 'Estado inválido no CRM',
            'dados' => null
        ];
    }
    
    // Se chegou aqui, formato está válido
    // Para validação real, você precisará de uma API paga ou fazer scraping
    return [
        'valido' => true,
        'mensagem' => 'Formato de CRM válido (validação completa requer API)',
        'dados' => [
            'numero' => $numero,
            'estado' => $estado,
            'crm_formatado' => $crm_formatado
        ]
    ];
}

/**
 * Valida COREN usando API da Infosimples ou similar
 * @param string $coren - COREN no formato "123456-SP"
 * @return array - ['valido' => bool, 'mensagem' => string, 'dados' => array|null]
 */
function validarCOREN($coren) {
    // Limpar e formatar o COREN
    $coren = strtoupper(trim($coren));
    
    // Validar formato básico
    if (!preg_match('/^([0-9]{4,10})-?([A-Z]{2})$/', $coren, $matches)) {
        return [
            'valido' => false,
            'mensagem' => 'Formato de COREN inválido. Use o formato: 123456-SP',
            'dados' => null
        ];
    }
    
    $numero = $matches[1];
    $estado = $matches[2];
    
    // Normalizar formato (garantir hífen)
    $coren_formatado = $numero . '-' . $estado;
    
    // Opção 1: API Infosimples (requer chave de API)
    // Descomente e configure se tiver chave de API
    /*
    $api_key = 'SUA_CHAVE_API_AQUI'; // Configure no config.php
    
    // A URL varia por estado, exemplo para SP:
    if ($estado === 'SP') {
        $url = "https://api.infosimples.com/v2/consultas/coren/sp/{$numero}";
    } else if ($estado === 'PR') {
        $url = "https://api.infosimples.com/v2/consultas/coren/pr/{$numero}";
    } else {
        // Para outros estados, verifique a documentação da Infosimples
        return [
            'valido' => false,
            'mensagem' => 'Validação de COREN para este estado ainda não disponível',
            'dados' => null
        ];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$api_key}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $dados = json_decode($response, true);
        if ($dados && isset($dados['situacao']) && $dados['situacao'] === 'Ativo') {
            return [
                'valido' => true,
                'mensagem' => 'COREN válido e ativo',
                'dados' => $dados
            ];
        } else {
            return [
                'valido' => false,
                'mensagem' => 'COREN encontrado mas não está ativo',
                'dados' => $dados
            ];
        }
    } else if ($http_code === 404) {
        return [
            'valido' => false,
            'mensagem' => 'COREN não encontrado no conselho regional',
            'dados' => null
        ];
    }
    */
    
    // Opção 2: Validação básica (sem API - apenas formato)
    // Por enquanto, retorna validação de formato apenas
    
    // Lista de estados válidos
    $estados_validos = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 
                        'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 
                        'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
    
    if (!in_array($estado, $estados_validos)) {
        return [
            'valido' => false,
            'mensagem' => 'Estado inválido no COREN',
            'dados' => null
        ];
    }
    
    // Se chegou aqui, formato está válido
    // Para validação real, você precisará de uma API paga ou fazer scraping
    return [
        'valido' => true,
        'mensagem' => 'Formato de COREN válido (validação completa requer API)',
        'dados' => [
            'numero' => $numero,
            'estado' => $estado,
            'coren_formatado' => $coren_formatado
        ]
    ];
}

/**
 * Função auxiliar para fazer requisição HTTP
 * @param string $url
 * @param array $headers
 * @param int $timeout
 * @return array - ['success' => bool, 'data' => mixed, 'http_code' => int]
 */
function fazerRequisicaoAPI($url, $headers = [], $timeout = 10) {
    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'data' => null,
            'http_code' => 0,
            'erro' => 'cURL não está disponível no servidor'
        ];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erro = curl_error($ch);
    curl_close($ch);
    
    if ($erro) {
        return [
            'success' => false,
            'data' => null,
            'http_code' => $http_code,
            'erro' => $erro
        ];
    }
    
    return [
        'success' => ($http_code >= 200 && $http_code < 300),
        'data' => $response,
        'http_code' => $http_code,
        'erro' => null
    ];
}
?>

