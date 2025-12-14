<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

initSecureSession();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

require_once "admin.php";

$success_message = "";
$form = ["username" => "", "name" => "", "email" => "", "password" => "", "confirm_password" => ""];
$errors = ["username" => "", "name" => "", "email" => "", "password" => "", "confirm_password" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form["username"] = trim(htmlspecialchars($_POST["username"] ?? ""));
    $form["name"] = trim(htmlspecialchars($_POST["name"] ?? ""));
    $form["email"] = trim(htmlspecialchars($_POST["email"] ?? ""));
    $form["password"] = trim($_POST["password"] ?? "");
    $form["confirm_password"] = trim($_POST["confirm_password"] ?? "");

    if (empty($form["username"])) {
        $errors["username"] = "Username is required";
    } elseif (strlen($form["username"]) < 4) {
        $errors["username"] = "Username must be at least 4 characters";
    }
    if (empty($form["name"])) $errors["name"] = "Full name is required";
    if (empty($form["email"])) {
        $errors["email"] = "Email is required";
    } elseif (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format";
    }
    if (empty($form["password"])) {
        $errors["password"] = "Password is required";
    } elseif (strlen($form["password"]) < 6) {
        $errors["password"] = "Password must be at least 6 characters";
    }
    if (empty($form["confirm_password"])) {
        $errors["confirm_password"] = "Please confirm your password";
    } elseif ($form["password"] !== $form["confirm_password"]) {
        $errors["confirm_password"] = "Passwords do not match";
    }

    if (!array_filter($errors)) {
        $adminObj = new Admin();
        
        if ($adminObj->usernameExists($form["username"])) {
            $errors["username"] = "Username already exists";
        } else {
            $adminObj->username = $form["username"];
            $adminObj->password = $form["password"];
            $adminObj->name = $form["name"];
            $adminObj->email = $form["email"];
            
            // Generate 6-digit verification code
            $adminObj->verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $adminObj->is_verified = 0;
            
            if ($adminObj->register()) {
                // Send verification email (with timeout handling)
                try {
                    require_once "EmailNotification.php";
                    $emailNotification = new EmailNotification();
                    // Set a short timeout for email sending
                    $emailNotification->sendAdminVerificationEmail(
                        $form["email"], 
                        $form["name"], 
                        $adminObj->verification_code
                    );
                } catch (Exception $e) {
                    // Email failed but registration succeeded - show success anyway
                    error_log("Email send error: " . $e->getMessage());
                }
                
                $success_message = "Registration successful! Please check your email to verify your account before logging in.";
                $form = ["username" => "", "name" => "", "email" => "", "password" => "", "confirm_password" => ""];
            } else {
                $errors["username"] = "Registration failed. Please try again.";
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
    <title>Admin Registration</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="center-container">
        <div class="auth-box">
            <h1><i class="fas fa-user-plus"></i> Admin Registration</h1>
            <p class="subtitle">Create your admin account</p>
            
            <?php if ($success_message): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success_message ?>
                </div>
                <p class="link-text"><a href="login.php"><i class="fas fa-sign-in-alt"></i> Click here to login</a></p>
            <?php endif; ?>
            
            <?php if (!$success_message): ?>
            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($form["username"]) ?>" required>
                    <?php if (!empty($errors["username"])): ?>
                        <span class="error"><?= $errors["username"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($form["name"]) ?>" required>
                    <?php if (!empty($errors["name"])): ?>
                        <span class="error"><?= $errors["name"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($form["email"]) ?>" required>
                    <?php if (!empty($errors["email"])): ?>
                        <span class="error"><?= $errors["email"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #667eea;"></i>
                    </div>
                    <?php if (!empty($errors["password"])): ?>
                        <span class="error"><?= $errors["password"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <div style="position: relative;">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <i class="fas fa-eye" id="toggleConfirmPassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #667eea;"></i>
                    </div>
                    <?php if (!empty($errors["confirm_password"])): ?>
                        <span class="error"><?= $errors["confirm_password"] ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Register
                </button>
            </form>
            
            <div class="divider"></div>
            
            <p class="link-text">Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login here</a></p>
            <?php endif; ?>
            
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
        
        // Toggle confirm password visibility
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPassword = document.querySelector('#confirm_password');
        
        toggleConfirmPassword.addEventListener('click', function () {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>