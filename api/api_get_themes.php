<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

$dbPath = __DIR__ . '/../database/db.php';
if (!file_exists($dbPath)) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Arquivo db.php não encontrado.']);
    exit;
}
require_once $dbPath;

if (!isset($_SESSION['user'])) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado.']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, title, content, image_path, created_at FROM themes WHERE status = 'published' ORDER BY created_at DESC");
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_clean();
    echo json_encode($themes);
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno ao buscar os temas.']);
}