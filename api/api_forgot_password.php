<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/utils/Mailer.php';
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/csrf.php';
require_once __DIR__ . '/utils/rate_limit.php';

start_secure_session();
validate_csrf();
rate_limit_check('forgot_password', 5, 3600); // máx. 5 solicitações por IP/hora

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_clean(); http_response_code(400); echo json_encode(['error' => 'Por favor, insira um e-mail válido.']); exit;
}

try {
    // 1. Verifica se o usuário existe
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // DICA DE SEGURANÇA: Mesmo que o e-mail não exista, sempre retornamos "Sucesso". 
    // Isso impede que hackers usem o formulário para descobrir quem tem conta na sua plataforma.
    if ($user) {
        // 2. Gera um token seguro de 64 caracteres e define validade para 1 hora
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 3. Salva no banco de dados
        $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $update->execute([$token, $expires, $user['id']]);

        // 4. Dispara o e-mail
        $mailer = new Mailer();
        
        // Monta o link de reset usando HTTPS quando disponível
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $scheme = $isHttps ? 'https' : 'http';
        $host = getenv('APP_HOST') ?: ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
        $resetLink = "$scheme://$host/pages/auth/reset_password.php?token=" . $token;
        $firstName = explode(' ', $user['name'])[0];

        $corpoEmail = "
            <h3 style='color: #1e293b; font-size: 20px; margin-bottom: 16px;'>Olá, $firstName!</h3>
            <p style='color: #475569; font-size: 15px; line-height: 1.6;'>Recebemos uma solicitação para redefinir a senha da sua conta na plataforma <strong>RedaçãoPro</strong>.</p>
            <p style='color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 24px;'>Para criar uma nova senha, clique no botão abaixo (este link expira em 1 hora):</p>
            
            <a href='$resetLink' style='display: inline-block; padding: 14px 28px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;'>CRIAR NOVA SENHA</a>
            
            <p style='color: #64748b; font-size: 13px; line-height: 1.5; margin-top: 32px;'>Se você não solicitou a redefinição, apenas ignore este e-mail. Sua conta continua segura.</p>
        ";

        $mailer->send($email, $user['name'], 'Recuperação de Senha - RedaçãoPro', $corpoEmail);
    }

    ob_clean();
    echo json_encode(['success' => 'Se o e-mail estiver cadastrado, você receberá um link de recuperação em instantes.']);

} catch (Exception $e) {
    ob_clean(); http_response_code(500); echo json_encode(['error' => 'Erro interno do servidor. Tente novamente mais tarde.']);
}