<?php
session_start();
require_once __DIR__ . '/../database/db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    http_response_code(401); exit;
}

$essayId = $_GET['id'] ?? null;
if (!$essayId) { http_response_code(400); exit; }

$stmt = $pdo->prepare('SELECT e.*, u.name as student_name FROM essays e JOIN users u ON e.user_id = u.id WHERE e.id = ?');
$stmt->execute([$essayId]);
$essay = $stmt->fetch();

if (!$essay) { http_response_code(404); exit; }

// Verifica se já existe um rascunho
$stmtCorr = $pdo->prepare('SELECT * FROM corrections WHERE essay_id = ? AND teacher_id = ?');
$stmtCorr->execute([$essayId, $_SESSION['user']['id']]);
$correction = $stmtCorr->fetch();

if ($correction) {
    $stmtComm = $pdo->prepare('SELECT * FROM comments WHERE correction_id = ?');
    $stmtComm->execute([$correction['id']]);
    $correction['comments'] = $stmtComm->fetchAll();
    $essay['correction'] = $correction;
}

header('Content-Type: application/json');
echo json_encode($essay);
?>