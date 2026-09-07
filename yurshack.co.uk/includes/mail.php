<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Mailer
{
    /**
     * @param list<array{path:string,name:string}> $attachments
     */
    public static function send(
        string $subject,
        string $htmlBody,
        string $textBody,
        ?string $replyTo = null,
        array $attachments = []
    ): bool {
        $to = AppConfig::supportEmail();
        $from = AppConfig::get('MAIL_FROM', 'noreply@yurshack.com') ?? 'noreply@yurshack.com';
        $fromName = AppConfig::get('MAIL_FROM_NAME', 'Yur Shack') ?? 'Yur Shack';

        $autoload = AppConfig::composerAutoload();
        if ($autoload !== null) {
            require_once $autoload;
            if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
                return self::sendWithPhpMailer($to, $from, $fromName, $subject, $htmlBody, $textBody, $replyTo, $attachments);
            }
        }

        return self::sendWithMail($to, $from, $fromName, $subject, $htmlBody, $textBody, $replyTo);
    }

    /**
     * @param list<array{path:string,name:string}> $attachments
     */
    private static function sendWithPhpMailer(
        string $to,
        string $from,
        string $fromName,
        string $subject,
        string $htmlBody,
        string $textBody,
        ?string $replyTo,
        array $attachments
    ): bool {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $host = AppConfig::get('SMTP_HOST');
            if ($host) {
                $mail->isSMTP();
                $mail->Host = $host;
                $mail->Port = (int) (AppConfig::get('SMTP_PORT', '587') ?? '587');
                $user = AppConfig::get('SMTP_USER');
                $pass = AppConfig::get('SMTP_PASS');
                if ($user) {
                    $mail->SMTPAuth = true;
                    $mail->Username = $user;
                    $mail->Password = $pass ?? '';
                }
                $secure = AppConfig::get('SMTP_SECURE', 'tls');
                if ($secure) {
                    $mail->SMTPSecure = $secure;
                }
            }

            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }
            foreach ($attachments as $file) {
                $mail->addAttachment($file['path'], $file['name']);
            }
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('Yur Shack mail error: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendWithMail(
        string $to,
        string $from,
        string $fromName,
        string $subject,
        string $htmlBody,
        string $textBody,
        ?string $replyTo
    ): bool {
        $boundary = 'bnd_' . bin2hex(random_bytes(8));
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'From: ' . sprintf('"%s" <%s>', addslashes($fromName), $from),
            'X-Mailer: YurShack',
        ];
        if ($replyTo) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
            . $textBody . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . $htmlBody . "\r\n"
            . "--{$boundary}--\r\n";

        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
        if (!$ok) {
            error_log('Yur Shack mail() failed for subject: ' . $subject);
        }
        return (bool) $ok;
    }
}

/**
 * Escape for HTML email bodies.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
