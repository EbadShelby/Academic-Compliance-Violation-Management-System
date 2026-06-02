<?php

require_once __DIR__ . '/../vendors/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendors/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendors/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Mailer Service
 *
 * Uses PHPMailer for reliable email delivery.
 */
class MailerService
{
    private string $fromEmail;
    private string $fromName;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUser;
    private string $smtpPass;

    public function __construct()
    {
        $this->fromName  = defined('APP_NAME') ? APP_NAME : 'System';
        
        // Grab config from constants defined in app.php
        $this->smtpHost = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $this->smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->smtpUser = defined('SMTP_USER') ? SMTP_USER : '';
        $this->smtpPass = defined('SMTP_PASS') ? str_replace(' ', '', SMTP_PASS) : '';
        
        $this->fromEmail = !empty($this->smtpUser) ? $this->smtpUser : 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    /**
     * Send an HTML email via SMTP.
     *
     * @param string $to      Recipient email address
     * @param string $subject Email subject
     * @param string $html    HTML body content
     * @return bool True if email was successfully sent
     * @throws Exception If connection or authentication fails
     */
    public function sendHtml(string $to, string $subject, string $html): bool
    {
        $mail = new PHPMailer(true);

        try {
            if (!empty($this->smtpUser) && !empty($this->smtpPass)) {
                // Server settings for SMTP
                $mail->isSMTP();
                $mail->Host       = $this->smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $this->smtpUser;
                $mail->Password   = $this->smtpPass;
                $mail->SMTPSecure = ($this->smtpPort === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $this->smtpPort;
            } else {
                // Fallback to mail() if no SMTP credentials are provided
                $mail->isMail();
            }

            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($this->fromEmail, $this->fromName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $this->wrapInTemplate($subject, $html);
            $mail->AltBody = strip_tags(str_replace(['<br>', '<p>'], "\n", $html));

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            throw new Exception("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * Wrap the provided HTML content in a consistent, styled email template.
     */
    private function wrapInTemplate(string $subject, string $content): string
    {
        $appName = htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'System');
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$subject}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 32px 24px;
            line-height: 1.6;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 16px 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 16px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$appName}</h1>
        </div>
        <div class="content">
            {$content}
        </div>
        <div class="footer">
            &copy; {$year} {$appName}. All rights reserved.<br>
            This is an automated message, please do not reply.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
