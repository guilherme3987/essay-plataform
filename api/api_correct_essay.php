<?php
session_start();
require_once __DIR__ . '/../database/db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    http_response_code(401); exit;
}

$essayId = $_GET['id'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);
if (!$essayId || !$data) { http_response_code(400); exit; }

$teacherId = $_SESSION['user']['id'];
$status = $data['status']; // 'draft' ou 'final'
$newEssayStatus = ($status === 'final') ? 'corrected' : 'in_correction';

try {
    $pdo->beginTransaction();

    // Atualiza status da redação
    $stmtEssay = $pdo->prepare('UPDATE essays SET status = ? WHERE id = ?');
    $stmtEssay->execute([$newEssayStatus, $essayId]);

    // Verifica se já existe correção
    $stmtCheck = $pdo->prepare('SELECT id FROM corrections WHERE essay_id = ? AND teacher_id = ?');
    $stmtCheck->execute([$essayId, $teacherId]);
    $corrId = $stmtCheck->fetchColumn();

    if ($corrId) {
        $stmtUpdate = $pdo->prepare('UPDATE corrections SET score=?, general_feedback=?, status=?, comp1_score=?, comp2_score=?, comp3_score=?, comp4_score=?, comp5_score=? WHERE id=?');
        $stmtUpdate->execute([$data['score'], $data['general_feedback'], $status, $data['comp1_score'], $data['comp2_score'], $data['comp3_score'], $data['comp4_score'], $data['comp5_score'], $corrId]);
        
        // Limpa comentários antigos
        $pdo->prepare('DELETE FROM comments WHERE correction_id = ?')->execute([$corrId]);
    } else {
        $stmtInsert = $pdo->prepare('INSERT INTO corrections (essay_id, teacher_id, score, general_feedback, status, comp1_score, comp2_score, comp3_score, comp4_score, comp5_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmtInsert->execute([$essayId, $teacherId, $data['score'], $data['general_feedback'], $status, $data['comp1_score'], $data['comp2_score'], $data['comp3_score'], $data['comp4_score'], $data['comp5_score']]);
        $corrId = $pdo->lastInsertId();
    }

    // Insere novos comentários
    if (!empty($data['comments'])) {
        $stmtComm = $pdo->prepare('INSERT INTO comments (correction_id, comment, excerpt, x_pos, y_pos, competency, page) VALUES (?, ?, ?, ?, ?, ?, 1)');
        foreach ($data['comments'] as $c) {
            $excerpt = is_string($c['excerpt']) ? $c['excerpt'] : json_encode($c['excerpt']);
            $stmtComm->execute([$corrId, $c['comment'], $excerpt, $c['x_pos'], $c['y_pos'], $c['competency']]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>