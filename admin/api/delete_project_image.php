<?php
/**
 * Minudi Shashina Portfolio - Delete Single Project Image API
 * Removes a specific image from a project's images array and deletes file if appropriate
 */

require_once __DIR__ . '/../../php/security.php';
init_secure_session();

header('Content-Type: application/json; charset=UTF-8');

// 1. Authenticate Admin
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

// 2. CSRF Verification
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF verification failed. Action blocked.']);
    exit;
}

$projectId = trim(strip_tags($_POST['project_id'] ?? ''));
$imagePath = trim(strip_tags($_POST['image_path'] ?? ''));

if (empty($projectId) || empty($imagePath)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Project ID and Image Path are required.']);
    exit;
}

$projectsFile = __DIR__ . '/../../data/projects.json';
if (!file_exists($projectsFile)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Projects file not found.']);
    exit;
}

$projects = json_decode(file_get_contents($projectsFile), true);
if (!is_array($projects)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to parse projects data.']);
    exit;
}

$found = false;
$projectTitle = '';
$remainingImages = [];

foreach ($projects as $key => $proj) {
    if ($proj['id'] === $projectId) {
        $found = true;
        $projectTitle = $proj['title'];
        $originalImages = $proj['images'] ?? [];
        
        $newImages = [];
        $imageRemoved = false;
        
        foreach ($originalImages as $img) {
            if ($img === $imagePath) {
                $imageRemoved = true;
                // Delete actual file if it is an uploaded project image (proj_*)
                if (strpos($img, 'uploads/projects/proj_') === 0) {
                    $realPath = __DIR__ . '/../../' . $img;
                    if (file_exists($realPath)) {
                        @unlink($realPath);
                    }
                }
            } else {
                $newImages[] = $img;
            }
        }

        if (!$imageRemoved) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Image not found in this project.']);
            exit;
        }

        $projects[$key]['images'] = array_values($newImages);
        $remainingImages = array_values($newImages);
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Project not found.']);
    exit;
}

// Save back to JSON
if (@file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save changes to database file.']);
    exit;
}

// Log in Security Audit Log
log_admin_action('PROJECT_IMAGE_DELETED', "Project: {$projectTitle} | Removed: " . basename($imagePath));

echo json_encode([
    'status' => 'success',
    'message' => 'Screenshot removed successfully!',
    'remaining_images' => $remainingImages,
    'count' => count($remainingImages)
]);
exit;
