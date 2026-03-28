<?php
session_start();
require_once __DIR__ . '/../database/db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    http_response_code(401); exit;
}

$userId = $_SESSION['user']['id'];

// Busca as redações e as correções vinculadas
$stmt = $pdo->prepare('
    SELECT e.id, e.file_path, e.status, e.created_at, 
           c.score, c.general_feedback, c.comp1_score, c.comp2_score, c.comp3_score, c.comp4_score, c.comp5_score
    FROM essays e
    LEFT JOIN corrections c ON e.id = c.essay_id
    WHERE e.user_id = ?
    ORDER BY e.created_at DESC
');
$stmt->execute([$userId]);
$essays = $stmt->fetchAll();

// Formata o JSON para o frontend processar facilmente
$response = [];
foreach ($essays as $row) {
    $essay = [
        'id' => $row['id'],
        'file_path' => $row['file_path'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'correction' => null
    ];

    if ($row['status'] === 'corrected') {
        // Busca os comentários visuais
        $stmtComments = $pdo->prepare('SELECT * FROM comments WHERE correction_id = (SELECT id FROM corrections WHERE essay_id = ?)');
        $stmtComments->execute([$row['id']]);
        
        $essay['correction'] = [
            'score' => $row['score'],
            'general_feedback' => $row['general_feedback'],
            'comp1_score' => $row['comp1_score'],
            'comp2_score' => $row['comp2_score'],
            'comp3_score' => $row['comp3_score'],
            'comp4_score' => $row['comp4_score'],
            'comp5_score' => $row['comp5_score'],
            'comments' => $stmtComments->fetchAll()
        ];
    }
    $response[] = $essay;
}

header('Content-Type: application/json');
echo json_encode($response);
?>