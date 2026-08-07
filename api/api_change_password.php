<?php
ob_start();
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/csrf.php';
header('Content-Type: application/json; charset=utf-8');

$dbPath = __DIR__ . '/../database/db.php';
if (!file_exists($dbPath)) { ob_clean(); http_response_code(500); echo json_encode(['error' => 'Arquivo db.php não encontrado.']); exit; }
require_once $dbPath;

start_secure_session();
validate_csrf();

if (!isset($_SESSION['user'])) {
    ob_clean(); http_response_code(401); echo json_encode(['error' => 'Faça login para continuar.']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';
$confirm  = $data['confirm_password'] ?? '';

if ($password !== $confirm) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'As senhas não coincidem.']); exit;
}
if (strlen($password) < 6) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'A senha deve ter pelo menos 6 caracteres.']); exit;
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $update = $pdo->prepare('UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?');
    $update->execute([$hash, $_SESSION['user']['id']]);

    ob_clean();
    echo json_encode(['success' => 'Senha alterada com sucesso!']);
} catch (Exception $e) {
    error_log('Erro ao alterar senha: ' . $e->getMessage());
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno ao alterar a senha.']);
}
