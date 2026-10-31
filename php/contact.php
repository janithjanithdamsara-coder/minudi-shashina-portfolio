<?php
/**
 * Minudi Shashina Portfolio - Secure Contact Handler
 * Features: CSRF verification, Anti-Spam Rate Limiting, Input Sanitization, XSS Immunity
 */

require_once __DIR__ . '/security.php';
init_secure_session();

header('Content-Type: application/json; charset=UTF-8');

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Please submit via POST.'
    ]);
    exit;
}

// 1. Anti-Spam Rate Limiting
if (!check_contact_rate_limit()) {
    http_response_code(429);
    echo json_encode([
        'status' => 'error',
        'message' => 'Too many messages sent recently. Please wait a few minutes before submitting again.'
    ]);
    exit;
}

// 2. CSRF Token Verification
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Security verification failed (Invalid or Expired CSRF token). Please refresh the page and try again.'
    ]);
    exit;
}

// 3. Retrieve and Sanitize Inputs
$name    = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email   = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : 'Portfolio Direct Inquiry';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// 4. Strict Validation
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all required fields (Name, Email, Message).'
    ]);
    exit;
}

if (strlen($message) > 4000 || strlen($name) > 100 || strlen($subject) > 200) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Input length exceeds permitted security limits.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please enter a valid, deliverable email address.'
    ]);
    exit;
}

// 5. Save message safely to protected data folder
$logDir = __DIR__ . '/../data';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$messageData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'name'      => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    'email'     => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
    'subject'   => htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'),
    'message'   => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
    'ip'        => get_client_ip()
];

$logFile = $logDir . '/contact_messages.json';
$messages = [];
if (file_exists($logFile)) {
    $content = @file_get_contents($logFile);
    $messages = @json_decode($content, true) ?: [];
}

// Keep last 200 messages
$messages[] = $messageData;
if (count($messages) > 200) {
    $messages = array_slice($messages, -200);
}

@file_put_contents($logFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'status' => 'success',
    'message' => 'Thank you! Your message has been sent successfully. Minudi will respond to you soon.'
]);
