<?php
session_start();
require_once __DIR__ . '/../database/db.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
$stmt->execute([$data['email'] ?? '']);
$user = $stmt->fetch();

if ($user && password_verify($data['password'] ?? '', $user['password'])) {
    unset($user['password']);
    $_SESSION['user'] = $user;
    echo json_encode(['success' => true, 'role' => $user['role']]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciais inválidas']);
}
?>