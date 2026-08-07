<?php
// Inicia o buffer para impedir que Warnings quebrem o JSON
ob_start(); 
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/csrf.php';
header('Content-Type: application/json; charset=utf-8');

$dbPath = __DIR__ . '/../database/db.php';
if (!file_exists($dbPath)) {
    ob_clean(); // Limpa o buffer antes de enviar o erro
    http_response_code(500);
    echo json_encode(['error' => 'Arquivo db.php não encontrado no servidor.']);
    exit;
}
require_once $dbPath;

start_secure_session();
validate_csrf();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    ob_clean();
    http_response_code(401); 
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    ob_clean();
    http_response_code(400); 
    echo json_encode(['error' => 'Erro interno de upload.']); 
    exit;
}

$file = $_FILES['file'];
$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
$allowedMime = [
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
    'pdf'  => ['application/pdf'],
];
$maxSize = 10 * 1024 * 1024; // 10 MB

if (!in_array($ext, $allowed)) {
    ob_clean();
    http_response_code(400); 
    echo json_encode(['error' => 'Formato não suportado.']); 
    exit;
}

if ($file['size'] > $maxSize) {
    ob_clean();
    http_response_code(400); 
    echo json_encode(['error' => 'Arquivo muito grande. Tamanho máximo: 10 MB.']); 
    exit;
}

// Valida o tipo real do arquivo (magic bytes) e não apenas a extensão
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowedMime[$ext], true)) {
    ob_clean();
    http_response_code(400); 
    echo json_encode(['error' => 'Conteúdo do arquivo inválido para a extensão informada.']); 
    exit;
}

$uploadDir = __DIR__ . '/uploads/redacoes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Função nativa para limpar acentos/espaços sem depender do mbstring do Docker
function sanitizeString($str) {
    // Remove tudo que não for letra ou número e substitui por underline
    $str = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($str));
    // Remove underlines duplos
    return trim(preg_replace('/_+/', '_', $str), '_');
}

$safeUserName = sanitizeString($_SESSION['user']['name']);
$safeOriginalName = sanitizeString($originalName);

// Exemplo: joao_silva_redacao_enem_170000000.pdf
$filename = $safeUserName . '_' . $safeOriginalName . '_' . time() . '.' . $ext;
$destination = $uploadDir . $filename;
$publicPath = 'uploads/redacoes/' . $filename; 

if (move_uploaded_file($file['tmp_name'], $destination)) {
    try {
        $stmt = $pdo->prepare('INSERT INTO essays (user_id, file_path, status) VALUES (?, ?, "pending")');
        $stmt->execute([$_SESSION['user']['id'], $publicPath]);
        
        ob_clean(); // Garante que só o JSON será impresso
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        ob_clean();
        http_response_code(500); 
        echo json_encode(['error' => 'Erro no banco de dados.']);
    }
} else {
    ob_clean();
    http_response_code(500); 
    echo json_encode(['error' => 'Falha ao salvar o arquivo no disco. Verifique as permissões.']);
}