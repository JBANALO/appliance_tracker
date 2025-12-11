<?php
session_start();
require_once "database.php";
require_once "EmailNotification.php";

$message = "";
$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(htmlspecialchars($_POST["email"] ?? ""));
    
    if (empty($email)) {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT id, name, email FROM admin WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $reset_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
       
            $stmt = $conn->prepare("UPDATE admin SET reset_token = :code, reset_expires = :expires WHERE email = :email");
            $stmt->bindParam(":code", $verification_code);
            $stmt->bindParam(":expires", $reset_expires);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            $emailNotif = new EmailNotification();
            
            $email_message = "
                <h2>Password Reset Request</h2>
                <p>Hello, {$user['name']}!</p>
                <p>We received a request to reset your password for your Warranty Tracker account.</p>
                <div class='highlight'>
                    <strong>Your Verification Code:</strong><br>
                    <span style='font-size: 32px; font-weight: bold; color: #4a5568; letter-spacing: 5px;'>{$verification_code}</span>
                </div>
                <p>Enter this code on the verification page to reset your password.</p>
                <p><strong>This code will expire in 15 minutes.</strong></p>
                <p>If you didn't request a password reset, please ignore this email.</p>
            ";
            
            if ($emailNotif->sendEmail($email, $user['name'], "Password Reset Code", $email_message)) {
                $_SESSION['reset_email'] = $email;
                header("Location: verify_reset_code.php");
                exit;
            } else {
                $error = "Failed to send email. Please try again later.";
            }
        } else {
            $message = "If an account with that email exists, a verification code has been sent.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Warranty Tracker</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="center-container">
        <div class="auth-box">
            <h1>Forgot Password</h1>
            <p class="subtitle">Enter your email address to receive a verification code</p>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <p><?= $message ?></p>
                </div>
                <p class="link-text"><a href="login.php">Back to Login</a></p>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <p><?= $error ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!$message): ?>
            <form action="" method="post">
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Send Verification Code</button>
            </form>
            
            <p class="link-text">Remember your password? <a href="login.php">Login here</a></p>
            <?php endif; ?>
            
            <p class="link-text"><a href="customer_warranty_tracker.php">Back to Customer Portal</a></p>
        </div>
    </div>
</body>
</html>