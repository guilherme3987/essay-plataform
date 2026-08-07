<?php
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/csrf.php';
require_once __DIR__ . '/utils/rate_limit.php';
require_once __DIR__ . '/../database/db.php';

start_secure_session();
validate_csrf();
rate_limit_check('login', 5, 300); // máx. 5 tentativas por IP a cada 5 min

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $pdo->prepare('SELECT id, name, password, role, must_change_password FROM users WHERE email = ?');
$stmt->execute([$data['email'] ?? '']);
$user = $stmt->fetch();

if ($user && password_verify($data['password'] ?? '', $user['password'])) {
    rate_limit_clear('login'); // sucesso limpa o contador de falhas

    // Previne session fixation: nova ID de sessão após autenticação
    session_regenerate_id(true);

    unset($user['password']);
    $_SESSION['user'] = $user;

    echo json_encode([
        'success' => true,
        'role' => $user['role'],
        'must_change_password' => (bool)$user['must_change_password'],
    ]);
} else {
    rate_limit_hit('login'); // registra a falha
    http_response_code(401);
    echo json_encode(['error' => 'Credenciais inválidas']);
}
?>