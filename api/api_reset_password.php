<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/utils/rate_limit.php';

rate_limit_check('reset_password', 10, 3600); // máx. 10 tentativas por IP/hora

$data = json_decode(file_get_contents('php://input'), true);

$token    = trim($data['token'] ?? '');
$password = $data['password'] ?? '';
$confirm  = $data['confirm_password'] ?? '';

// 1. Validações básicas
if (empty($token) || empty($password) || empty($confirm)) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Preencha todos os campos.']); exit;
}
if ($password !== $confirm) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'As senhas não coincidem.']); exit;
}
if (strlen($password) < 6) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'A senha deve ter pelo menos 6 caracteres.']); exit;
}

try {
    // 2. Busca o usuário pelo token E verifica se não expirou (NOW() compara com o reset_token_expires)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        rate_limit_hit('reset_password');
        ob_clean(); http_response_code(400); echo json_encode(['error' => 'Este link de recuperação é inválido ou já expirou. Por favor, solicite um novo.']); exit;
    }

    // 3. Criptografa a nova senha
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // 4. Salva a nova senha e LIMPA o token e a flag de mudança obrigatória
    $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL, must_change_password = 0 WHERE id = ?");
    $update->execute([$hash, $user['id']]);

    ob_clean();
    echo json_encode(['success' => 'Senha alterada com sucesso! Você já pode fazer login.']);

} catch (Exception $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno do servidor.']);
}