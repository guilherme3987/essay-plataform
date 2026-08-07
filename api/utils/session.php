<?php
/**
 * Inicia uma sessão segura com flags de cookie (HttpOnly, SameSite, Secure)
 * e garante a existência de um token CSRF por sessão.
 *
 * Exemplo de uso em qualquer página/endpoint:
 *   require_once __DIR__ . '/../utils/session.php';
 *   start_secure_session();
 */

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps, // true apenas sob HTTPS (mantém o funcionamento em localhost HTTP)
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrf_token(): string {
    return $_SESSION['csrf_token'] ?? '';
}
