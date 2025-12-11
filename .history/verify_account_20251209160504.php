<?php
class EmailNotification {
    
    private $from_email = "noreply@warrantytracker.com";
    private $from_name = "Warranty Tracker System";
    
    public function sendEmail($to_email, $to_name, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$this->from_name} <{$this->from_email}>" . "\r\n";
        
        $full_message = $this->getEmailTemplate($message, $subject);
        
        return mail($to_email, $subject, $full_message, $headers);
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
                    <h1><i class="fas fa-tools"></i> Warranty Tracker</h1>
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
        $subject = " Warranty Expiring Soon - {$appliance_name}";
        
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
        $subject = "✅ Warranty Claim Received - {$appliance_name}";
        
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
}
?>