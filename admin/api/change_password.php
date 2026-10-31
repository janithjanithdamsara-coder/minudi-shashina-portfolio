<?php
/**
 * Minudi Shashina Portfolio - Change Admin Password API
 * Verifies Current Password, Hashes New Password with Bcrypt, and Updates Credentials
 */

require_once __DIR__ . '/../../php/security.php';
init_secure_session();

header('Content-Type: application/json; charset=UTF-8');

// 1. Ensure Admin is Authenticated
if (empty($_SESSION['admin_logged'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'POST method required.']);
    exit;
}

// 2. Validate CSRF Token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF verification failed. Action blocked.']);
    exit;
}

// 3. Retrieve Inputs
$currentPassword = trim($_POST['current_password'] ?? '');
$newPassword     = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'All password fields are required.']);
    exit;
}

// 4. Verify Current Password
if (!verify_admin_password('admin', $currentPassword)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Current password entered is incorrect.']);
    exit;
}

// 5. Validate New Password
if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters long.']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'New password and confirmation do not match.']);
    exit;
}

// 6. Hash with Bcrypt (Cost 12)
$newHash = hash_admin_password($newPassword);

$credFile = __DIR__ . '/../../data/admin_credentials.json';
$credData = [
    'username'      => 'admin',
    'password_hash' => $newHash,
    'updated_at'    => date('Y-m-d H:i:s'),
    'updated_by_ip' => get_client_ip()
];

if (@file_put_contents($credFile, json_encode($credData, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save updated password to credentials store.']);
    exit;
}

// 7. Log Action in Audit Log
log_admin_action('ADMIN_PASSWORD_CHANGED', 'Admin updated dashboard password');

echo json_encode([
    'status' => 'success',
    'message' => 'Admin password updated successfully! Please remember your new password.'
]);
