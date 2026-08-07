<?php
/**
 * Validação de token CSRF em requisições que alteram estado (não-GET).
 *
 * Uso (após iniciar a sessão com start_secure_session()):
 *   require_once __DIR__ . '/../utils/csrf.php';
 *   validate_csrf();
 *
 * Aceita o token no header `X-CSRF-Token` ou no campo POST `csrf_token`.
 */

require_once __DIR__ . '/session.php';

function validate_csrf(): void {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }

    if (empty($_SESSION['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Sessão inválida.']);
        exit;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF inválido.']);
        exit;
    }
}
