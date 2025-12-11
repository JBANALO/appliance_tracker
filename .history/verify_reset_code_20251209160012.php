<?php
session_start();
require_once "database.php";

$error = "";
$success = "";


if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST["code"] ?? "");
    
    if (empty($code)) {
        $error = "Verification code is required";
    } elseif (strlen($code) != 6) {
        $error = "Invalid verification code format";
    } else {
        $db = new Database();
        $conn = $db->connect();
        
        
        $debug_stmt = $conn->prepare("SELECT reset_token, reset_expires FROM admin WHERE email = :email");
        $debug_stmt->bindParam(":email", $email);
        $debug_stmt->execute();
        $debug_data = $debug_stmt->fetch(PDO::FETCH_ASSOC);
        
        
        $stmt = $conn->prepare("SELECT id, name, reset_token, reset_expires FROM admin WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            
            if ($user['reset_token'] !== $code) {
                $error = "Invalid verification code. Code does not match.";
            } 
          
            elseif (strtotime($user['reset_expires']) < time()) {
                $error = "Verification code has expired. Please request a new one.";
            }
          
            else {
                $_SESSION['verified_reset'] = true;
                $_SESSION['reset_user_id'] = $user['id'];
                header("Location: reset_password_form.php");
                exit;
            }
        } else {
            $error = "No reset request found for this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Verify Code - Warranty Tracker</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="center-container">
        <div class="auth-box">
            <h1><i class="fas fa-shield-alt"></i> Enter Verification Code</h1>
            <p class="subtitle">We sent a 6-digit code to <strong><?= htmlspecialchars($email) ?></strong></p>
            
            <?php if ($error): ?>
                <div class="alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="form-group">
                    <label for="code">Verification Code <span class="required">*</span></label>
                    <input type="text" name="code" id="code" class="form-control" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required style="text-align: center; font-size: 24px; letter-spacing: 10px; font-weight: bold;">
                    <small style="color: #666; display: block; margin-top: 5px;">Enter the 6-digit code from your email</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check-circle"></i> Verify Code
                </button>
            </form>
            
            <div class="divider"></div>
            
            <p class="link-text">Didn't receive the code? <a href="forgot_password.php"><i class="fas fa-redo"></i> Resend code</a></p>
            
            <p class="link-text"><a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a></p>
        </div>
    </div>