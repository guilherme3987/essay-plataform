<?php
/**
 * Rate limiting simples baseado em arquivos (por IP).
 * Usa o diretório temporário do sistema para evitar problemas de permissão.
 *
 * Uso:
 *   require_once __DIR__ . '/../utils/rate_limit.php';
 *   rate_limit_check('login', 5, 300); // máx. 5 tentativas em 5 minutos
 *   ...
 *   rate_limit_hit('login'); // registra falha após autenticação inválida
 */

function rate_limit_dir(): string {
    $dir = sys_get_temp_dir() . '/redacaopro_rate_limit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    return $dir;
}

function rate_limit_key(string $action): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return rate_limit_dir() . '/' . $action . '_' . md5($ip) . '.lock';
}

/**
 * Verifica se o limite foi atingido. Em caso positivo, encerra a requisição
 * com 429 Too Many Requests.
 */
function rate_limit_check(string $action, int $maxAttempts = 5, int $windowSeconds = 300): void {
    $file = rate_limit_key($action);
    $now = time();
    $entries = [];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $entries = json_decode($raw, true);
        }
    }
    if (!is_array($entries)) {
        $entries = [];
    }

    // Mantém apenas tentativas dentro da janela
    $entries = array_values(array_filter($entries, fn($t) => $now - $t < $windowSeconds));

    if (count($entries) >= $maxAttempts) {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Muitas tentativas. Aguarde alguns minutos e tente novamente.']);
        exit;
    }
}

/**
 * Registra uma tentativa (falha) para fins de limitação.
 */
function rate_limit_hit(string $action, int $windowSeconds = 300): void {
    $file = rate_limit_key($action);
    $now = time();
    $entries = [];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $entries = json_decode($raw, true);
        }
    }
    if (!is_array($entries)) {
        $entries = [];
    }

    $entries = array_values(array_filter($entries, fn($t) => $now - $t < $windowSeconds));
    $entries[] = $now;

    @file_put_contents($file, json_encode($entries), LOCK_EX);
}

/**
 * Limpa as tentativas registradas (usado após autenticação bem-sucedida).
 */
function rate_limit_clear(string $action): void {
    $file = rate_limit_key($action);
    if (is_file($file)) {
        @unlink($file);
    }
}
