<?php
require_once __DIR__ . '/../../php/security.php';
init_secure_session();

header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['admin_logged'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Validate CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF verification failed. Action blocked.']);
    exit;
}

$id = trim($_POST['id'] ?? '');
if (empty($id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Project ID required']);
    exit;
}

$projectsFile = __DIR__ . '/../../data/projects.json';
if (!file_exists($projectsFile)) {
    echo json_encode(['status' => 'error', 'message' => 'Projects file not found']);
    exit;
}

$projects = json_decode(file_get_contents($projectsFile), true);
if (!is_array($projects)) {
    $projects = [];
}

$filtered = [];
$found = false;
$deletedTitle = '';
foreach ($projects as $p) {
    if ($p['id'] === $id) {
        $found = true;
        $deletedTitle = $p['title'] ?? $id;
    } else {
        $filtered[] = $p;
    }
}

if ($found) {
    file_put_contents($projectsFile, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    log_admin_action('PROJECT_DELETED', "Title: {$deletedTitle} (ID: {$id})");
    echo json_encode(['status' => 'success', 'message' => 'Project deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Project not found']);
}
exit;
