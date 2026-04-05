<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    ob_clean(); http_response_code(403); echo json_encode(['error' => 'Não autorizado.']); exit;
}

$teacherId = $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare("SELECT id, title, content, image_path, status, created_at FROM themes WHERE teacher_id = ? ORDER BY created_at DESC");
    $stmt->execute([$teacherId]);
    
    ob_clean();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno.']);
}