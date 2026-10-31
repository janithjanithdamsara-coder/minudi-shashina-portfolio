<?php
require_once __DIR__ . '/../php/security.php';
init_secure_session();

if (empty($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

// 1. Load Analytics Data
$analyticsFile = __DIR__ . '/../data/analytics.json';
$analytics = [
    'total_views'      => 0,
    'unique_visitors'  => 0,
    'today_views'      => 0,
    'today_uniques'    => 0,
    'recent_visits'    => []
];
if (file_exists($analyticsFile)) {
    $aContent = @file_get_contents($analyticsFile);
    $aDecoded = @json_decode($aContent, true);
    if (is_array($aDecoded)) {
        $analytics = array_merge($analytics, $aDecoded);
    }
}

// 2. Load Projects Data
$projectsFile = __DIR__ . '/../data/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $pContent = @file_get_contents($projectsFile);
    $pDecoded = @json_decode($pContent, true);
    if (is_array($pDecoded)) {
        $projects = $pDecoded;
    }
}

// 3. Load Contact Messages Data
$messagesFile = __DIR__ . '/../data/contact_messages.json';
$messages = [];
if (file_exists($messagesFile)) {
    $mContent = @file_get_contents($messagesFile);
    $mDecoded = @json_decode($mContent, true);
    if (is_array($mDecoded)) {
        $messages = $mDecoded;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Minudi Shashina Portfolio</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
      --bg-primary: #06080e;
      --bg-secondary: #0c101a;
      --bg-card: rgba(14, 18, 30, 0.75);
      --border-glass: rgba(255, 255, 255, 0.08);
      --border-glow: rgba(169, 112, 255, 0.35);
      --accent-purple: #a970ff;
      --accent-purple-light: #c084fc;
      --accent-cyan: #00f2fe;
      --accent-emerald: #10b981;
      --accent-amber: #f59e0b;
      --text-primary: #f8fafc;
      --text-secondary: #94a3b8;
      --text-muted: #64748b;
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      background: var(--bg-primary);
      color: var(--text-primary);
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      line-height: 1.6;
    }

    /* Container */
    .admin-container {
      max-width: 1240px;
      margin: 0 auto;
      padding: 30px 24px 80px 24px;
    }

    /* Top Navbar */
    .admin-navbar {
      background: var(--bg-card);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid var(--border-glass);
      padding: 18px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .brand-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-family: 'Outfit', sans-serif;
      font-size: 1.35rem;
      font-weight: 800;
      color: #fff;
      text-decoration: none;
    }
    .brand-logo .icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: rgba(169, 112, 255, 0.15);
      border: 1px solid var(--border-glow);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent-purple);
    }
    .nav-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .btn-nav {
      padding: 8px 18px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 0.88rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.25s ease;
    }
    .btn-nav-outline {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-glass);
      color: var(--text-secondary);
    }
    .btn-nav-outline:hover {
      border-color: var(--accent-cyan);
      color: var(--accent-cyan);
    }
    .btn-nav-danger {
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #fca5a5;
    }
    .btn-nav-danger:hover {
      background: #ef4444;
      color: #fff;
    }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-top: 30px;
      margin-bottom: 35px;
    }
    .stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-glass);
      border-radius: 16px;
      padding: 24px;
      position: relative;
      transition: transform 0.25s ease, border-color 0.25s ease;
    }
    .stat-card:hover {
      transform: translateY(-3px);
      border-color: var(--border-glow);
    }
    .stat-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }
    .stat-label {
      font-size: 0.84rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      font-weight: 600;
    }
    .stat-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
    }
    .icon-purple { background: rgba(169, 112, 255, 0.12); color: var(--accent-purple); }
    .icon-cyan   { background: rgba(0, 242, 254, 0.12); color: var(--accent-cyan); }
    .icon-emerald{ background: rgba(16, 185, 129, 0.12); color: var(--accent-emerald); }
    .icon-amber  { background: rgba(245, 158, 11, 0.12); color: var(--accent-amber); }

    .stat-value {
      font-family: 'Outfit', sans-serif;
      font-size: 2.1rem;
      font-weight: 800;
      color: #fff;
      line-height: 1.1;
      margin-bottom: 6px;
    }
    .stat-sub {
      font-size: 0.78rem;
      color: var(--text-secondary);
    }

    /* Tabs Navigation */
    .tabs-nav {
      display: flex;
      gap: 12px;
      border-bottom: 1px solid var(--border-glass);
      padding-bottom: 12px;
      margin-bottom: 25px;
    }
    .tab-btn {
      padding: 10px 22px;
      background: none;
      border: 1px solid transparent;
      color: var(--text-secondary);
      font-size: 0.95rem;
      font-weight: 600;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s ease;
    }
    .tab-btn:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.03);
    }
    .tab-btn.active {
      color: #fff;
      background: rgba(169, 112, 255, 0.12);
      border-color: var(--border-glow);
    }

    /* Tab Content Sections */
    .tab-pane {
      display: none;
    }
    .tab-pane.active {
      display: block;
    }

    /* Tables & Cards */
    .content-box {
      background: var(--bg-card);
      border: 1px solid var(--border-glass);
      border-radius: 16px;
      padding: 28px;
    }
    .box-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
    }
    .box-title {
      font-family: 'Outfit', sans-serif;
      font-size: 1.35rem;
      font-weight: 700;
      color: #fff;
    }

    .btn-primary {
      padding: 10px 22px;
      background: linear-gradient(135deg, var(--accent-purple), #8b5cf6);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 15px rgba(169, 112, 255, 0.3);
      transition: all 0.25s ease;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(169, 112, 255, 0.5);
    }

    /* Data Table */
    .table-responsive {
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.92rem;
    }
    th {
      padding: 14px 16px;
      color: var(--text-muted);
      font-weight: 600;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      border-bottom: 1px solid var(--border-glass);
    }
    td {
      padding: 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      color: var(--text-secondary);
      vertical-align: middle;
    }
    tr:hover td {
      background: rgba(255, 255, 255, 0.015);
      color: #fff;
    }

    .table-thumb {
      width: 65px;
      height: 44px;
      border-radius: 6px;
      object-fit: cover;
      background: #111;
      border: 1px solid var(--border-glass);
    }
    .badge-pill {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
    }
    .badge-web { background: rgba(0, 242, 254, 0.1); color: var(--accent-cyan); border: 1px solid rgba(0, 242, 254, 0.25); }
    .badge-mgmt { background: rgba(16, 185, 129, 0.1); color: var(--accent-emerald); border: 1px solid rgba(16, 185, 129, 0.25); }
    .badge-ent { background: rgba(169, 112, 255, 0.1); color: var(--accent-purple-light); border: 1px solid rgba(169, 112, 255, 0.25); }

    .action-btn {
      background: none;
      border: 1px solid var(--border-glass);
      color: var(--text-secondary);
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      margin-right: 6px;
      transition: all 0.2s ease;
    }
    .action-btn:hover {
      border-color: var(--accent-purple);
      color: #fff;
    }
    .action-delete:hover {
      border-color: #ef4444;
      color: #ef4444;
    }

    /* Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(10px);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    .modal-overlay.open {
      opacity: 1;
      visibility: visible;
    }
    .modal-box {
      background: var(--bg-secondary);
      border: 1px solid var(--border-glow);
      border-radius: 20px;
      max-width: 680px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      padding: 35px;
      position: relative;
    }
    .modal-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: none;
      border: none;
      color: var(--text-muted);
      font-size: 1.4rem;
      cursor: pointer;
    }
    .modal-close:hover { color: #fff; }

    .form-group {
      margin-bottom: 18px;
    }
    .form-group label {
      display: block;
      font-size: 0.86rem;
      font-weight: 600;
      color: #cbd5e1;
      margin-bottom: 6px;
    }
    .form-input, .form-textarea, .form-select {
      width: 100%;
      padding: 12px 14px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-glass);
      border-radius: 8px;
      color: #fff;
      font-family: inherit;
      font-size: 0.92rem;
      outline: none;
    }
    .form-input:focus, .form-textarea:focus, .form-select:focus {
      border-color: var(--accent-purple);
    }
    .form-select option {
      background: #111624;
      color: #fff;
    }
    .file-input-box {
      border: 2px dashed var(--border-glass);
      border-radius: 10px;
      padding: 22px;
      text-align: center;
      cursor: pointer;
      background: rgba(255, 255, 255, 0.015);
      transition: border-color 0.25s ease;
    }
    .file-input-box:hover {
      border-color: var(--accent-purple);
    }

    /* Existing Image Manager Cards in Modal */
    .existing-img-card {
      position: relative;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid var(--border-glass);
      aspect-ratio: 16/10;
      background: #000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      transition: all 0.25s ease;
    }
    .existing-img-card:hover {
      border-color: var(--accent-purple);
      transform: translateY(-2px);
    }
    .existing-img-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .img-del-btn {
      position: absolute;
      top: 6px;
      right: 6px;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: rgba(239, 68, 68, 0.9);
      color: #fff;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.5);
      transition: all 0.2s ease;
    }
    .img-del-btn:hover {
      background: #dc2626;
      transform: scale(1.15);
    }

    @media (max-width: 900px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 680px) {
      .admin-navbar { padding: 12px 16px; }
      .brand-logo { font-size: 1.15rem; gap: 8px; }
      .brand-logo .icon { width: 30px; height: 30px; font-size: 0.85rem; }
      .btn-nav { padding: 6px 10px; font-size: 0.78rem; gap: 5px; }
      .admin-container { padding: 18px 14px 60px 14px; }
      .stats-grid { grid-template-columns: 1fr; gap: 12px; margin-top: 20px; margin-bottom: 25px; }
      .stat-card { padding: 18px 16px; }
      .stat-value { font-size: 1.8rem; }
      .tabs-nav { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }
      .tab-btn { white-space: nowrap; padding: 8px 14px; font-size: 0.82rem; flex-shrink: 0; }
      .content-box { padding: 18px 14px; border-radius: 14px; }
      .box-header { flex-direction: column; align-items: flex-start; gap: 12px; }
      .modal-box { padding: 22px 15px; width: 95%; max-height: 94vh; border-radius: 16px; }
      .form-input, .form-textarea, .form-select { font-size: 16px !important; }
    }
  </style>
</head>
<body>

  <!-- Top Navbar -->
  <header class="admin-navbar">
    <a href="index.php" class="brand-logo">
      <div class="icon"><i class="fa-solid fa-code"></i></div>
      <span>MINUDI<span>.ADMIN</span></span>
    </a>

    <div class="nav-actions">
      <a href="../index.php" target="_blank" class="btn-nav btn-nav-outline">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> Live Portfolio
      </a>
      <a href="logout.php" class="btn-nav btn-nav-danger">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </a>
    </div>
  </header>

  <div class="admin-container">
    
    <!-- Welcome Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:10px;">
      <div>
        <h1 style="font-family:'Outfit',sans-serif; font-size:2rem; font-weight:800; color:#fff;">Control Center &amp; Analytics</h1>
        <p style="color:var(--text-secondary); font-size:0.95rem;">Manage project showcases, view real-time visitor counts, and monitor client inquiries.</p>
      </div>
      <div>
        <span class="badge-pill" style="background:rgba(16,185,129,0.1); color:var(--accent-emerald); border:1px solid rgba(16,185,129,0.3); font-size:0.85rem; padding:6px 14px;">
          <i class="fa-solid fa-circle" style="font-size:0.6rem; margin-right:5px;"></i> Tracking Active
        </span>
      </div>
    </div>

    <!-- 4 Key Stat Cards -->
    <div class="stats-grid">
      
      <!-- Card 1: Total Views -->
      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Total Page Views</span>
          <div class="stat-icon icon-purple"><i class="fa-solid fa-eye"></i></div>
        </div>
        <div class="stat-value"><?= number_format($analytics['total_views'] ?? 0) ?></div>
        <div class="stat-sub">Today: +<?= number_format($analytics['today_views'] ?? 0) ?> views</div>
      </div>

      <!-- Card 2: Unique Visitors -->
      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Unique Visitors</span>
          <div class="stat-icon icon-cyan"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="stat-value"><?= number_format($analytics['unique_visitors'] ?? 0) ?></div>
        <div class="stat-sub">Real visitors (deduplicated by IP)</div>
      </div>

      <!-- Card 3: Live Projects -->
      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Live Projects</span>
          <div class="stat-icon icon-emerald"><i class="fa-solid fa-laptop-code"></i></div>
        </div>
        <div class="stat-value"><?= count($projects) ?></div>
        <div class="stat-sub">Featured in portfolio</div>
      </div>

      <!-- Card 4: Contact Messages -->
      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-label">Messages Received</span>
          <div class="stat-icon icon-amber"><i class="fa-solid fa-envelope"></i></div>
        </div>
        <div class="stat-value"><?= count($messages) ?></div>
        <div class="stat-sub">Client &amp; recruiter inquiries</div>
      </div>

    </div>

    <!-- Navigation Tabs -->
    <div class="tabs-nav">
      <button class="tab-btn active" onclick="switchTab('projects-tab', this)">
        <i class="fa-solid fa-folder-open"></i> Projects Management (<?= count($projects) ?>)
      </button>
      <button class="tab-btn" onclick="switchTab('analytics-tab', this)">
        <i class="fa-solid fa-chart-line"></i> Visitor Analytics &amp; Log
      </button>
      <button class="tab-btn" onclick="switchTab('inquiries-tab', this)">
        <i class="fa-solid fa-inbox"></i> Inquiries Inbox (<?= count($messages) ?>)
      </button>
      <button class="tab-btn" onclick="switchTab('audit-tab', this)">
        <i class="fa-solid fa-shield-halved"></i> Security Audit Log
      </button>
      <button class="tab-btn" onclick="switchTab('password-tab', this)">
        <i class="fa-solid fa-key"></i> Change Password
      </button>
    </div>

    <!-- ==========================================
         TAB 1: PROJECTS MANAGEMENT
         ========================================== -->
    <div id="projects-tab" class="tab-pane active">
      <div class="content-box">
        <div class="box-header">
          <div>
            <h3 class="box-title">Portfolio Projects</h3>
            <p style="color:var(--text-muted); font-size:0.86rem;">Add new software projects with multiple UI screenshots or edit existing ones.</p>
          </div>
          <button class="btn-primary" onclick="openAddProjectModal()">
            <i class="fa-solid fa-plus"></i> Add New Project
          </button>
        </div>

        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Preview UI</th>
                <th>Project Title</th>
                <th>Category</th>
                <th>UI Screenshots</th>
                <th>Tech Stack</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($projects)): ?>
                <tr>
                  <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No projects found. Click "Add New Project" to get started.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($projects as $proj): ?>
                  <tr>
                    <td>
                      <?php 
                        $thumb = !empty($proj['images'][0]) ? '../' . htmlspecialchars($proj['images'][0]) : '../images/profile.jpg';
                      ?>
                      <img src="<?= $thumb ?>" alt="UI" class="table-thumb" onerror="this.src='../images/profile.jpg'">
                    </td>
                    <td>
                      <strong style="color:#fff;"><?= htmlspecialchars($proj['title']) ?></strong>
                      <div style="font-size:0.8rem; color:var(--text-muted); max-width:280px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <?= htmlspecialchars($proj['short_desc']) ?>
                      </div>
                    </td>
                    <td>
                      <?php 
                        $cat = $proj['category'] ?? 'web';
                        $badgeClass = $cat === 'web' ? 'badge-web' : ($cat === 'management' ? 'badge-mgmt' : 'badge-ent');
                      ?>
                      <span class="badge-pill <?= $badgeClass ?>"><?= htmlspecialchars($proj['category_name'] ?? ucfirst($cat)) ?></span>
                    </td>
                    <td>
                      <button type="button" class="action-btn" style="width:auto; padding:4px 10px; font-family:'JetBrains Mono',monospace; font-size:0.82rem; color:var(--accent-cyan); border-color:rgba(0,242,254,0.3);" title="View & Manage Screenshots" onclick='editProject(<?= json_encode($proj) ?>)'>
                        <i class="fa-solid fa-images"></i> <?= count($proj['images'] ?? []) ?> Screenshots
                      </button>
                    </td>
                    <td>
                      <div style="font-size:0.8rem; color:var(--text-secondary); max-width:200px;">
                        <?= htmlspecialchars(implode(', ', $proj['tech_stack'] ?? [])) ?>
                      </div>
                    </td>
                    <td>
                      <button class="action-btn" title="Edit Project" onclick='editProject(<?= json_encode($proj) ?>)'>
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <button class="action-btn action-delete" title="Delete Project" onclick="deleteProject('<?= $proj['id'] ?>')">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    

    <!-- ==========================================
         TAB 2: VISITOR ANALYTICS
         ========================================== -->
    <div id="analytics-tab" class="tab-pane">
      <div class="content-box">
        <div class="box-header">
          <div>
            <h3 class="box-title">Accurate Visitor Analytics</h3>
            <p style="color:var(--text-muted); font-size:0.86rem;">Monitors genuine page views and unique visitor IPs. Ignores admin page refreshes.</p>
          </div>
          <div>
            <span style="font-size:0.85rem; color:var(--accent-cyan); font-family:'JetBrains Mono',monospace;">
              Today: <?= number_format($analytics['today_uniques'] ?? 0) ?> Unique Users
            </span>
          </div>
        </div>

        <h4 style="font-size:1rem; color:#fff; margin-bottom:14px;">Recent Visitor Activity (Last 50 Visits):</h4>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Timestamp</th>
                <th>Visitor IP</th>
                <th>Device</th>
                <th>Browser</th>
                <th>Page Route</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $visits = $analytics['recent_visits'] ?? [];
                if (empty($visits)):
              ?>
                <tr>
                  <td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No visitor logs recorded yet. Visit the portfolio to see live tracking in action.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($visits as $v): ?>
                  <tr>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:0.82rem; color:var(--accent-purple-light);">
                      <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($v['timestamp'] ?? '') ?>
                    </td>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:0.85rem; color:#fff;">
                      <?= htmlspecialchars($v['ip'] ?? 'Unknown') ?>
                    </td>
                    <td>
                      <span class="badge-pill" style="background:rgba(255,255,255,0.04); color:#cbd5e1;">
                        <i class="fa-solid <?= ($v['device'] ?? '') === 'Mobile' ? 'fa-mobile-screen' : 'fa-laptop' ?>"></i>
                        <?= htmlspecialchars($v['device'] ?? 'Desktop') ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($v['browser'] ?? 'Browser') ?></td>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:0.82rem; color:var(--accent-cyan);">
                      <?= htmlspecialchars($v['page'] ?? '/') ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    

    <!-- ==========================================
         TAB 3: INQUIRIES INBOX
         ========================================== -->
    <div id="inquiries-tab" class="tab-pane">
      <div class="content-box">
        <div class="box-header">
          <div>
            <h3 class="box-title">Client &amp; Recruiter Messages</h3>
            <p style="color:var(--text-muted); font-size:0.86rem;">Inquiries submitted through the portfolio contact form.</p>
          </div>
        </div>

        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Date / Time</th>
                <th>Sender Name</th>
                <th>Email Address</th>
                <th>Subject</th>
                <th>Message Content</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($messages)): ?>
                <tr>
                  <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No messages received yet. All new inquiries from the contact form will appear here.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach (array_reverse($messages) as $m): ?>
                  <tr>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:0.82rem; color:var(--text-muted); white-space:nowrap;">
                      <?= htmlspecialchars($m['timestamp'] ?? '') ?>
                    </td>
                    <td><strong style="color:#fff;"><?= htmlspecialchars($m['name'] ?? '') ?></strong></td>
                    <td>
                      <a href="mailto:<?= htmlspecialchars($m['email'] ?? '') ?>" style="color:var(--accent-cyan); text-decoration:none;">
                        <?= htmlspecialchars($m['email'] ?? '') ?>
                      </a>
                    </td>
                    <td><?= htmlspecialchars($m['subject'] ?? 'General Inquiry') ?></td>
                    <td style="max-width:320px; font-size:0.88rem; color:#cbd5e1;">
                      <?= nl2br(htmlspecialchars($m['message'] ?? '')) ?>
                    </td>
                    <td>
                      <a href="mailto:<?= htmlspecialchars($m['email'] ?? '') ?>?subject=Re: <?= urlencode($m['subject'] ?? 'Portfolio Inquiry') ?>" class="action-btn" title="Reply via Email" style="color:var(--accent-purple);">
                        <i class="fa-solid fa-reply"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    

  </div>

  
    <!-- ==========================================
         TAB 4: SECURITY AUDIT LOGS
         ========================================== -->
    <div id="audit-tab" class="tab-pane">
      <div class="content-box">
        <div class="box-header">
          <div>
            <h3 class="box-title">Security &amp; Administrative Audit Trail</h3>
            <p style="color:var(--text-muted); font-size:0.86rem;">Chronological record of logins, project modifications, and security events.</p>
          </div>
          <div>
            <span class="badge-pill" style="background:rgba(16,185,129,0.1); color:var(--accent-emerald); border:1px solid rgba(16,185,129,0.25);">
              <i class="fa-solid fa-lock"></i> Audit Logging Active
            </span>
          </div>
        </div>

        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Timestamp</th>
                <th>Source IP</th>
                <th>Security Action</th>
                <th>Event Details</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $auditLogFile = __DIR__ . '/../data/admin_audit.log';
                $auditLogs = [];
                if (file_exists($auditLogFile)) {
                    $rawLines = @file($auditLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    if (is_array($rawLines)) {
                        $auditLogs = array_reverse(array_slice($rawLines, -50));
                    }
                }
              ?>
              <?php if (empty($auditLogs)): ?>
                <tr>
                  <td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">
                    No audit records logged yet. Actions will automatically appear here.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($auditLogs as $logLine): ?>
                  <?php
                    $time = ''; $ip = ''; $action = ''; $details = '';
                    if (preg_match('/\\[(.*?)\\]\\s+IP:\\s+([^|]+)\\s+\\|\\s+Action:\\s+([^|]+)(?:\\|\\s+Details:\\s*(.*))?/', $logLine, $matches)) {
                        $time    = trim($matches[1]);
                        $ip      = trim($matches[2]);
                        $action  = trim($matches[3]);
                        $details = trim($matches[4] ?? '');
                    } else {
                        $details = $logLine;
                    }
                  ?>
                  <tr>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:0.82rem; color:var(--text-muted); white-space:nowrap;">
                      <?= htmlspecialchars($time) ?>
                    </td>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:0.84rem; color:var(--accent-cyan);">
                      <?= htmlspecialchars($ip) ?>
                    </td>
                    <td>
                      <span class="badge-pill" style="background:rgba(169,112,255,0.12); color:var(--accent-purple-light); border:1px solid rgba(169,112,255,0.3);">
                        <?= htmlspecialchars($action) ?>
                      </span>
                    </td>
                    <td style="font-size:0.88rem; color:#cbd5e1;">
                      <?= htmlspecialchars($details) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ==========================================
         TAB 5: CHANGE ADMIN PASSWORD
         ========================================== -->
    <div id="password-tab" class="tab-pane">
      <div class="content-box" style="max-width: 600px; margin: 0 auto;">
        <div class="box-header">
          <div>
            <h3 class="box-title"><i class="fa-solid fa-key" style="color:var(--accent-purple); margin-right:8px;"></i> Change Admin Password</h3>
            <p style="color:var(--text-muted); font-size:0.86rem; margin-top:4px;">Update your administrator credentials securely with Bcrypt encryption.</p>
          </div>
        </div>

        <form id="change-pwd-form" style="display:flex; flex-direction:column; gap:18px;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

          <div id="pwd-alert" style="display:none; padding:12px 16px; border-radius:10px; font-size:0.9rem;"></div>

          <div class="form-group">
            <label for="current_password" style="display:block; margin-bottom:8px; font-weight:600; font-size:0.88rem; color:#cbd5e1;">Current Password *</label>
            <div style="position:relative;">
              <input type="password" id="current_password" name="current_password" class="form-input" style="width:100%; padding-right:45px;" placeholder="Enter current admin password" required>
              <button type="button" class="pwd-eye-btn" onclick="toggleFieldVisibility('current_password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.05rem;" title="Show/Hide Password">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="new_password" style="display:block; margin-bottom:8px; font-weight:600; font-size:0.88rem; color:#cbd5e1;">New Password *</label>
            <div style="position:relative;">
              <input type="password" id="new_password" name="new_password" class="form-input" style="width:100%; padding-right:45px;" placeholder="Minimum 6 characters" required minlength="6">
              <button type="button" class="pwd-eye-btn" onclick="toggleFieldVisibility('new_password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.05rem;" title="Show/Hide Password">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="confirm_password" style="display:block; margin-bottom:8px; font-weight:600; font-size:0.88rem; color:#cbd5e1;">Confirm New Password *</label>
            <div style="position:relative;">
              <input type="password" id="confirm_password" name="confirm_password" class="form-input" style="width:100%; padding-right:45px;" placeholder="Re-type new password" required minlength="6">
              <button type="button" class="pwd-eye-btn" onclick="toggleFieldVisibility('confirm_password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.05rem;" title="Show/Hide Password">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:10px;">
            <button type="submit" id="save-pwd-btn" class="btn-primary" style="padding:12px 24px;">
              <i class="fa-solid fa-lock"></i> Update Password
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <!-- ===================================================================
       ADD / EDIT PROJECT MODAL
       =================================================================== -->
  <div id="project-modal" class="modal-overlay">
    <div class="modal-box">
      <button class="modal-close" onclick="closeProjectModal()"><i class="fa-solid fa-xmark"></i></button>
      
      <h3 id="modal-heading" style="font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:700; color:#fff; margin-bottom:20px;">
        Add New Software Project
      </h3>

      <form id="project-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
        <input type="hidden" id="proj-id" name="id" value="">

        <div class="form-group">
          <label for="proj-title">Project Title *</label>
          <input type="text" id="proj-title" name="title" class="form-input" placeholder="e.g. Hospital Clinic Management System" required>
        </div>

        <div class="form-group">
          <label for="proj-category">Category *</label>
          <select id="proj-category" name="category" class="form-select">
            <option value="web">Web Application</option>
            <option value="management">Management System</option>
            <option value="enterprise">Enterprise System</option>
          </select>
        </div>

        <div class="form-group">
          <label for="proj-short">Short Description (for Project Card) *</label>
          <textarea id="proj-short" name="short_desc" class="form-textarea" rows="2" placeholder="Brief 1-2 sentence overview shown on the card..." required></textarea>
        </div>

        <div class="form-group">
          <label for="proj-full">Full Detailed Description (for Gallery Modal)</label>
          <textarea id="proj-full" name="full_desc" class="form-textarea" rows="4" placeholder="Detailed architectural description, features, database schema, user flows..."></textarea>
        </div>

        <div class="form-group">
          <label for="proj-tech">Tech Stack Tags (Comma Separated)</label>
          <input type="text" id="proj-tech" name="tech_stack" class="form-input" placeholder="e.g. PHP, MySQL, JavaScript, Bootstrap">
        </div>

        <!-- Existing Project Screenshots Manager -->
        <div class="form-group" id="existing-images-group" style="display:none;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <label style="margin-bottom:0; font-weight:700; color:#fff;">
              <i class="fa-solid fa-images" style="color:var(--accent-purple); margin-right:6px;"></i> Current Screenshots (<span id="existing-images-count">0</span>)
            </label>
            <span style="font-size:0.78rem; color:var(--text-muted);">
              Click <i class="fa-solid fa-trash-can" style="color:#ef4444;"></i> to delete any image
            </span>
          </div>

          <div id="existing-images-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:12px; background:rgba(255,255,255,0.02); padding:14px; border-radius:12px; border:1px solid var(--border-glass);">
            <!-- Populated via JS -->
          </div>
        </div>

        <!-- Multi-Image Upload Field -->
        <div class="form-group">
          <label><i class="fa-solid fa-cloud-arrow-up" style="color:var(--accent-cyan); margin-right:6px;"></i> Upload Additional Screenshots (.jpg, .png, .webp)</label>
          <div class="file-input-box" onclick="document.getElementById('proj-images').click()">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size:2rem; color:var(--accent-purple); margin-bottom:8px;"></i>
            <div style="font-weight:600; color:#fff;">Click to select multiple UI screenshots (.jpg, .png, .webp)</div>
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:4px;">You can select 2, 3 or more images at once</div>
            <div id="file-count-label" style="margin-top:8px; font-weight:700; color:var(--accent-cyan);"></div>
          </div>
          <input type="file" id="proj-images" name="images[]" multiple accept="image/*" style="display:none;" onchange="updateFileLabel(this)">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:25px;">
          <button type="button" class="btn-nav btn-nav-outline" onclick="closeProjectModal()">Cancel</button>
          <button type="submit" id="save-proj-btn" class="btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Save Project
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Tab Switching
    function switchTab(tabId, btn) {
      document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
      document.getElementById(tabId).classList.add('active');
      btn.classList.add('active');
    }

    // Modal Handling
    const modal = document.getElementById('project-modal');
    function openAddProjectModal() {
      document.getElementById('project-form').reset();
      document.getElementById('proj-id').value = '';
      document.getElementById('modal-heading').textContent = 'Add New Software Project';
      document.getElementById('file-count-label').textContent = '';
      document.getElementById('existing-images-group').style.display = 'none';
      document.getElementById('existing-images-grid').innerHTML = '';
      modal.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeProjectModal() {
      if (modal) modal.classList.remove('open');
      document.body.style.overflow = '';
    }

    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) closeProjectModal();
      });
      window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' || e.keyCode === 27) closeProjectModal();
      });
    }

    function updateFileLabel(input) {
      const label = document.getElementById('file-count-label');
      if (input.files && input.files.length > 0) {
        label.textContent = `✓ ${input.files.length} screenshot(s) selected for upload`;
      } else {
        label.textContent = '';
      }
    }

    // Edit Project & Populate Existing Images
    function editProject(proj) {
      document.getElementById('proj-id').value = proj.id;
      document.getElementById('proj-title').value = proj.title;
      document.getElementById('proj-category').value = proj.category || 'web';
      document.getElementById('proj-short').value = proj.short_desc || '';
      document.getElementById('proj-full').value = proj.full_desc || '';
      document.getElementById('proj-tech').value = (proj.tech_stack || []).join(', ');
      document.getElementById('modal-heading').textContent = 'Edit Project & Manage Screenshots: ' + proj.title;
      document.getElementById('file-count-label').textContent = '';

      // Populate Existing Screenshots
      const imagesGroup = document.getElementById('existing-images-group');
      const imagesGrid = document.getElementById('existing-images-grid');
      const images = proj.images || [];

      imagesGrid.innerHTML = '';
      if (images.length > 0) {
        imagesGroup.style.display = 'block';
        document.getElementById('existing-images-count').textContent = images.length;
        
        images.forEach((img, idx) => {
          const fileName = img.split('/').pop();
          const card = document.createElement('div');
          card.className = 'existing-img-card';
          card.innerHTML = `
            <img src="../${img}" alt="Screenshot ${idx + 1}" onerror="this.src='../images/profile.jpg'">
            <input type="hidden" name="existing_images[]" value="${img}">
            <button type="button" class="img-del-btn" onclick="removeImageCard(this, '${proj.id}', '${img}')" title="Delete Screenshot">
              <i class="fa-solid fa-trash-can"></i>
            </button>
            <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(0,0,0,0.85)); padding:4px 6px; font-size:0.68rem; color:#cbd5e1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              ${fileName}
            </div>
          `;
          imagesGrid.appendChild(card);
        });
      } else {
        imagesGroup.style.display = 'none';
      }

      modal.classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    // Direct Image Deletion
    async function removeImageCard(btn, projId, imgPath) {
      const fileName = imgPath.split('/').pop();
      if (!confirm(`Are you sure you want to delete this screenshot?\n\n"${fileName}"\n\nClick OK to permanently delete it.`)) {
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="font-size:0.7rem;"></i>';

      const fd = new FormData();
      fd.append('project_id', projId);
      fd.append('image_path', imgPath);
      fd.append('csrf_token', '<?= get_csrf_token() ?>');

      try {
        const res = await fetch('api/delete_project_image.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.status === 'success') {
          const card = btn.closest('.existing-img-card');
          card.style.opacity = '0';
          card.style.transform = 'scale(0.8)';
          setTimeout(() => {
            card.remove();
            const remainingCards = document.querySelectorAll('#existing-images-grid .existing-img-card');
            document.getElementById('existing-images-count').textContent = remainingCards.length;
            if (remainingCards.length === 0) {
              document.getElementById('existing-images-grid').innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:15px; color:var(--text-muted); font-size:0.85rem;"><i class="fa-solid fa-triangle-exclamation" style="color:var(--accent-amber); margin-right:6px;"></i> All screenshots deleted. Please upload new screenshots below.</div>';
            }
          }, 250);
        } else {
          alert(data.message || 'Error deleting screenshot.');
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        }
      } catch (err) {
        alert('Server communication error.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
      }
    }

    // Delete Project
    async function deleteProject(id) {
      if (!confirm('Are you sure you want to delete this project?')) return;

      const fd = new FormData();
      fd.append('id', id);
      fd.append('csrf_token', '<?= get_csrf_token() ?>');

      try {
        const res = await fetch('api/delete_project.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.status === 'success') {
          alert('Project deleted successfully.');
          location.reload();
        } else {
          alert(data.message || 'Error deleting project');
        }
      } catch (e) {
        alert('Server communication error.');
      }
    }

    // Save Project Form Submit
    document.getElementById('project-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('save-proj-btn');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;

      const formData = new FormData(e.target);

      try {
        const res = await fetch('api/save_project.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
          alert(data.message);
          location.reload();
        } else {
          alert(data.message || 'Error saving project.');
        }
      } catch (err) {
        alert('Upload or server error.');
      } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    });

    // Toggle Password Visibility in Admin Panel
    function toggleFieldVisibility(fieldId, btn) {
      const input = document.getElementById(fieldId);
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }

    // Change Password Form Submit
    const pwdForm = document.getElementById('change-pwd-form');
    if (pwdForm) {
      pwdForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const alertBox = document.getElementById('pwd-alert');
        const submitBtn = document.getElementById('save-pwd-btn');
        const originalText = submitBtn.innerHTML;

        alertBox.style.display = 'none';

        const newP = document.getElementById('new_password').value;
        const confP = document.getElementById('confirm_password').value;
        if (newP !== confP) {
          alertBox.style.display = 'block';
          alertBox.style.background = 'rgba(239, 68, 68, 0.15)';
          alertBox.style.border = '1px solid rgba(239, 68, 68, 0.4)';
          alertBox.style.color = '#fca5a5';
          alertBox.textContent = 'New password and confirmation password do not match.';
          return;
        }

        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;

        try {
          const res = await fetch('api/change_password.php', {
            method: 'POST',
            body: new FormData(pwdForm)
          });
          const data = await res.json();
          alertBox.style.display = 'block';
          if (data.status === 'success') {
            alertBox.style.background = 'rgba(16, 185, 129, 0.15)';
            alertBox.style.border = '1px solid rgba(16, 185, 129, 0.4)';
            alertBox.style.color = '#6ee7b7';
            alertBox.textContent = data.message;
            pwdForm.reset();
          } else {
            alertBox.style.background = 'rgba(239, 68, 68, 0.15)';
            alertBox.style.border = '1px solid rgba(239, 68, 68, 0.4)';
            alertBox.style.color = '#fca5a5';
            alertBox.textContent = data.message || 'Error changing password.';
          }
        } catch (err) {
          alertBox.style.display = 'block';
          alertBox.style.background = 'rgba(239, 68, 68, 0.15)';
          alertBox.style.border = '1px solid rgba(239, 68, 68, 0.4)';
          alertBox.style.color = '#fca5a5';
          alertBox.textContent = 'Server communication error. Please try again.';
        } finally {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }
      });
    }
  </script>
</body>
</html>
