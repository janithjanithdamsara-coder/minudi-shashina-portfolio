<?php
/**
 * Minudi Shashina Portfolio - Contact Handler
 * Supports both AJAX JSON requests and standard POST form submissions.
 */

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

// Retrieve and sanitize inputs
$name    = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email   = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : 'New Inquiry from Portfolio';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// Validation
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
        'message' => 'Input exceeds allowed character length.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

// Save message locally to a log / json store for easy viewing
$logDir = __DIR__ . '/../data';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$messageData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'name'      => $name,
    'email'     => $email,
    'subject'   => $subject,
    'message'   => $message,
    'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
];

$logFile = $logDir . '/contact_messages.json';
$messages = [];
if (file_exists($logFile)) {
    $existing = @file_get_contents($logFile);
    $decoded = @json_decode($existing, true);
    if (is_array($decoded)) {
        $messages = $decoded;
    }
}
$messages[] = $messageData;
@file_put_contents($logFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// If regular form submission (non-AJAX)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
    // Render styled HTML fallback response
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Thank You | Minudi Shashina</title>
        <style>
            body {
                background: #0b0d17;
                color: #e2e8f0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .card {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(169, 112, 255, 0.3);
                border-radius: 16px;
                padding: 40px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            }
            h1 { color: #a970ff; margin-bottom: 12px; }
            p { line-height: 1.6; color: #cbd5e1; }
            a.btn {
                display: inline-block;
                margin-top: 25px;
                padding: 12px 28px;
                background: linear-gradient(135deg, #a970ff, #8b5cf6);
                color: #fff;
                text-decoration: none;
                border-radius: 30px;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Message Sent Successfully!</h1>
            <p>Thank you, <strong><?= htmlspecialchars($name) ?></strong>! Your message has been received. I will respond to your email (<strong><?= htmlspecialchars($email) ?></strong>) shortly.</p>
            <a href="../index.php" class="btn">Return to Portfolio</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Return JSON response for modern AJAX
echo json_encode([
    'status'  => 'success',
    'message' => 'Thank you, ' . htmlspecialchars($name) . '! Your message has been sent successfully. I will get back to you soon.'
]);
exit;
