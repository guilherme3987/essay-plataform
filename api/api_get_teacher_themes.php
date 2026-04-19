<?php
ob_start(); session_start(); header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    ob_clean(); http_response_code(403); exit;
}

$teacherId = $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare("SELECT id, title, content, image_path, pdf_path, status, created_at FROM themes WHERE teacher_id = ? ORDER BY created_at DESC");
    $stmt->execute([$teacherId]);
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca as turmas selecionadas para cada tema
    foreach($themes as &$t) {
        $st = $pdo->prepare("SELECT class_id FROM theme_classes WHERE theme_id = ?");
        $st->execute([$t['id']]);
        $t['class_ids'] = $st->fetchAll(PDO::FETCH_COLUMN); // Retorna um array [1, 2, 5...]
    }
    
    ob_clean(); echo json_encode($themes);
} catch (PDOException $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno.']);
}