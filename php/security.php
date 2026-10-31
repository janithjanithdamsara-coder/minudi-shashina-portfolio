<?php
/**
 * Unified Application Security Framework for Minudi Shashina Portfolio
 * Implements:
 * 1. Hardened Session Management (HttpOnly, SameSite, Secure, Session Hijacking Protection)
 * 2. Inactivity Session Timeout (30 mins)
 * 3. CSRF Protection (Cryptographic Tokens)
 * 4. Brute-Force Rate Limiting for Admin Login
 * 5. Password Hashing & Verification (Bcrypt)
 * 6. Audit Logging for Administrative Actions
 */

// 1. Initialize Hardened Session
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

        // Hardened Cookie Parameters
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }

    // Inactivity Session Timeout Check (30 Minutes = 1800 seconds)
    $timeoutDuration = 1800;
    if (!empty($_SESSION['admin_logged'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutDuration) {
            // Session expired due to inactivity
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
        $currentFingerprint = md5($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
        if (!isset($_SESSION['ua_fingerprint'])) {
            $_SESSION['ua_fingerprint'] = $currentFingerprint;
        } elseif ($_SESSION['ua_fingerprint'] !== $currentFingerprint) {
            // Potential session hijacking detected
            destroy_secure_session();
            header('Location: login.php?msg=security_reset');
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

// 3. CSRF Protection
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 4. Rate Limiting for Login (Brute-Force Protection)
function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    return $ip;
}

function check_login_rate_limit() {
    $ip = get_client_ip();
    $file = __DIR__ . '/../data/login_attempts.json';
    $attempts = [];

    if (file_exists($file)) {
        $content = @file_get_contents($file);
        $attempts = @json_decode($content, true) ?: [];
    }

    $now = time();
    $window = 900; // 15 minutes
    $maxAttempts = 5;

    // Filter attempts within window for this IP
    if (isset($attempts[$ip])) {
        $attempts[$ip] = array_filter($attempts[$ip], function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });

        if (count($attempts[$ip]) >= $maxAttempts) {
            $oldest = min($attempts[$ip]);
            $remaining = $window - ($now - $oldest);
            return [
                'allowed'           => false,
                'remaining_seconds' => max(1, $remaining),
                'attempts'          => count($attempts[$ip])
            ];
        }
    }

    return ['allowed' => true, 'remaining_seconds' => 0, 'attempts' => count($attempts[$ip] ?? [])];
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

// 5. Password Authentication (Bcrypt)
function verify_admin_password($enteredUsername, $enteredPassword) {
    // Default Admin Credentials
    $expectedUsername = 'admin';
    // Bcrypt hash for 'admin123'
    $defaultHash = '$2y$10$3kkWmS5aJ0Tb9U5/6t1pO.t5Ijk.4/YqwcwykexeTsKSRHLnA2L12';

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

// 6. Security Audit Logging
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
