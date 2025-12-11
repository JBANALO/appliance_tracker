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
</head>
<body>
    <h1> Enter Verification Code</h1>
    <p>We sent a 6-digit code to <strong><?= htmlspecialchars($email) ?></strong></p>
    
    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    
    <form action="" method="post">
        <label for="code">Verification Code <span>*</span></label>
        <input type="text" name="code" id="code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required style="text-align: center; font-size: 24px; letter-spacing: 10px; font-weight: bold;">
        <p style="font-size: 12px; color: #666;">Enter the 6-digit code from your email</p>
        
        <button type="submit">Verify Code</button>
    </form>
    
    <p>Didn't receive the code? <a href="forgot_password.php">Resend code</a></p>
    <p><a href="login.php">Back to Login</a></p>
    
   
    <?php if (isset($debug_data) && $debug_data): ?>
        <div style="background: #fff3cd; padding: 10px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <strong> Debug Info:</strong><br>
            Code in DB: <?= htmlspecialchars($debug_data['reset_token']) ?><br>
            Expires: <?= htmlspecialchars($debug_data['reset_expires']) ?><br>
            Current Time: <?= date('Y-m-d H:i:s') ?>
        </div>
    <?php endif; ?>
</body>
</html>