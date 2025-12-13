<?php

require_once __DIR__ . '/../../config.php';

function envoyerMail($to, $subject, $body, $fromName = 'Administrateur 2A10') {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (defined('MAILER') && MAILER === 'smtp' && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_USER, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->isHTML(true);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("SMTP échoué : " . $e->getMessage());
        }
    }

    $headers .= "From: " . $fromName . " <" . SMTP_USER . ">\r\n";
    return mail($to, $subject, $body, $headers);
}

function envoyerMailAdmin($to, $subject, $body) {
    return envoyerMail($to, $subject, $body, 'Admin 2A10 Projet');
}

function envoyerMailUtilisateur($to, $subject, $body) {
    return envoyerMail($to, $subject, $body, 'Équipe 2A10');
}
