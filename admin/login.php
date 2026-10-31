<?php
require_once __DIR__ . '/../php/security.php';
init_secure_session();

// If already logged in, redirect to dashboard
if (!empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$rateLimit = check_login_rate_limit();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Check Rate Limiting
    if (!$rateLimit['allowed']) {
        $minutes = ceil($rateLimit['remaining_seconds'] / 60);
        $error = "Too many failed login attempts. For security, access is temporarily locked. Please try again in {$minutes} minute(s).";
    } else {
        // 2. Validate CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!validate_csrf_token($csrfToken)) {
            $error = 'Security session validation failed (Invalid CSRF Token). Please refresh and try again.';
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // 3. Verify via Bcrypt Password Verify
            if (verify_admin_password($username, $password)) {
                // Success: Regenerate Session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['admin_logged']   = true;
                $_SESSION['admin_user']     = 'Admin';
                $_SESSION['last_activity']  = time();
                $_SESSION['ua_fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');

                clear_failed_logins();
                log_admin_action('ADMIN_LOGIN_SUCCESS', "User: {$username}");

                header('Location: index.php');
                exit;
            } else {
                record_failed_login();
                log_admin_action('ADMIN_LOGIN_FAILED', "Attempted User: {$username}");

                $remaining = 5 - ($rateLimit['attempts'] + 1);
                if ($remaining > 0) {
                    $error = "Invalid username or password. ({$remaining} attempt(s) remaining before temporary lockout).";
                } else {
                    $error = "Too many failed login attempts. Access is now temporarily locked for 15 minutes.";
                }
            }
        }
    }
}

// Notice messages
$msg = $_GET['msg'] ?? '';
if ($msg === 'expired') {
    $error = 'Your administrative session expired due to inactivity. Please sign in again.';
} elseif ($msg === 'security_reset') {
    $error = 'Security warning: Session signature mismatch detected. Session has been safely reset.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Minudi Shashina Portfolio</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    :root {
      --bg-primary: #06080e;
      --bg-card: rgba(14, 18, 30, 0.85);
      --border-glow: rgba(169, 112, 255, 0.35);
      --accent-purple: #a970ff;
      --accent-indigo: #8b5cf6;
      --accent-cyan: #00f2fe;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      background: var(--bg-primary);
      color: #f8fafc;
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }
    body::before {
      content: '';
      position: absolute;
      width: 450px;
      height: 450px;
      background: radial-gradient(circle, rgba(169, 112, 255, 0.18), transparent 70%);
      top: -100px;
      left: -100px;
      border-radius: 50%;
      pointer-events: none;
    }
    body::after {
      content: '';
      position: absolute;
      width: 450px;
      height: 450px;
      background: radial-gradient(circle, rgba(0, 242, 254, 0.15), transparent 70%);
      bottom: -100px;
      right: -100px;
      border-radius: 50%;
      pointer-events: none;
    }
    .login-card {
      background: var(--bg-card);
      backdrop-filter: blur(16px);
      border: 1px solid var(--border-glow);
      border-radius: 22px;
      padding: 45px 40px;
      max-width: 440px;
      width: 100%;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(169, 112, 255, 0.15);
      position: relative;
      z-index: 10;
    }
    .brand-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      font-family: 'Outfit', sans-serif;
      font-size: 1.5rem;
      font-weight: 800;
      margin-bottom: 8px;
    }
    .logo-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background: rgba(169, 112, 255, 0.15);
      border: 1px solid var(--border-glow);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent-purple);
    }
    .login-subtitle {
      text-align: center;
      color: #94a3b8;
      font-size: 0.92rem;
      margin-bottom: 25px;
    }
    .alert-error {
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #fca5a5;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 0.86rem;
      margin-bottom: 22px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      line-height: 1.5;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 0.86rem;
      font-weight: 600;
      color: #cbd5e1;
      margin-bottom: 8px;
    }
    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-wrapper i {
      position: absolute;
      left: 16px;
      color: #64748b;
      font-size: 1rem;
    }
    .form-input {
      width: 100%;
      padding: 13px 16px 13px 44px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      color: #fff;
      font-size: 16px !important;
      outline: none;
      transition: all 0.25s ease;
    }
    .form-input:focus {
      border-color: var(--accent-purple);
      box-shadow: 0 0 12px rgba(169, 112, 255, 0.3);
      background: rgba(255, 255, 255, 0.06);
    }
    .btn-submit {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--accent-purple), var(--accent-indigo));
      border: none;
      border-radius: 10px;
      color: #fff;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: 0 4px 18px rgba(169, 112, 255, 0.35);
      margin-top: 10px;
    }
    .btn-submit:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(169, 112, 255, 0.55);
    }
    .btn-submit:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 25px;
      color: #64748b;
      font-size: 0.88rem;
      text-decoration: none;
      transition: color 0.2s;
    }
    .back-link:hover {
      color: var(--accent-purple);
    }
    .credentials-hint {
      margin-top: 20px;
      padding: 10px 14px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 8px;
      font-size: 0.78rem;
      color: #94a3b8;
      text-align: center;
      border: 1px dashed rgba(255, 255, 255, 0.08);
    }
    @media (max-width: 480px) {
      .login-card {
        padding: 30px 20px;
        border-radius: 18px;
      }
      .brand-logo {
        font-size: 1.3rem;
      }
    }
  </style>
</head>
<body>

  <div class="login-card">
    <div class="brand-logo">
      <div class="logo-icon"><i class="fa-solid fa-shield-halved"></i></div>
      <span>MINUDI<span style="color:var(--accent-purple)">.ADMIN</span></span>
    </div>
    <p class="login-subtitle">Hardened &amp; Secure Control Center</p>

    <?php if (!empty($error)): ?>
      <div class="alert-error">
        <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <!-- Cryptographic CSRF Token -->
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

      <div class="form-group">
        <label for="username">Admin Username</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-user"></i>
          <input type="text" id="username" name="username" class="form-input" placeholder="Enter username" required autofocus value="admin">
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper" style="position:relative;">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="password" name="password" class="form-input" placeholder="Enter password" required value="admin123" style="padding-right:45px;">
          <button type="button" id="toggle-pwd-btn" onclick="togglePasswordVisibility()" style="position:absolute; right:14px; background:none; border:none; color:#94a3b8; cursor:pointer; font-size:1.1rem; padding:4px; transition:color 0.2s;" title="Show/Hide Password" aria-label="Toggle password visibility">
            <i class="fa-solid fa-eye" id="toggle-pwd-icon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit" <?= !$rateLimit['allowed'] ? 'disabled' : '' ?>>
        <i class="fa-solid fa-right-to-bracket"></i> Sign In to Dashboard
      </button>

      <div class="credentials-hint">
        Protected with <strong>Bcrypt Hash</strong> &amp; <strong>Brute-Force Rate Limiting</strong>
      </div>

      <a href="../index.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Return to Live Portfolio
      </a>
    </form>
  </div>

  <script>
    function togglePasswordVisibility() {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('toggle-pwd-icon');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        icon.style.color = '#a970ff';
      } else {
        pwd.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        icon.style.color = '#94a3b8';
      }
    }
  </script>
</body>
</html>
