<?php
ob_start();
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/csrf.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/db.php';

start_secure_session();
validate_csrf();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    ob_clean(); http_response_code(403); echo json_encode(['error' => 'Acesso negado.']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$themeId = $data['id'] ?? null;
$teacherId = $_SESSION['user']['id'];

if (!$themeId) { ob_clean(); http_response_code(400); echo json_encode(['error' => 'ID não fornecido.']); exit; }

try {
    // Pega o caminho da imagem para deletar do disco
    $stmt = $pdo->prepare("SELECT image_path FROM themes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$themeId, $teacherId]);
    $theme = $stmt->fetch();

    if (!$theme) { ob_clean(); http_response_code(404); echo json_encode(['error' => 'Tema não encontrado.']); exit; }

    // Deleta do banco
    $stmt = $pdo->prepare("DELETE FROM themes WHERE id = ?");
    $stmt->execute([$themeId]);

    // Deleta o arquivo da imagem se existir
    if ($theme['image_path'] && file_exists(__DIR__ . '/../' . $theme['image_path'])) {
        unlink(__DIR__ . '/../' . $theme['image_path']);
    }

    ob_clean();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno ao deletar.']);
}