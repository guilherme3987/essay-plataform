<?php
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/../database/db.php';

start_secure_session();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    http_response_code(401); exit;
}

// Traz redações pendentes ou rascunhos feitos por esse professor específico
$teacherId = $_SESSION['user']['id'];

$stmt = $pdo->prepare('
    SELECT e.id, e.status, e.created_at, u.name as student_name 
    FROM essays e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN corrections c ON e.id = c.essay_id
    WHERE e.status = "pending" 
       OR (e.status = "in_correction" AND c.teacher_id = ?)
    ORDER BY e.created_at ASC
');
$stmt->execute([$teacherId]);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
?>