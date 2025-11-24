<?php
/**
 * Envoi d'emails sécurisé
 * Support : SMTP (Gmail, etc.) + fallback mail()
 * Utilisé pour : approbation, notifications, etc.
 */

require_once __DIR__ . '/../controller/config.php';


/**
 * Envoie un email à l'utilisateur ou admin
 * @param string $to        Destinataire
 * @param string $subject   Sujet
 * @param string $body      Corps HTML
 * @param string $fromName  Nom expéditeur (optionnel)
 */
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
            // Fallback ci-dessous
        }
    }

    // === FALLBACK : fonction mail() ===
    $headers .= "From: " . $fromName . " <" . SMTP_USER . ">\r\n";
    return mail($to, $subject, $body, $headers);
}

/**
 * Envoi spécifique à l'admin (approbation, alertes)
 */
function envoyerMailAdmin($to, $subject, $body) {
    return envoyerMail($to, $subject, $body, 'Admin 2A10 Projet');
}

/**
 * Envoi à l'utilisateur (confirmation, rejet)
 */
function envoyerMailUtilisateur($to, $subject, $body) {
    return envoyerMail($to, $subject, $body, 'Équipe 2A10');
}
