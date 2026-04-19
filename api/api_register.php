<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

$dbPath = __DIR__ . '/../database/db.php';
if (!file_exists($dbPath)) { ob_clean(); http_response_code(500); echo json_encode(['error' => 'Arquivo de banco de dados não encontrado.']); exit; }
require_once $dbPath;

// Recebe os dados em formato JSON do fetch
$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role = $data['role'] ?? '';

// Validações básicas
if (empty($name) || empty($email) || empty($password) || empty($role)) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Todos os campos são obrigatórios.']); exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Formato de e-mail inválido.']); exit;
}

if (strlen($password) < 6) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'A senha deve ter pelo menos 6 caracteres.']); exit;
}

// Segurança: Impede que alguém force a criação de um administrador via API
if (!in_array($role, ['student', 'teacher'])) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Tipo de conta inválido.']); exit;
}

try {
    // Verifica se o e-mail já existe no banco
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        ob_clean(); http_response_code(409); echo json_encode(['error' => 'Este e-mail já está em uso.']); exit;
    }

    // Criptografa a senha com Bcrypt (padrão seguro da indústria)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insere o usuário no banco de dados
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword, $role]);

    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Conta criada com sucesso!']);
} catch (PDOException $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno no banco de dados.']);
}