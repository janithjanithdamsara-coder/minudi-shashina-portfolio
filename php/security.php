<?php
/**
 * Unified Enterprise Application Security Framework for Minudi Shashina Portfolio
 * Complies with OWASP Top 10 Security Checklist:
 * 1. Hardened Session Management (HttpOnly, SameSite=Strict, Secure Cookies, Fixation & Hijacking Defense)
 * 2. Inactivity Session Timeout (30 mins automatic expiration)
 * 3. Cryptographic CSRF Protection (Per-session unique tokens)
 * 4. Brute-Force Rate Limiting (5 failed attempts = 15-minute IP lock)
 * 5. Password Hashing (Bcrypt with cost factor 12) & password_verify()
 * 6. Audit Logging for Administrative Actions
 * 7. Information Leakage Prevention (Display errors suppressed, sensitive files blocked)
 * 8. Contact Form Anti-Spam Rate Limiting
 */

// Suppress error display to public (prevents path & stack trace leakage)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// Helper for XSS sanitization
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// 1. Initialize Hardened Session
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        // Enforce hardened cookie parameters
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        session_start();
    }

    // Emit Security Headers (In case Apache mod_headers is not loaded)
    if (!headers_sent()) {
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }

    // Inactivity Session Timeout Check (30 Minutes = 1800 seconds)
    $timeoutDuration = 1800;
    if (!empty($_SESSION['admin_logged'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutDuration) {
            destroy_secure_session();
            if (strpos($_SERVER['REQUEST_URI'] ?? '', 'api/') !== false) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['status' => 'error', 'message' => 'Session expired due to inactivity. Please login again.']);
                exit;
            } else {
                header('Location: login.php?msg=expired');
                exit;
            }
        }
        $_SESSION['last_activity'] = time();

        // Session Hijacking Protection (User-Agent Fingerprint)
        $currentFingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
        if (!isset($_SESSION['ua_fingerprint'])) {
            destroy_secure_session();
            header('Location: login.php?msg=security_reset');
            exit;
        } elseif (!hash_equals($_SESSION['ua_fingerprint'], $currentFingerprint)) {
            destroy_secure_session();
            header('Location: login.php?msg=security_hijack');
            exit;
        }
    }
}

// 2. Destroy Session Cleanly
function destroy_secure_session() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    @session_destroy();
}

// 3. Cryptographic CSRF Protection
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

// 4. Client IP Retrieval
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($list[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

// 5. Brute-Force Rate Limiting for Login (Max 5 attempts per 15 minutes)
function check_login_rate_limit() {
    $ip = get_client_ip();
    $file = __DIR__ . '/../data/login_attempts.json';
    $attempts = [];

    if (file_exists($file)) {
        $content = @file_get_contents($file);
        $attempts = @json_decode($content, true) ?: [];
    }

    $now = time();
    $window = 15 * 60; // 15 minutes
    $maxAttempts = 5;

    $userAttempts = $attempts[$ip] ?? [];
    $validAttempts = array_filter($userAttempts, function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });

    $attempts[$ip] = array_values($validAttempts);
    @file_put_contents($file, json_encode($attempts, JSON_PRETTY_PRINT));

    if (count($validAttempts) >= $maxAttempts) {
        $oldest = min($validAttempts);
        $remainingSeconds = $window - ($now - $oldest);
        return [
            'allowed' => false,
            'remaining_seconds' => max(0, $remainingSeconds),
            'attempts' => count($validAttempts)
        ];
    }

    return [
        'allowed' => true,
        'remaining_seconds' => 0,
        'attempts' => count($validAttempts)
    ];
}

function record_failed_login() {
    $ip = get_client_ip();
    $file = __DIR__ . '/../data/login_attempts.json';
    $attempts = [];

    if (file_exists($file)) {
        $content = @file_get_contents($file);
        $attempts = @json_decode($content, true) ?: [];
    }

    $now = time();
    if (!isset($attempts[$ip])) {
        $attempts[$ip] = [];
    }
    $attempts[$ip][] = $now;

    @file_put_contents($file, json_encode($attempts, JSON_PRETTY_PRINT));
}

function clear_failed_logins() {
    $ip = get_client_ip();
    $file = __DIR__ . '/../data/login_attempts.json';
    if (file_exists($file)) {
        $content = @file_get_contents($file);
        $attempts = @json_decode($content, true) ?: [];
        if (isset($attempts[$ip])) {
            unset($attempts[$ip]);
            @file_put_contents($file, json_encode($attempts, JSON_PRETTY_PRINT));
        }
    }
}

// 6. Contact Form Rate Limiting (Max 5 submissions per 10 minutes)
function check_contact_rate_limit() {
    $ip = get_client_ip();
    $file = __DIR__ . '/../data/contact_rate_limit.json';
    $limits = [];

    if (file_exists($file)) {
        $limits = @json_decode(@file_get_contents($file), true) ?: [];
    }

    $now = time();
    $window = 10 * 60; // 10 minutes
    $maxSubmissions = 5;

    $history = $limits[$ip] ?? [];
    $validHistory = array_filter($history, function($t) use ($now, $window) {
        return ($now - $t) < $window;
    });

    if (count($validHistory) >= $maxSubmissions) {
        return false;
    }

    $validHistory[] = $now;
    $limits[$ip] = array_values($validHistory);
    @file_put_contents($file, json_encode($limits, JSON_PRETTY_PRINT));
    return true;
}

// 7. Password Hashing & Authentication (Bcrypt with Cost 12)
function hash_admin_password($plainPassword) {
    return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_admin_password($enteredUsername, $enteredPassword) {
    // Default Admin Credentials
    $expectedUsername = 'admin';
    // Bcrypt hash for 'admin123' (Cost 12)
    $defaultHash = '$2y$12$0tOqj.2pE0h9bEwMbmVfqu4eJj33Vb0vjLwZkG2k3cQq74tqI4rVy';

    // Allow custom credentials file if updated
    $credFile = __DIR__ . '/../data/admin_credentials.json';
    if (file_exists($credFile)) {
        $creds = @json_decode(file_get_contents($credFile), true);
        if (is_array($creds) && !empty($creds['username']) && !empty($creds['password_hash'])) {
            $expectedUsername = $creds['username'];
            $defaultHash      = $creds['password_hash'];
        }
    }

    if ($enteredUsername !== $expectedUsername) {
        return false;
    }

    return password_verify($enteredPassword, $defaultHash);
}

// 8. Security Audit Logging
function log_admin_action($action, $details = '') {
    $logFile = __DIR__ . '/../data/admin_audit.log';
    $entry = sprintf(
        "[%s] IP: %s | Action: %s | Details: %s%s",
        date('Y-m-d H:i:s'),
        get_client_ip(),
        $action,
        $details,
        PHP_EOL
    );
    @file_put_contents($logFile, $entry, FILE_APPEND);
}
