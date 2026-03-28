<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    http_response_code(401); exit;
}
if (!isset($_FILES['file'])) {
    http_response_code(400); echo json_encode(['error' => 'Nenhum arquivo foi recebido pelo PHP.']); exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400); 
    echo json_encode([
        'error' => 'O arquivo não chegou no servidor.',
        'arquivos_recebidos_pelo_php' => $_FILES,
        'texto_recebido_pelo_php' => $_POST,
        'tipo_da_requisicao' => $_SERVER['CONTENT_TYPE'] ?? 'Nenhum'
    ]); 
    exit;
}

if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); 
    echo json_encode(['error' => 'Erro interno de upload código: ' . $_FILES['file']['error']]); 
    exit;
}
$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'pdf'];

if (!in_array($ext, $allowed)) {
    http_response_code(400); echo json_encode(['error' => 'Formato não suportado.']); exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid('essay_') . '.' . $ext;
$destination = $uploadDir . $filename;
$publicPath = 'uploads/' . $filename; 

if (move_uploaded_file($file['tmp_name'], $destination)) {
    $stmt = $pdo->prepare('INSERT INTO essays (user_id, file_path, status) VALUES (?, ?, "pending")');
    $stmt->execute([$_SESSION['user']['id'], $publicPath]);
    
    echo json_encode(['success' => true]);
} else {
    http_response_code(500); echo json_encode(['error' => 'Falha ao salvar o arquivo no servidor.']);
}
?>