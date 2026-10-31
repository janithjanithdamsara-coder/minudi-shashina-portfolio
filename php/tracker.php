<?php
/**
 * Visitor Tracking Engine for Minudi Shashina's Portfolio
 * Accurately tracks Total Page Views vs. Unique Visitors (deduplicated by IP per day).
 * Excludes active admin sessions.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Do not track visits if the user is logged into the Admin Panel
if (!empty($_SESSION['admin_logged'])) {
    return;
}

$dataFile = __DIR__ . '/../data/analytics.json';
$dataDir  = dirname($dataFile);

if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0755, true);
}

// Load existing analytics data
$analytics = [
    'total_views'      => 0,
    'unique_visitors'  => 0,
    'today_date'       => date('Y-m-d'),
    'today_views'      => 0,
    'today_uniques'    => 0,
    'unique_ips_today' => [],
    'all_unique_ips'   => [],
    'recent_visits'    => []
];

if (file_exists($dataFile)) {
    $content = @file_get_contents($dataFile);
    $decoded = @json_decode($content, true);
    if (is_array($decoded)) {
        $analytics = array_merge($analytics, $decoded);
    }
}

// Check date rollover (reset daily counters if new day)
$currentDate = date('Y-m-d');
if ($analytics['today_date'] !== $currentDate) {
    $analytics['today_date']       = $currentDate;
    $analytics['today_views']      = 0;
    $analytics['today_uniques']    = 0;
    $analytics['unique_ips_today'] = [];
}

// Obtain client IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($ips[0]);
}

$ipHash = md5($ip);
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Determine simple device/browser name
$device = 'Desktop';
if (preg_match('/(mobile|android|iphone|ipad)/i', $userAgent)) {
    $device = 'Mobile';
}
$browser = 'Browser';
if (strpos($userAgent, 'Chrome') !== false) {
    $browser = 'Chrome';
} elseif (strpos($userAgent, 'Firefox') !== false) {
    $browser = 'Firefox';
} elseif (strpos($userAgent, 'Safari') !== false) {
    $browser = 'Safari';
} elseif (strpos($userAgent, 'Edge') !== false) {
    $browser = 'Edge';
}

// 1. Increment Total Page Views
$analytics['total_views']++;
$analytics['today_views']++;

// 2. Check if Unique Visitor Today
if (!in_array($ipHash, $analytics['unique_ips_today'])) {
    $analytics['unique_ips_today'][] = $ipHash;
    $analytics['today_uniques']++;
}

// 3. Check if All-Time Unique Visitor
if (!in_array($ipHash, $analytics['all_unique_ips'])) {
    $analytics['all_unique_ips'][] = $ipHash;
    $analytics['unique_visitors']++;
}

// 4. Log recent visit (masked IP for privacy)
$maskedIp = substr($ip, 0, strrpos($ip, '.') !== false ? strrpos($ip, '.') : strlen($ip)) . '.***';
if ($ip === '::1' || $ip === '127.0.0.1') {
    $maskedIp = 'Localhost (127.0.0.1)';
}

$visitEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'ip'        => $maskedIp,
    'device'    => $device,
    'browser'   => $browser,
    'page'      => $_SERVER['REQUEST_URI'] ?? '/'
];

array_unshift($analytics['recent_visits'], $visitEntry);
// Keep last 50 visits
$analytics['recent_visits'] = array_slice($analytics['recent_visits'], 0, 50);

// Save back
@file_put_contents($dataFile, json_encode($analytics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
