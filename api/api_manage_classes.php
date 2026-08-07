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

$action = $_GET['action'] ?? json_decode(file_get_contents('php://input'), true)['action'] ?? $_POST['action'] ?? '';
$teacherId = $_SESSION['user']['id'];

try {
    if ($action === 'get_classes') {
        // Busca as turmas do professor
        $stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = ? ORDER BY created_at DESC");
        $stmt->execute([$teacherId]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada turma, busca os alunos matriculados
        foreach($classes as &$c) {
            $st = $pdo->prepare("SELECT u.id, u.name, u.email FROM users u JOIN class_students cs ON u.id = cs.student_id WHERE cs.class_id = ?");
            $st->execute([$c['id']]);
            $c['students'] = $st->fetchAll(PDO::FETCH_ASSOC);
        }
        ob_clean(); echo json_encode($classes);
    }
    elseif ($action === 'create_class') {
        $name = $_POST['name'] ?? '';
        if(empty(trim($name))) throw new Exception("O nome da turma é obrigatório.");
        $stmt = $pdo->prepare("INSERT INTO classes (teacher_id, name) VALUES (?, ?)");
        $stmt->execute([$teacherId, $name]);
        ob_clean(); echo json_encode(['success'=>true]);
    }
    elseif ($action === 'delete_class') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$data['id'], $teacherId]);
        ob_clean(); echo json_encode(['success'=>true]);
    }
    elseif ($action === 'get_all_students') {
        // Busca todos os alunos cadastrados na plataforma para o professor poder adicionar
        $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'student' ORDER BY name ASC");
        ob_clean(); echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($action === 'add_student') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT IGNORE INTO class_students (class_id, student_id) VALUES (?, ?)");
        $stmt->execute([$data['class_id'], $data['student_id']]);
        ob_clean(); echo json_encode(['success'=>true]);
    }
    elseif ($action === 'remove_student') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("DELETE FROM class_students WHERE class_id = ? AND student_id = ?");
        $stmt->execute([$data['class_id'], $data['student_id']]);
        ob_clean(); echo json_encode(['success'=>true]);
    } else {
        throw new Exception("Ação inválida.");
    }
} catch(Exception $e) { 
    ob_clean(); http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); 
}