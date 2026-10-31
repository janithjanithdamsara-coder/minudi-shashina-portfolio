<?php
require_once __DIR__ . '/../../php/security.php';
init_secure_session();

header('Content-Type: application/json; charset=UTF-8');

// 1. Ensure admin is authenticated
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
    echo json_encode(['status' => 'error', 'message' => 'CSRF verification failed. Request blocked.']);
    exit;
}

$projectsFile = __DIR__ . '/../../data/projects.json';
$uploadDir    = __DIR__ . '/../../uploads/projects/';

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// Load existing projects
$projects = [];
if (file_exists($projectsFile)) {
    $content = @file_get_contents($projectsFile);
    $decoded = @json_decode($content, true);
    if (is_array($decoded)) {
        $projects = $decoded;
    }
}

$id          = trim(strip_tags($_POST['id'] ?? ''));
$title       = trim(strip_tags($_POST['title'] ?? ''));
$category    = trim(strip_tags($_POST['category'] ?? 'web'));
$short_desc  = trim(strip_tags($_POST['short_desc'] ?? ''));
$full_desc   = trim(strip_tags($_POST['full_desc'] ?? ''));
$tech_raw    = trim(strip_tags($_POST['tech_stack'] ?? ''));
$live_url    = trim(filter_var($_POST['live_url'] ?? '', FILTER_SANITIZE_URL));
$github_url  = trim(filter_var($_POST['github_url'] ?? '', FILTER_SANITIZE_URL));

if (empty($title) || empty($short_desc)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Project Title and Short Description are required.']);
    exit;
}

// Parse tech tags
$tech_stack = [];
if (!empty($tech_raw)) {
    $tags = explode(',', $tech_raw);
    foreach ($tags as $t) {
        $cleaned = trim(strip_tags($t));
        if ($cleaned !== '') {
            $tech_stack[] = $cleaned;
        }
    }
}
if (empty($tech_stack)) {
    $tech_stack = ['PHP', 'MySQL', 'Web UI'];
}

// Map Category Names
$catNames = [
    'web'        => 'Web Application',
    'management' => 'Management System',
    'enterprise' => 'Enterprise System'
];
$category_name = $catNames[$category] ?? ucfirst($category);

// 3. Strict File Upload Validation
$uploadedImages = [];
if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxFileSize  = 5 * 1024 * 1024; // 5 MB per image

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $fileCount = count($_FILES['images']['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        $err = $_FILES['images']['error'][$i];
        if ($err === UPLOAD_ERR_OK) {
            $tmpPath  = $_FILES['images']['tmp_name'][$i];
            $origName = $_FILES['images']['name'][$i];
            $fileSize = $_FILES['images']['size'][$i];

            // 1. Check File Size Limit
            if ($fileSize > $maxFileSize) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "File '{$origName}' exceeds the maximum allowed size of 5MB."]);
                exit;
            }

            // 2. Check File Extension
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "File '{$origName}' has an invalid extension. Only JPG, PNG, and WebP are permitted."]);
                exit;
            }

            // 3. Check True Binary MIME Type
            $realMime = $finfo->file($tmpPath);
            if (!in_array($realMime, $allowedMimes)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "File '{$origName}' failed security check (Invalid MIME type: {$realMime})."]);
                exit;
            }

            // 4. Generate Unpredictable Cryptographic Filename
            $safeRandom = bin2hex(random_bytes(8));
            $newFilename = 'proj_' . date('Ymd_His') . '_' . $safeRandom . '.' . $ext;
            $targetPath  = $uploadDir . $newFilename;

            if (move_uploaded_file($tmpPath, $targetPath)) {
                $uploadedImages[] = 'uploads/projects/' . $newFilename;
            }
        }
    }
}

// 4. Check if editing existing project
$isEdit = false;
if (!empty($id)) {
    foreach ($projects as $key => $proj) {
        if ($proj['id'] === $id) {
            $isEdit = true;
            
            // Retained existing images submitted by user
            $keptImages = [];
            if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
                foreach ($_POST['existing_images'] as $img) {
                    $imgClean = trim(strip_tags($img));
                    if (!empty($imgClean)) {
                        $keptImages[] = $imgClean;
                    }
                }
            } else {
                // If not explicitly set, keep original images
                $keptImages = $proj['images'] ?? [];
            }

            // Clean up removed uploaded files from disk
            $removedImages = array_diff($proj['images'] ?? [], $keptImages);
            foreach ($removedImages as $rem) {
                if (strpos($rem, 'uploads/projects/proj_') === 0) {
                    $remPath = __DIR__ . '/../../' . $rem;
                    if (file_exists($remPath)) {
                        @unlink($remPath);
                    }
                }
            }

            // Combine kept images + new uploaded images
            $finalImages = array_merge($keptImages, $uploadedImages);
            if (empty($finalImages)) {
                $finalImages = ['images/profile.jpg'];
            }

            $projects[$key] = [
                'id'            => $id,
                'title'         => $title,
                'category'      => $category,
                'category_name' => $category_name,
                'short_desc'    => $short_desc,
                'full_desc'     => !empty($full_desc) ? $full_desc : $short_desc,
                'tech_stack'    => $tech_stack,
                'images'        => array_values($finalImages),
                'live_url'      => $live_url,
                'github_url'    => $github_url,
                'created_at'    => $proj['created_at'] ?? date('Y-m-d')
            ];
            break;
        }
    }
}

if (!$isEdit) {
    // New Project
    $newId = 'proj_' . bin2hex(random_bytes(6));
    if (empty($uploadedImages)) {
        $uploadedImages = ['uploads/projects/bloomy_ui_1.jpg'];
    }

    $newProject = [
        'id'            => $newId,
        'title'         => $title,
        'category'      => $category,
        'category_name' => $category_name,
        'short_desc'    => $short_desc,
        'full_desc'     => !empty($full_desc) ? $full_desc : $short_desc,
        'tech_stack'    => $tech_stack,
        'images'        => $uploadedImages,
        'live_url'      => $live_url,
        'github_url'    => $github_url,
        'created_at'    => date('Y-m-d')
    ];

    array_unshift($projects, $newProject);
}

// Save back to JSON
@file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 5. Audit Log
log_admin_action($isEdit ? 'PROJECT_UPDATED' : 'PROJECT_CREATED', "Title: {$title} (ID: " . ($isEdit ? $id : $newId) . ")");

echo json_encode([
    'status'  => 'success',
    'message' => $isEdit ? 'Project updated successfully!' : 'New project added successfully!'
]);
exit;
