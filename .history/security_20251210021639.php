<?php
/**
 * Security Helper Functions
 */

// Generate CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// Rate Limiting for Login Attempts
function checkRateLimit($identifier, $max_attempts = 5, $time_window = 900) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $data = $_SESSION[$key];
    
    // Reset if time window has passed
    if (time() - $data['first_attempt'] > $time_window) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    // Check if limit exceeded
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    
    // Increment counter
    $_SESSION[$key]['count']++;
    return true;
}

// Get remaining lockout time
function getRateLimitWaitTime($identifier, $time_window = 900) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        return 0;
    }
    
    $data = $_SESSION[$key];
    $elapsed = time() - $data['first_attempt'];
    $remaining = $time_window - $elapsed;
    
    return max(0, $remaining);
}

// Reset rate limit (after successful login)
function resetRateLimit($identifier) {
    $key = 'rate_limit_' . md5($identifier);
    unset($_SESSION[$key]);
}

// Secure session initialization
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Enable secure flag only if using HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        session_start();
        
        // Session timeout check
        if (isset($_SESSION['last_activity'])) {
            $session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 7200;
            if (time() - $_SESSION['last_activity'] > $session_lifetime) {
                session_unset();
                session_destroy();
                session_start();
            }
        }
        
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

// Sanitize input
function sanitizeInput($data) {
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone number (basic)
function isValidPhone($phone) {
    return preg_match('/^[\d\s\-\+\(\)]{10,20}$/', $phone);
}

// Generate secure random token
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Force HTTPS redirect (only in production)
function forceHTTPS() {
    if (defined('APP_ENV') && APP_ENV === 'production') {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirect_url, true, 301);
            exit;
        }
    }
}

// Log security events
function logSecurityEvent($event, $details = []) {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = sprintf(
        "[%s] IP: %s | Event: %s | Details: %s | User-Agent: %s\n",
        $timestamp,
        $ip,
        $event,
        json_encode($details),
        $user_agent
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>
