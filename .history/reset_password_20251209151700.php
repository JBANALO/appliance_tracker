<?php
session_start();
require_once "database.php";

$error = "";
$success = "";

// Check if user has verified the code
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
        
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $stmt = $conn->prepare("UPDATE admin SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $user_id);
        
        if ($stmt->execute()) {
            $success = "Password has been reset successfully!";
            // Clear session variables
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
</head>
<body>
    <h1> Create New Password</h1>
    <p>Enter your new password</p>
    
    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
        <p>You can now login with your new password.</p>
        <p><a href="login.php" style="display: inline-block; padding: 10px 20px; background: #4a5568; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    
    <?php if (!$success): ?>
    <form action="" method="post">
        <label for="password">New Password <span>*</span></label>
        <input type="password" name="password" id="password" required>
        <p style="font-size: 12px; color: #666;">Must be at least 6 characters</p>
        
        <label for="confirm_password">Confirm New Password <span>*</span></label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        
        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</body>
</html>