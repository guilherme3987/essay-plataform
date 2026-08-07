<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carrega o autoloader do Composer /vendor/autoload.php
require_once __DIR__ . '/../../vendor/autoload.php';
class Mailer {
    private $mail;
    private $enabled;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = 'UTF-8';

        // Configurações SMTP lidas de variáveis de ambiente.
        // As credenciais NÃO devem ser versionadas no código.
        $this->mail->isSMTP();
        $this->mail->Host       = getenv('SMTP_HOST') ?: '';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = getenv('SMTP_USER') ?: '';
        $this->mail->Password   = getenv('SMTP_PASS') ?: '';
        $this->mail->SMTPSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = (int)(getenv('SMTP_PORT') ?: 587);

        // Remetente padrão
        $from = getenv('SMTP_FROM') ?: 'no-reply@redacaopro.local';
        $fromName = getenv('SMTP_FROM_NAME') ?: 'Plataforma RedaçãoPro';
        $this->mail->setFrom($from, $fromName);

        $this->enabled = ($this->mail->Host !== '' && $this->mail->Username !== '' && $this->mail->Password !== '');
        if (!$this->enabled) {
            error_log('Mailer: credenciais SMTP não configuradas (SMTP_HOST/SMTP_USER/SMTP_PASS).');
        }
    }

    /**
     * Função principal para enviar e-mails
     * Função principal para enviar e-mails
     * @param string $toEmail E-mail do destinatário
     * @param string $toName Nome do destinatário
     * @param string $subject Assunto do e-mail
     * @param string $htmlBody Corpo do e-mail em formato HTML
     * @return bool Retorna true se o e-mail foi enviado com sucesso, false caso contrário
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool{
        if (!$this->enabled) {
            error_log("Mailer desabilitado. E-mail para $toEmail não enviado.");
            return false;
        }

        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);

            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            
            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; max-w-2xl mx-auto; p-6 text-slate-800;'>
                    <h2 style='color: #4f46e5;'>RedaçãoPro</h2>
                    <hr style='border: 1px solid #e2e8f0;'>
                    <br>
                    $htmlBody
                    <br><br>
                    <hr style='border: 1px solid #e2e8f0;'>
                    <p style='font-size: 12px; color: #64748b;'>Este é um e-mail automático, por favor não responda.</p>
                </div>
            ";

            return $this->mail->send();
        } catch (Exception $e) {
            // Registra o erro no log do servidor
            error_log("Erro ao enviar e-mail para $toEmail. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}