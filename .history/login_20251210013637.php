<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}
require_once "database.php";
$error_message = "";
$success_message = "";
$email = "";


if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = "You have been logged out successfully";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(htmlspecialchars($_POST["email"] ?? ""));
    $password = trim($_POST["password"] ?? "");
    if (empty($email)) {
        $error_message = "Email is required";
    } elseif (empty($password)) {
        $error_message = "Password is required";
    } else {
        $db = new Database();
        $sql = "SELECT * FROM admin WHERE email = :email";
        $query = $db->connect()->prepare($sql);
        $query->bindParam(":email", $email);
        
        if ($query->execute()) {
            $admin = $query->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Check if email is verified
                if (isset($admin['is_verified']) && $admin['is_verified'] == 0) {
                    $error_message = "Please verify your email address before logging in. Check your inbox for the verification link.";
                } else {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_name'] = $admin['name'];
                    
                    header("Location: admin_dashboard.php");
                    exit;
                }
            } else {
                $error_message = "Invalid email or password";
            }
        } else {
            $error_message = "Database error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="center-container">
        <div class="auth-box">
            <h1><i class="fas fa-sign-in-alt"></i> Admin Login</h1>
            <p class="subtitle">Warranty Tracker System</p>
            
            <?php if ($success_message): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-lock"></i> Login
                </button>
            </form>
            
            <div class="divider"></div>
            
            <p class="link-text"><i class="fas fa-question-circle"></i> <a href="forgot_password.php">Forgot your password? Reset it here</a></p>
            
            <div class="divider"></div>
            
            <p class="link-text">Don't have an account? <a href="register.php">Register here</a></p>
            
            <div class="divider"></div>
            
            <p class="link-text"><a href="customer_warranty_tracker.php"><i class="fas fa-arrow-left"></i> Back to Customer Portal</a></p>
        </div>
    </div>
</body>
</html>