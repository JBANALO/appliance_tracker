<?php
session_start();
require_once "admin.php";

$success_message = "";
$error_message = "";

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = trim($_GET['email']);
    $code = trim($_GET['code']);
    
    $adminObj = new Admin();
    if ($adminObj->verifyEmail($email, $code)) {
        $success_message = "Email verified successfully! You can now login to your admin account.";
    } else {
        $error_message = "Invalid verification code or email. Please check the link and try again.";
    }
} else {
    $error_message = "Invalid verification link.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Warranty Tracker</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="center-container">
        <div class="auth-box">
            <?php if ($success_message): ?>
                <div class="success-icon">
                    <i class="fas fa-check-circle" style="font-size: 64px; color: #10b981;"></i>
                </div>
                <h1 style="color: #10b981; margin-top: 20px;">Email Verified!</h1>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
                <div style="margin-top: 30px;">
                    <a href="login.php" class="button" style="text-decoration: none; display: inline-block; padding: 12px 30px;">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                </div>
            <?php else: ?>
                <div class="error-icon">
                    <i class="fas fa-times-circle" style="font-size: 64px; color: #dc3545;"></i>
                </div>
                <h1 style="color: #dc3545; margin-top: 20px;">Verification Failed</h1>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
                <div style="margin-top: 30px;">
                    <a href="register.php" class="button" style="text-decoration: none; display: inline-block; padding: 12px 30px; background-color: #6c757d;">
                        <i class="fas fa-redo"></i> Register Again
                    </a>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="color: #6b7280; font-size: 14px;">
                    <i class="fas fa-shield-alt"></i> Warranty Tracker Admin Portal
                </p>
            </div>
        </div>
    </div>
</body>
</html>
