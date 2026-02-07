<?php
// Load PHPMailer classes
// Using __DIR__ ensures it looks in the current directory, preventing path errors
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('sendEmailNotification')) {
    function sendEmailNotification($toEmail, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // 1. Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            
            // --- ENTER YOUR EMAIL DETAILS HERE ---
            // NOTE: You MUST use an App Password, not your normal Gmail password
            $mail->Username   = 'testnetcsc@gmail.com'; 
            $mail->Password   = 'ionh awmn scnu tbfz'; 
            // -------------------------------------

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587; 

            // 2. Recipients
            $mail->setFrom('no-reply@mct-lostfound.com', 'MCT Lost & Found Admin');
            $mail->addAddress($toEmail); 

            // 3. Content
            $mail->isHTML(true); 
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // This will help you debug if the email fails to send
            return "Mailer Error: " . $mail->ErrorInfo;
        }
    }
}
?>