<?php
session_start();
require_once "database.php";

$error = "";
$success = "";


if (!isset($_SESSION['verified_reset']) || !isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit;
}

$user_id = $_SESSION['reset_user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");
    
    if (empty($password)) {
        $error = "Password is required";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif (empty($confirm_password)) {
        $error = "Please confirm your password";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $db = new Database();
        $conn = $db->connect();
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
   
        $stmt = $conn->prepare("UPDATE admin SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $user_id);
        
        if ($stmt->execute()) {
            $success = "Password has been reset successfully!";
          
            unset($_SESSION['reset_email']);
            unset($_SESSION['verified_reset']);
            unset($_SESSION['reset_user_id']);
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password - Warranty Tracker</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="center-container">
        <div class="auth-box">
            <h1><i class="fas fa-lock"></i> Create New Password</h1>
            <p class="subtitle">Enter a secure password for your account</p>
            
            <?php if ($success): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
                <p style="text-align: center; margin: 20px 0;">You can now login with your new password.</p>
                <a href="login.php" class="btn btn-primary btn-block" style="text-align: center;">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form action="" method="post">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i> New Password <span class="required">*</span>
                    </label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <small style="color: #666; display: block; margin-top: 5px;">Must be at least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-check-circle"></i> Confirm Password <span class="required">*</span>
                    </label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>