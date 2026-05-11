<?php
require_once __DIR__ . '/config.php';

function pulseiraJsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function pulseiraGetJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function pulseiraRequireAuth(): void
{
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        pulseiraJsonResponse([
            'success' => false,
            'message' => 'Sessão expirada. Faça login novamente.'
        ], 401);
    }
}

function pulseiraRequirePdo(?PDO $pdo): void
{
    if (!$pdo) {
        pulseiraJsonResponse([
            'success' => false,
            'message' => 'Banco de dados indisponível no momento.'
        ], 500);
    }
}

function pulseiraGetEnvOrDefault(string $key, string $default): string
{
    $value = getenv($key);
    if ($value === false || trim($value) === '') {
        return $default;
    }

    return trim($value);
}

function pulseiraDefaultDeviceId(): string
{
    return pulseiraGetEnvOrDefault('SAMED_PULSEIRA_DEVICE_ID', 'raspberry-01');
}

function pulseiraApiToken(): string
{
    return pulseiraGetEnvOrDefault('SAMED_PULSEIRA_API_TOKEN', 'samed-pulseira-dev-token');
}

function pulseiraBuildAppBaseUrl(): string
{
    $configured = pulseiraGetEnvOrDefault('SAMED_APP_URL', '');
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if (substr($basePath, -13) === '/api/pulseira') {
        $basePath = substr($basePath, 0, -13);
    }

    return $scheme . '://' . $host . $basePath;
}

function pulseiraBuildFichaUrl(int $perfilMedicoId): string
{
    return pulseiraBuildAppBaseUrl() . '/visualizar_paciente.php?id_ficha=' . $perfilMedicoId;
}

function pulseiraGetWorkerHeaders(): array
{
    $headers = [];

    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
            $headers[$headerName] = $value;
        }
    }

    return $headers;
}

function pulseiraRequireWorkerAuth(): string
{
    $headers = pulseiraGetWorkerHeaders();
    $receivedToken = $headers['X-Pulseira-Token'] ?? '';

    if (!hash_equals(pulseiraApiToken(), (string) $receivedToken)) {
        pulseiraJsonResponse([
            'success' => false,
            'message' => 'Token do worker inválido.'
        ], 401);
    }

    $deviceId = $_GET['device_id'] ?? $headers['X-Pulseira-Device'] ?? '';
    $deviceId = trim((string) $deviceId);

    if ($deviceId === '') {
        pulseiraJsonResponse([
            'success' => false,
            'message' => 'device_id é obrigatório para o worker.'
        ], 422);
    }

    return $deviceId;
}

function pulseiraBuscarPerfilOperavel(PDO $pdo, int $perfilMedicoId, int $usuarioId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            pm.*,
            d.paciente_id AS dependente_paciente_id
        FROM perfis_medicos pm
        LEFT JOIN dependentes d ON d.id = pm.dependente_id
        WHERE pm.id = ?
          AND (
            pm.usuario_id = ?
            OR d.paciente_id = ?
          )
        LIMIT 1
    ");
    $stmt->execute([$perfilMedicoId, $usuarioId, $usuarioId]);

    $perfil = $stmt->fetch();
    return $perfil ?: null;
}

function pulseiraParseResultado(?string $json): array
{
    if (!$json) {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function pulseiraExtrairIdFichaDoPayload(?string $payload): ?int
{
    if (!$payload) {
        return null;
    }

    $payload = trim($payload);
    if ($payload === '') {
        return null;
    }

    if (ctype_digit($payload)) {
        return (int) $payload;
    }

    $parts = parse_url($payload);
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $queryParams);
        if (isset($queryParams['id_ficha']) && ctype_digit((string) $queryParams['id_ficha'])) {
            return (int) $queryParams['id_ficha'];
        }
    }

    if (preg_match('/id_ficha=(\d+)/', $payload, $matches)) {
        return (int) $matches[1];
    }

    return null;
}

function pulseiraNormalizeUid(?string $uid): string
{
    $uid = strtoupper(trim((string) $uid));
    return preg_replace('/[^A-F0-9]/', '', $uid) ?? '';
}

function pulseiraMontarPayloadDesejado(int $perfilMedicoId): array
{
    return [
        'tipo' => 'url',
        'tag_tipo' => 'NTAG215',
        'id_ficha' => $perfilMedicoId,
        'url' => pulseiraBuildFichaUrl($perfilMedicoId),
    ];
}

function pulseiraRegistrarEvento(PDO $pdo, ?int $pulseiraId, ?int $comandoId, string $tipoEvento, array $detalhes = [], bool $sucesso = true): void
{
    $stmt = $pdo->prepare("
        INSERT INTO pulseira_eventos (
            pulseira_id,
            comando_id,
            tipo_evento,
            uid_tag,
            payload_ndef,
            device_id,
            detalhes_json,
            sucesso
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $pulseiraId,
        $comandoId,
        $tipoEvento,
        $detalhes['uid_tag'] ?? null,
        $detalhes['payload_ndef'] ?? null,
        $detalhes['device_id'] ?? null,
        json_encode($detalhes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $sucesso ? 1 : 0,
    ]);
}
?>
