<?php
ob_start(); require_once __DIR__ . '/utils/session.php'; header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/db.php';

start_secure_session();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    ob_clean(); http_response_code(401); echo json_encode(['error' => 'Não autorizado.']); exit;
}

$studentId = $_SESSION['user']['id'];

try {
    // Faz o Join mágico: Temas -> Vínculo com Turmas -> Vínculo com Alunos
    $stmt = $pdo->prepare("
        SELECT t.id, t.title, t.content, t.image_path, t.pdf_path, t.created_at 
        FROM themes t
        JOIN theme_classes tc ON t.id = tc.theme_id
        JOIN class_students cs ON tc.class_id = cs.class_id
        WHERE t.status = 'published' AND cs.student_id = ?
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$studentId]);
    
    ob_clean();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno.']);
}