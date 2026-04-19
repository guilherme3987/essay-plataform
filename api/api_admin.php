<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

$dbPath = __DIR__ . '/../database/db.php';
if (!file_exists($dbPath)) { ob_clean(); http_response_code(500); echo json_encode(['error' => 'Arquivo db.php não encontrado.']); exit; }
require_once $dbPath;

// Apenas administradores podem acessar esta API
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    ob_clean(); http_response_code(403); echo json_encode(['error' => 'Acesso negado. Apenas administradores.']); exit;
}

$action = $_GET['action'] ?? json_decode(file_get_contents('php://input'), true)['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'get_stats') {
        // Busca estatísticas gerais do sistema
        $stats = [
            'students' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
            'teachers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
            'essays_total' => $pdo->query("SELECT COUNT(*) FROM essays")->fetchColumn(),
            'essays_corrected' => $pdo->query("SELECT COUNT(*) FROM essays WHERE status = 'corrected'")->fetchColumn(),
            'themes' => $pdo->query("SELECT COUNT(*) FROM themes")->fetchColumn()
        ];
        ob_clean(); echo json_encode($stats);
    } 
    elseif ($action === 'get_users') {
        // Busca todos os usuários, exceto a si mesmo (para não correr o risco de se auto-deletar)
        $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id != ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user']['id']]);
        ob_clean(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } 
    elseif ($action === 'delete_user') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) throw new Exception("ID do usuário não fornecido.");
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$data['id']]);
        ob_clean(); echo json_encode(['success' => 'Usuário excluído com sucesso.']);
    } 
    elseif ($action === 'add_user') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("E-mail inválido.");
        if (strlen($password) < 6) throw new Exception("A senha deve ter no mínimo 6 caracteres.");

        // Verifica duplicidade de e-mail
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) throw new Exception("Este e-mail já está cadastrado.");

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role]);

        ob_clean(); echo json_encode(['success' => 'Usuário cadastrado com sucesso!']);
    } 
    else {
        throw new Exception("Ação inválida.");
    }
} catch (Exception $e) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => $e->getMessage()]);
} catch (PDOException $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno de banco de dados.']);
}