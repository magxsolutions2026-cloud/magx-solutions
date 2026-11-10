<?php

require_once __DIR__ . '/app_bootstrap.php';
require_once __DIR__ . '/appointment_functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if (!function_exists('magx_inquiry_rate_limited')) {
    // Session-based throttle: max 3 inquiries per 10 minutes per visitor session.
    function magx_inquiry_rate_limited(): bool
    {
        $now = time();
        $hits = array_filter((array)($_SESSION['magx_inquiry_hits'] ?? []), function ($ts) use ($now) {
            return ($now - (int)$ts) < 600;
        });
        $_SESSION['magx_inquiry_hits'] = array_values($hits);
        return count($hits) >= 3;
    }
}

if (!function_exists('magx_inquiry_record_hit')) {
    function magx_inquiry_record_hit(): void
    {
        $_SESSION['magx_inquiry_hits'][] = time();
    }
}

if (!function_exists('magx_send_inquiry_alert_email')) {
    function magx_send_inquiry_alert_email(array $inquiry): array
    {
        $smtpHost = (string)(getenv('SMTP_HOST') ?: '');
        $smtpPort = (int)(getenv('SMTP_PORT') ?: 587);
        $smtpUser = (string)(getenv('SMTP_USER') ?: '');
        $smtpPass = (string)(getenv('SMTP_PASSWORD') ?: '');
        $fromEmail = (string)(getenv('SMTP_FROM_EMAIL') ?: getenv('SMTP_USER') ?: '');
        $fromName = (string)(getenv('SMTP_FROM_NAME') ?: 'MAGX Solutions');
        $adminEmail = (string)(getenv('ADMIN_NOTIFICATION_EMAIL') ?: 'magxsolutions2026@gmail.com');

        if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '' || $fromEmail === '' || $adminEmail === '') {
            return ['success' => false, 'message' => 'SMTP configuration is missing.'];
        }

        $e = function ($v) {
            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        };

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtpPort;
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($adminEmail, 'Admin');
            $mail->addReplyTo((string)$inquiry['email'], (string)$inquiry['full_name']);
            $mail->isHTML(true);
            $mail->Subject = 'New project inquiry from ' . (string)$inquiry['full_name'];
            $mail->Body =
                '<p>A new project inquiry was submitted from the website.</p>' .
                '<p><strong>Name:</strong> ' . $e($inquiry['full_name']) . '<br>' .
                '<strong>Email:</strong> ' . $e($inquiry['email']) . '<br>' .
                '<strong>Company:</strong> ' . $e($inquiry['company']) . '<br>' .
                '<strong>Budget:</strong> ' . $e($inquiry['budget']) . '</p>' .
                '<p><strong>Message:</strong><br>' . nl2br($e($inquiry['message'])) . '</p>';
            $mail->send();
            return ['success' => true];
        } catch (PHPMailerException $ex) {
            return ['success' => false, 'message' => 'Inquiry alert email failed: ' . $ex->getMessage()];
        }
    }
}
