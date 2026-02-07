<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- CRITICAL FIX: Added __DIR__ and /src/ ---
// This ensures PHP looks inside the 'src' folder where the files actually live.
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// --- 1. CONFIGURATION ---
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'testnetcsc@gmail.com');
define('MAIL_PASS', 'pfzunkexewpleoqx'); // Ensure this App Password is correct
define('MAIL_PORT', 465); // SSL usually uses 465, TLS uses 587
define('MAIL_SECURE', 'ssl');

// --- 2. GLOBAL MAIL OBJECT (For Admin Forgot Password) ---
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = MAIL_SECURE;
    $mail->Port       = MAIL_PORT;
    
    // Default sender
    $mail->setFrom(MAIL_USER, 'MCT Lost & Found Admin');
} catch (Exception $e) {
    // If this global init fails, individual functions below will handle errors locally
}

// --- 3. EXISTING FUNCTIONS (For Student Notifications) ---

function sendEmailNotification($to_email, $student_name, $item_name) {
    $mail = new PHPMailer(true); // Create a fresh instance

    try {
        $mail->SMTPDebug = 0;                       
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST; 
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;  
        $mail->Password   = MAIL_PASS;      
        $mail->SMTPSecure = MAIL_SECURE;                   
        $mail->Port       = MAIL_PORT;                     

        $mail->setFrom(MAIL_USER, 'MCT Lost and Found Admin');
        $mail->addAddress($to_email, $student_name);

        $mail->isHTML(true);
        $mail->Subject = 'Good News! Your Lost Item was Found';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                <div style='background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                    <h2 style='color: #2563EB;'>Item Found!</h2>
                    <p>Dear <strong>$student_name</strong>,</p>
                    <p>We have good news! An item matching your report for <strong>'$item_name'</strong> has been found or turned in.</p>
                    <p>Please visit the <strong>Student Affairs Office (SAO)</strong> or the Admin Office to verify and claim your item.</p>
                    <p>Bring your ID for verification.</p>
                    <br>
                    <p style='font-size: 12px; color: #888;'>MCT Lost & Found System</p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function sendPasswordResetEmail($to_email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST; 
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;  
        $mail->Password   = MAIL_PASS;      
        $mail->SMTPSecure = MAIL_SECURE;                   
        $mail->Port       = MAIL_PORT;                     

        $mail->setFrom(MAIL_USER, 'MCT Admin');
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password';
        
        // Link to the reset page
        $link = "http://localhost/lost-found/reset_password.php?token=" . $token;

        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                <div style='background-color: #fff; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                    <h2 style='color: #2563EB;'>Password Reset Request</h2>
                    <p>Click the button below to reset your password. This link expires in 30 minutes.</p>
                    <a href='$link' style='background-color: #2563EB; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0;'>Reset Password</a>
                    <p style='color: #888; font-size: 12px;'>If you did not request this, please ignore this email.</p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>