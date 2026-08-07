<?php
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/csrf.php';
require_once __DIR__ . '/../database/db.php';
header('Content-Type: application/json');

start_secure_session();
validate_csrf();

// Proteção da rota: Apenas admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

// Recebe os dados do formulário via JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos.']);
    exit;
}

$name = trim(htmlspecialchars($data['name']));
$email = trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL));
$password = $data['password'];
$role = $data['role'];

// Validação básica
if (empty($name) || empty($email) || empty($password) || !in_array($role, ['teacher', 'student', 'admin'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Preencha todos os campos corretamente.']);
    exit;
}

// Verifica se email já existe
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'E-mail já cadastrado.']);
    exit;
}

// Hash seguro da senha
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Usuário criado por admin deve trocar a senha no primeiro acesso
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, must_change_password) VALUES (?, ?, ?, ?, 1)');
    $stmt->execute([$name, $email, $hashedPassword, $role]);
    
    echo json_encode(['success' => 'Usuário cadastrado com sucesso!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao cadastrar usuário.']);
}
?>