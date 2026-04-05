<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

$dbPath = __DIR__ . '/../database/db.php';
if (!file_exists($dbPath)) { ob_clean(); http_response_code(500); echo json_encode(['error' => 'Arquivo db.php não encontrado.']); exit; }
require_once $dbPath;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    ob_clean(); http_response_code(403); echo json_encode(['error' => 'Acesso negado.']); exit;
}

$themeId = $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$status = $_POST['status'] ?? 'published';
$teacherId = $_SESSION['user']['id'];

if (empty(trim($title)) || empty(trim($content))) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Título e conteúdo são obrigatórios.']); exit;
}

$imagePath = null;
$updateImage = false;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/themes/';    
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
        ob_clean(); http_response_code(400); echo json_encode(['error' => 'Formato inválido.']); exit;
    }

    $fileName = uniqid('theme_') . '.' . $fileExt;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
        $imagePath = 'uploads/themes/' . $fileName; 
        $updateImage = true;
    }
}

try {
    if ($themeId) {
        // Atualizar tema existente
        $stmt = $pdo->prepare("SELECT id FROM themes WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$themeId, $teacherId]);
        if (!$stmt->fetch()) { ob_clean(); http_response_code(403); echo json_encode(['error' => 'Tema não encontrado ou não pertence a você.']); exit; }

        if ($updateImage) {
            $stmt = $pdo->prepare("UPDATE themes SET title=?, content=?, status=?, image_path=? WHERE id=?");
            $stmt->execute([$title, $content, $status, $imagePath, $themeId]);
        } else {
            $stmt = $pdo->prepare("UPDATE themes SET title=?, content=?, status=? WHERE id=?");
            $stmt->execute([$title, $content, $status, $themeId]);
        }
    } else {
        // Criar novo tema
        $stmt = $pdo->prepare("INSERT INTO themes (teacher_id, title, content, image_path, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$teacherId, $title, $content, $imagePath, $status]);
    }
    
    ob_clean();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    ob_clean(); 
    http_response_code(500); 
    echo json_encode(['error' => 'Erro no Banco: ' . $e->getMessage()]);
}