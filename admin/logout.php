<?php
require_once __DIR__ . '/../php/security.php';
init_secure_session();

if (!empty($_SESSION['admin_logged'])) {
    log_admin_action('ADMIN_LOGOUT', 'User logged out normally');
}

destroy_secure_session();
header('Location: login.php');
exit;
