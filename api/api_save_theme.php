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

// --- NOVO: Captura as turmas selecionadas ---
$classIds = isset($_POST['class_ids']) ? json_decode($_POST['class_ids'], true) : [];

if (empty(trim($title)) || empty(trim($content))) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Título e conteúdo são obrigatórios.']); exit;
}

if ($status === 'published' && empty($classIds)) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Para publicar, selecione pelo menos uma turma.']); exit;
}

// Trata Imagem
$imagePath = null;
$updateImage = false;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/themes/';    
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
        ob_clean(); http_response_code(400); echo json_encode(['error' => 'Formato de imagem inválido.']); exit;
    }
    $fileName = uniqid('theme_') . '.' . $fileExt;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
        $imagePath = 'uploads/themes/' . $fileName; 
        $updateImage = true;
    }
}

// Trata PDF
$pdfPath = null;
$updatePdf = false;
if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
    $uploadDirPdf = __DIR__ . '/uploads/themes/pdfs/';    
    if (!is_dir($uploadDirPdf)) mkdir($uploadDirPdf, 0777, true);
    
    $fileExtPdf = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
    if ($fileExtPdf !== 'pdf') {
        ob_clean(); http_response_code(400); echo json_encode(['error' => 'O material de apoio deve ser um arquivo PDF.']); exit;
    }
    $fileNamePdf = uniqid('theme_pdf_') . '.' . $fileExtPdf;
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadDirPdf . $fileNamePdf)) {
        $pdfPath = 'uploads/themes/pdfs/' . $fileNamePdf; 
        $updatePdf = true;
    }
}

try {
    $pdo->beginTransaction(); // Inicia transação segura

    if ($themeId) {
        $stmt = $pdo->prepare("SELECT id FROM themes WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$themeId, $teacherId]);
        if (!$stmt->fetch()) { throw new Exception('Tema não encontrado.'); }

        $sql = "UPDATE themes SET title=?, content=?, status=?";
        $params = [$title, $content, $status];

        if ($updateImage) { $sql .= ", image_path=?"; $params[] = $imagePath; }
        if ($updatePdf) { $sql .= ", pdf_path=?"; $params[] = $pdfPath; }

        $sql .= " WHERE id=?";
        $params[] = $themeId;

        $pdo->prepare($sql)->execute($params);
    } else {
        $stmt = $pdo->prepare("INSERT INTO themes (teacher_id, title, content, image_path, pdf_path, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$teacherId, $title, $content, $imagePath, $pdfPath, $status]);
        $themeId = $pdo->lastInsertId();
    }

    // --- NOVO: Limpa as turmas antigas e insere as novas ---
    $pdo->prepare("DELETE FROM theme_classes WHERE theme_id = ?")->execute([$themeId]);
    if (!empty($classIds)) {
        $stmtTC = $pdo->prepare("INSERT INTO theme_classes (theme_id, class_id) VALUES (?, ?)");
        foreach ($classIds as $cId) {
            $stmtTC->execute([$themeId, $cId]);
        }
    }
    
    $pdo->commit();
    ob_clean();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
}