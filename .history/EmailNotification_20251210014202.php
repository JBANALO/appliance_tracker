<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

class EmailNotification {
    
   
    private $from_email = "josiebanalo977@gmail.com";
    private $from_name = "Warranty Appliance Tracker ";
    private $smtp_username = "josiebanalo977@gmail.com";
    private $smtp_password = "hipo yssc beku scir"; 
    
    public function sendEmail($to_email, $to_name, $subject, $message) {
        $mail = new PHPMailer(true);
        
        try {
           
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
      
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);
            
     
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $this->getEmailTemplate($message, $subject);
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    private function getEmailTemplate($content, $title) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1> Warranty Tracker</h1>
                    <p>{$title}</p>
                </div>
                <div class='email-body'>
                    {$content}
                </div>
                <div class='email-footer'>
                    <p>This is an automated message from Warranty Tracker System.</p>
                    <p>&copy; " . date('Y') . " Warranty Tracker. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    public function sendVerificationEmail($email, $owner_name, $verification_code) {
        $subject = "Verify Your Account - Warranty Tracker";
        
        $message = "
            <h2>Welcome, {$owner_name}!</h2>
            <p>Thank you for registering with Warranty Tracker System.</p>
            <p>Please verify your email address to activate your account.</p>
            <div class='highlight'>
                <strong>Verification Code:</strong> <span style='font-size: 24px; font-weight: bold; color: #4a5568;'>{$verification_code}</span>
            </div>
            <p>Enter this code on the verification page to complete your registration.</p>
            <p>If you didn't create this account, please ignore this email.</p>
        ";
        
        return $this->sendEmail($email, $owner_name, $subject, $message);
    }
    
    public function sendWarrantyExpiringEmail($email, $owner_name, $appliance_name, $days_left, $warranty_end_date) {
        $subject = "Warranty Expiring Soon - {$appliance_name}";
        
        $message = "
            <h2>Hello, {$owner_name}!</h2>
            <p>This is a reminder that your warranty is expiring soon.</p>
            <div class='highlight'>
                <strong>Appliance:</strong> {$appliance_name}<br>
                <strong>Warranty End Date:</strong> {$warranty_end_date}<br>
                <strong>Days Remaining:</strong> <span style='color: #dc3545; font-weight: bold;'>{$days_left} days</span>
            </div>
            <p>Please take necessary action if you wish to renew or extend your warranty coverage.</p>
            <p>For assistance, please contact our support team.</p>
        ";
        
        return $this->sendEmail($email, $owner_name, $subject, $message);
    }
    
    public function sendClaimConfirmationEmail($email, $owner_name, $appliance_name, $claim_id, $claim_date) {
        $subject = " Warranty Claim Received - {$appliance_name}";
        
        $message = "
            <h2>Hello, {$owner_name}!</h2>
            <p>Your warranty claim has been successfully submitted and is now being processed.</p>
            <div class='highlight'>
                <strong>Claim ID:</strong> #{$claim_id}<br>
                <strong>Appliance:</strong> {$appliance_name}<br>
                <strong>Date Filed:</strong> {$claim_date}<br>
                <strong>Status:</strong> <span style='color: #f59e0b;'>Pending Review</span>
            </div>
            <p>Our team will review your claim and contact you within 3-5 business days.</p>
            <p>Please keep your claim ID for reference.</p>
        ";
        
        return $this->sendEmail($email, $owner_name, $subject, $message);
    }
    
    public function sendClaimStatusUpdateEmail($email, $owner_name, $appliance_name, $claim_id, $status, $admin_notes = '') {
        $subject = "Claim Status Update - {$appliance_name}";
        
        $status_color = $status == 'Approved' ? '#10b981' : ($status == 'Rejected' ? '#dc3545' : '#f59e0b');
        
        $message = "
            <h2>Hello, {$owner_name}!</h2>
            <p>There has been an update to your warranty claim.</p>
            <div class='highlight'>
                <strong>Claim ID:</strong> #{$claim_id}<br>
                <strong>Appliance:</strong> {$appliance_name}<br>
                <strong>Status:</strong> <span style='color: {$status_color}; font-weight: bold;'>{$status}</span>
            </div>
            " . ($admin_notes ? "<p><strong>Admin Notes:</strong><br>{$admin_notes}</p>" : "") . "
            <p>If you have any questions, please contact our support team.</p>
        ";
        
        return $this->sendEmail($email, $owner_name, $subject, $message);
    }
    
    public function sendPasswordResetEmail($email, $name, $reset_link) {
        $subject = "Password Reset Request - Warranty Tracker";
        
        $message = "
            <h2>Password Reset Request</h2>
            <p>Hello, {$name}!</p>
            <p>We received a request to reset your password for your Warranty Tracker account.</p>
            <div class='highlight'>
                <p>Click the button below to reset your password:</p>
                <a href='{$reset_link}' class='button' style='color: white;'>Reset Password</a>
            </div>
            <p>Or copy and paste this link into your browser:</p>
            <p style='word-break: break-all; color: #4a5568;'>{$reset_link}</p>
            <p><strong>This link will expire in 1 hour.</strong></p>
            <p>If you didn't request a password reset, please ignore this email.</p>
        ";
        
        return $this->sendEmail($email, $name, $subject, $message);
    }
    
    public function sendAdminVerificationEmail($email, $name, $verification_code) {
        $subject = "Verify Your Admin Account - Warranty Tracker";
        
        $verification_link = "http://localhost/appliance_tracker/verify_admin_email.php?email=" . urlencode($email) . "&code=" . $verification_code;
        
        $message = "
            <h2>Welcome to Warranty Tracker!</h2>
            <p>Hello, {$name}!</p>
            <p>Thank you for registering as an admin. To complete your registration and access the admin dashboard, please verify your email address.</p>
            <div class='highlight'>
                <p><strong>Your verification code is:</strong></p>
                <h1 style='font-size: 32px; color: #667eea; letter-spacing: 5px; margin: 10px 0;'>{$verification_code}</h1>
                <p>Click the button below to verify your account:</p>
                <a href='{$verification_link}' class='button' style='color: white;'>Verify Email Address</a>
            </div>
            <p>Or copy and paste this link into your browser:</p>
            <p style='word-break: break-all; color: #4a5568;'>{$verification_link}</p>
            <p><strong>For security reasons, this verification link will expire in 24 hours.</strong></p>
            <p>If you didn't create this account, please ignore this email.</p>
        ";
        
        return $this->sendEmail($email, $name, $subject, $message);
    }
}
?>