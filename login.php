<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

initSecureSession();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error_message = "";
$success_message = "";
$email = "";

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = "You have been logged out successfully";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "database.php";
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
        logSecurityEvent('CSRF_VALIDATION_FAILED', ['email' => $_POST['email'] ?? 'unknown']);
    } else {
        $email = sanitizeInput($_POST["email"] ?? "");
        $password = trim($_POST["password"] ?? "");
        
        if (empty($email)) {
            $error_message = "Email is required";
        } elseif (empty($password)) {
            $error_message = "Password is required";
        } elseif (!isValidEmail($email)) {
            $error_message = "Invalid email format";
        } else {
            // Check rate limiting
            if (!checkRateLimit($email, 5, 900)) {
                $wait_time = ceil(getRateLimitWaitTime($email, 900) / 60);
                $error_message = "Too many login attempts. Please try again in {$wait_time} minute(s).";
                logSecurityEvent('RATE_LIMIT_EXCEEDED', ['email' => $email]);
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
                            logSecurityEvent('LOGIN_UNVERIFIED', ['email' => $email]);
                        } else {
                            // Reset rate limit on successful login
                            resetRateLimit($email);
                            
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $admin['id'];
                            $_SESSION['admin_email'] = $admin['email'];
                            $_SESSION['admin_name'] = $admin['name'];
                            
                            logSecurityEvent('LOGIN_SUCCESS', ['email' => $email, 'admin_id' => $admin['id']]);
                            
                            header("Location: admin_dashboard.php");
                            exit;
                        }
                    } else {
                        $error_message = "Invalid email or password";
                        logSecurityEvent('LOGIN_FAILED', ['email' => $email, 'reason' => 'invalid_credentials']);
                    }
                } else {
                    $error_message = "Database error. Please try again.";
                    logSecurityEvent('LOGIN_DB_ERROR', ['email' => $email]);
                }
            }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
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
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #667eea;"></i>
                    </div>
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
    
    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>