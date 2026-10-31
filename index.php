<?php
// Initialize Secure Framework and Tracking
require_once __DIR__ . '/php/security.php';
init_secure_session();
require_once __DIR__ . '/php/tracker.php';

// Load Projects from JSON
$projectsFile = __DIR__ . '/data/projects.json';
$projectsList = [];
if (file_exists($projectsFile)) {
    $pContent = @file_get_contents($projectsFile);
    $pDecoded = @json_decode($pContent, true);
    if (is_array($pDecoded)) {
        $projectsList = $pDecoded;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Minudi Shashina | Software Developer &amp; ICT Technician</title>
  <meta name="description" content="Portfolio of Minudi Shashina - Software Developer (NVQ Level 4) & ICT Technician (NVQ Level 5, NVTI Baddegama). Experience in PHP, MySQL, Java, web systems, and enterprise IT support." />
  
  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="Minudi Shashina | Software Developer &amp; ICT Professional" />
  <meta property="og:description" content="Explore Minudi Shashina's developer portfolio featuring web applications, enterprise software solutions, and academic credentials from NVTI Baddegama." />
  <meta property="og:image" content="images/profile.jpg" />
  <meta property="og:type" content="website" />

  <!-- SVG Favicon -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%23a970ff'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='46' font-weight='900' fill='%2306080e'>MS</text></svg>" />

  <!-- Google Fonts: Plus Jakarta Sans, Outfit, JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- FontAwesome 6 Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="styles.css" />

  <!-- Three.js (3D WebGL Ambient Particles Library) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</head>
<body>

  <!-- Custom Glowing Mouse Cursor -->
  <div id="custom-cursor" class="custom-cursor"></div>
  <div id="custom-cursor-follower" class="custom-cursor-follower"></div>

  <!-- Three.js 3D Ambient Canvas Background -->
  <div id="canvas-container"></div>

  <!-- Toast Notifications Container -->
  <div id="toast-container" class="toast-container"></div>

  <!-- Floating Scroll To Top Widget -->
  <button id="scroll-top-btn" class="scroll-top-widget" title="Back to Top">
    <i class="fa-solid fa-arrow-up"></i>
  </button>

  <!-- ===================================================================
       SITE HEADER & NAVIGATION
       =================================================================== -->
  <header class="site-header">
    <div class="container header-content">
      <a href="#hero" class="brand-logo">
        <div class="logo-icon">
          <i class="fa-solid fa-code"></i>
        </div>
        <span class="logo-text-full">MINUDI<span>.DEV</span></span>
      </a>

      <!-- Desktop Navigation Menu -->
      <nav>
        <ul class="nav-menu">
          <li><a href="#hero" class="nav-link active">Home</a></li>
          <li><a href="#about" class="nav-link">About</a></li>
          <li><a href="#education" class="nav-link">Education</a></li>
          <li><a href="#experience" class="nav-link">Experience</a></li>
          <li><a href="#skills" class="nav-link">Skills</a></li>
          <li><a href="#projects" class="nav-link">Projects</a></li>
          <li><a href="#terminal" class="nav-link">Terminal</a></li>
          <li><a href="#contact" class="nav-link">Contact</a></li>
        </ul>
      </nav>

      <!-- Status Badge & Admin / Mobile Toggle -->
      <div class="header-actions">
        <div class="status-badge" title="Status: Available for Opportunities">
          <div class="status-dot"></div>
          <span>Available for Opportunities</span>
        </div>

        <a href="admin/login.php" class="btn-nav btn-nav-outline" style="padding:6px 14px; font-size:0.8rem; text-decoration:none; color:var(--text-muted); border-radius:30px; border:1px solid var(--border-glass);" title="Admin Access">
          <i class="fa-solid fa-lock"></i>
        </a>

        <button id="mobile-menu-toggle" class="mobile-toggle-btn" title="Toggle Navigation">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile Drawer Overlay & Menu -->
  <div id="mobile-drawer-overlay" class="mobile-drawer-overlay"></div>
  <aside id="mobile-nav-drawer" class="mobile-nav-drawer">
    <div>
      <div style="display:flex; align-items:center; justify-content:space-between;">
        <div class="brand-logo">
          <div class="logo-icon"><i class="fa-solid fa-code"></i></div>
          <span>MINUDI<span>.DEV</span></span>
        </div>
        <button id="mobile-drawer-close" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <ul class="mobile-nav-links">
        <li><a href="#hero"><i class="fa-solid fa-house"></i> Home</a></li>
        <li><a href="#about"><i class="fa-solid fa-user"></i> About</a></li>
        <li><a href="#education"><i class="fa-solid fa-graduation-cap"></i> Education</a></li>
        <li><a href="#experience"><i class="fa-solid fa-briefcase"></i> Experience</a></li>
        <li><a href="#skills"><i class="fa-solid fa-layer-group"></i> Skills</a></li>
        <li><a href="#projects"><i class="fa-solid fa-laptop-code"></i> Projects</a></li>
        <li><a href="#terminal"><i class="fa-solid fa-terminal"></i> Terminal</a></li>
        <li><a href="#contact"><i class="fa-solid fa-envelope"></i> Contact</a></li>
        <li><a href="admin/login.php"><i class="fa-solid fa-shield-halved"></i> Admin Control</a></li>
      </ul>
    </div>

    <div style="font-size:0.8rem; color:var(--text-muted); text-align:center;">
      © 2026 Minudi Shashina
    </div>
  </aside>

  <!-- ===================================================================
       HERO SECTION
       =================================================================== -->
  <section id="hero" class="hero-section">
    <div class="container hero-grid">
      
      <!-- Left Column: Introduction & CTAs -->
      <div class="hero-content">
        <div class="badge-pill">
          <i class="fa-solid fa-certificate"></i>
          <span>NVQ LEVEL 4 &amp; DIPLOMA IN ICT (NVTI BADDEGAMA)</span>
        </div>

        <h1 class="hero-title">
          Hello, I'm <br />
          <span class="text-gradient">Minudi Shashina</span>
        </h1>

        <div class="hero-subtitle-wrapper">
          <span>I am a&nbsp;</span>
          <span class="typing-text">Software Developer</span>
          <span class="typing-cursor"></span>
        </div>

        <p class="hero-description">
          Motivated Software Developer and ICT professional with hands-on industry experience from Sakwa Canneries &amp; Exports (Pvt) Ltd. Skilled in database architecture, PHP/MySQL, Java, and modern web systems with a proven track record in leadership, education, and team collaboration.
        </p>

        <div class="btn-group">
          <a href="#projects" class="btn btn-primary">
            <i class="fa-solid fa-rocket"></i> View Projects
          </a>
          <a href="#contact" class="btn btn-outline">
            <i class="fa-solid fa-paper-plane"></i> Contact Me
          </a>
          <a href="#about" class="btn btn-outline" style="border-style:dashed;">
            <i class="fa-solid fa-id-card"></i> About Me
          </a>
        </div>

        <!-- Hero Stats Grid -->
        <div class="hero-stats-grid">
          <div class="stat-item">
            <h4>NVQ 4 &amp; 5</h4>
            <p>NVTI Baddegama</p>
          </div>
          <div class="stat-item">
            <h4>Sakwa Canneries</h4>
            <p>Industry Experience</p>
          </div>
          <div class="stat-item">
            <h4>Full-Stack</h4>
            <p>PHP, MySQL, Java</p>
          </div>
          <div class="stat-item">
            <h4>4+ Roles</h4>
            <p>Dev, IT, Teaching &amp; Biz</p>
          </div>
        </div>
      </div>

      <!-- Right Column: Visual Portrait Card -->
      <div class="hero-visual-wrapper">
        <div class="portrait-glow-ring">
          <div class="portrait-img-box">
            <img src="images/profile.jpg" alt="Minudi Shashina Galbokka Hewage" />
          </div>

          <div class="portrait-badge-floating">
            <i class="fa-solid fa-shield-halved"></i>
            <div>
              <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">PROFESSIONAL QUALIFICATION</div>
              <div style="font-size:0.88rem; font-weight:700; color:#fff;">NVTI Software Developer</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ===================================================================
       ABOUT ME SECTION
       =================================================================== -->
  <section id="about" class="section-padding">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// ABOUT THE DEVELOPER</span>
        <h2 class="section-title">Driven By Passion. Powered By Purpose.</h2>
        <p class="section-subtitle">
          Discover my background, technical journey, qualifications, and multi-disciplinary experience in IT, education, and business.
        </p>
      </div>

      <div class="about-grid">
        
        <!-- Bio Card -->
        <div class="glass-card about-bio-card">
          <div>
            <h3>Software Developer &amp; ICT Technician</h3>
            <p>
              I am Minudi Shashina from Galle, Sri Lanka. Having completed my G.C.E. Ordinary Level exams in the English Medium and the <strong>Software Developer – NVQ Level 4</strong> qualification at the National Vocational Training Institute (NVTI) Baddegama, I am currently completing the final stages of my <strong>Diploma in ICT Technician – NVQ Level 5</strong>.
            </p>
            <p>
              I gained valuable industry internship experience as a <strong>Software Developer &amp; IT Assistant at Sakwa Canneries &amp; Exports (Pvt) Ltd</strong>, where I contributed to internal software tools, database maintenance, and operational IT support.
            </p>
            <p>
              Beyond technical engineering, my career journey includes working as an English Teacher, Business Development Associate, and Dance Teacher. This multifaceted background has cultivated strong verbal and written communication, leadership, and time-management capabilities. I am a quick learner eager to embrace modern technologies and build high-impact software systems.
            </p>
          </div>

          <div class="soft-skills-container">
            <h4>Key Soft Skills &amp; Competencies:</h4>
            <div class="skill-tags">
              <span class="tag-badge"><i class="fa-solid fa-users"></i> Teamwork &amp; Collaboration</span>
              <span class="tag-badge"><i class="fa-solid fa-clock"></i> Time Management</span>
              <span class="tag-badge"><i class="fa-solid fa-diagram-project"></i> Project Management</span>
              <span class="tag-badge"><i class="fa-solid fa-crown"></i> Leadership</span>
              <span class="tag-badge"><i class="fa-solid fa-comments"></i> Effective Communication</span>
              <span class="tag-badge"><i class="fa-solid fa-bolt"></i> Fast &amp; Agile Learner</span>
            </div>
          </div>
        </div>

        <!-- Personal Details Card -->
        <div class="glass-card personal-details-card">
          <h3>Personal Information</h3>

          <div class="details-list">
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-signature"></i> Full Name</span>
              <span class="value">Minudi Shashina Galbokka Hewage</span>
            </div>
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-calendar"></i> Date of Birth</span>
              <span class="value">03rd March 2004</span>
            </div>
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-hourglass-half"></i> Age</span>
              <span class="value">22 Years Old</span>
            </div>
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-venus"></i> Gender</span>
              <span class="value">Female</span>
            </div>
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-ring"></i> Civil Status</span>
              <span class="value">Unmarried</span>
            </div>
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-flag"></i> Nationality</span>
              <span class="value">Sri Lankan</span>
            </div>
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-location-dot"></i> Hometown</span>
              <span class="value">Baddegama, Galle</span>
            </div>
            <div class="detail-row">
              <span class="label"><i class="fa-solid fa-language"></i> Languages</span>
              <span class="value">English, Sinhala, Tamil</span>
            </div>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- ===================================================================
       EDUCATION & QUALIFICATIONS SECTION
       =================================================================== -->
  <section id="education" class="section-padding" style="background:rgba(255,255,255,0.01);">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// ACADEMIC &amp; VOCATIONAL CREDENTIALS</span>
        <h2 class="section-title">Education &amp; Qualifications</h2>
        <p class="section-subtitle">
          Formal software engineering training, diplomas, and secondary academic achievements.
        </p>
      </div>

      <div class="education-cards-grid">
        
        <!-- Card 1: NVQ 5 ICT Diploma -->
        <div class="glass-card edu-card">
          <div class="edu-icon-box">
            <i class="fa-solid fa-graduation-cap"></i>
          </div>
          <div class="edu-content">
            <span class="edu-period">2026 - Present</span>
            <h3>Diploma in ICT - NVQ Level V</h3>
            <div class="edu-institution">National Vocational Training Institute (NVTI) - Baddegama</div>
            <p class="edu-desc">
              Currently completing the final stages of the ICT Technician Level 5 diploma, focusing on system analysis, advanced database administration, IT infrastructure management, and technical troubleshooting.
            </p>
          </div>
        </div>

        <!-- Card 2: NVQ 4 Software Developer -->
        <div class="glass-card edu-card">
          <div class="edu-icon-box">
            <i class="fa-solid fa-laptop-code"></i>
          </div>
          <div class="edu-content">
            <span class="edu-period">Completed 2025</span>
            <h3>Software Developer - NVQ Level IV</h3>
            <div class="edu-institution">National Vocational Training Institute (NVTI) - Baddegama</div>
            <p class="edu-desc">
              Comprehensive national qualification covering desktop application development, web development concepts, relational database programming (SQL), and software testing methodologies.
            </p>
          </div>
        </div>

        <!-- Card 3: English Diploma -->
        <div class="glass-card edu-card">
          <div class="edu-icon-box">
            <i class="fa-solid fa-award"></i>
          </div>
          <div class="edu-content">
            <span class="edu-period">Completed 2024</span>
            <h3>English Diploma Certificate Course</h3>
            <div class="edu-institution">Institute of Nations Professional Management (INPM)</div>
            <p class="edu-desc">
              Professional training in spoken and written English, presentation delivery, technical report generation, and business communication standards.
            </p>
          </div>
        </div>

        <!-- Card 4: GCE O/L -->
        <div class="glass-card edu-card">
          <div class="edu-icon-box">
            <i class="fa-solid fa-school"></i>
          </div>
          <div class="edu-content">
            <span class="edu-period">2020 (2021)</span>
            <h3>G.C.E (O/L) Exam (English Medium)</h3>
            <div class="edu-institution">Christ Church Girls' College - Baddegama (2010 - 2025)</div>
            <p class="edu-desc">
              Successfully passed the General Certificate of Education (Ordinary Level) examination in the English medium with strong analytical and language scores.
            </p>
            
            <div class="ol-grades-grid">
              <div class="ol-badge"><span class="sub">Buddhism</span><span class="grade">A</span></div>
              <div class="ol-badge"><span class="sub">English</span><span class="grade">A</span></div>
              <div class="ol-badge"><span class="sub">Sinhala</span><span class="grade">B</span></div>
              <div class="ol-badge"><span class="sub">Maths</span><span class="grade">B</span></div>
              <div class="ol-badge"><span class="sub">History</span><span class="grade">B</span></div>
              <div class="ol-badge"><span class="sub">Dancing</span><span class="grade">B</span></div>
              <div class="ol-badge"><span class="sub">ICT</span><span class="grade">C</span></div>
              <div class="ol-badge"><span class="sub">Tamil</span><span class="grade">C</span></div>
              <div class="ol-badge"><span class="sub">Science</span><span class="grade">C</span></div>
              <div class="ol-badge"><span class="sub">English Lit</span><span class="grade">S</span></div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- ===================================================================
       WORK EXPERIENCE SECTION
       =================================================================== -->
  <section id="experience" class="section-padding">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// CAREER HISTORY &amp; WORK EXPERIENCE</span>
        <h2 class="section-title">Professional Experience</h2>
        <p class="section-subtitle">
          Industry software internship, educational mentoring, and business development roles.
        </p>
      </div>

      <div class="timeline-container">
        
        <!-- Experience 1: Sakwa Canneries -->
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="glass-card timeline-card">
            <div class="timeline-meta">
              <div class="timeline-role">Software Developer &amp; IT Assistant</div>
              <div class="timeline-badge">FEB 2026 - AUG 2026</div>
            </div>
            <div class="timeline-company">
              <i class="fa-solid fa-building"></i> Sakwa Canneries &amp; Exports (Pvt) Ltd - Baddegama
            </div>
            <p class="timeline-details">
              Gained practical industry experience in enterprise computing. Assisted in developing internal software routines, managing database inventories, troubleshooting hardware and network issues, and providing end-user technical support for factory and administrative operations.
            </p>
            <div class="skill-tags" style="margin-top:12px;">
              <span class="tag-badge">Software Development</span>
              <span class="tag-badge">Database Management</span>
              <span class="tag-badge">IT Support</span>
              <span class="tag-badge">Enterprise Operations</span>
            </div>
          </div>
        </div>

        <!-- Experience 2: Dance Teacher -->
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="glass-card timeline-card">
            <div class="timeline-meta">
              <div class="timeline-role">Dance Teacher</div>
              <div class="timeline-badge">AUG 2023 - MAR 2025</div>
            </div>
            <div class="timeline-company">
              <i class="fa-solid fa-school"></i> G/Ganegama Roman Catholic Primary School
            </div>
            <p class="timeline-details">
              Taught traditional dance forms to primary school students, organized school cultural festivals, coordinated group choreography, and fostered teamwork and stage confidence among young learners.
            </p>
          </div>
        </div>

        <!-- Experience 3: Business Development Associate -->
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="glass-card timeline-card">
            <div class="timeline-meta">
              <div class="timeline-role">Business Development Associate</div>
              <div class="timeline-badge">JUL 2021 - JUL 2022</div>
            </div>
            <div class="timeline-company">
              <i class="fa-solid fa-chart-line"></i> Chords by Semi Circle
            </div>
            <p class="timeline-details">
              Facilitated business outreach, managed client relationships, assisted in promotional initiatives, and coordinated schedule management for team deliveries.
            </p>
          </div>
        </div>

        <!-- Experience 4: English Teacher -->
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="glass-card timeline-card">
            <div class="timeline-meta">
              <div class="timeline-role">English Teacher</div>
              <div class="timeline-badge">DEC 2020 - DEC 2021</div>
            </div>
            <div class="timeline-company">
              <i class="fa-solid fa-chalkboard-user"></i> Edexer Online Institute
            </div>
            <p class="timeline-details">
              Conducted interactive online English language sessions for registered students, developed lesson worksheets, evaluated grammar assignments, and strengthened students' verbal expression.
            </p>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- ===================================================================
       SKILLS MATRIX SECTION
       =================================================================== -->
  <section id="skills" class="section-padding" style="background:rgba(255,255,255,0.01);">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// TECHNICAL ARSENAL</span>
        <h2 class="section-title">Core Capabilities &amp; Skill Matrix</h2>
        <p class="section-subtitle">
          A structured overview of software programming languages, database systems, web stacks, and interpersonal strengths.
        </p>
      </div>

      <div class="skills-matrix-grid">
        
        <!-- Category 1: Backend & Database -->
        <div class="glass-card skill-category-card">
          <h3><i class="fa-solid fa-database"></i> Backend &amp; DB</h3>
          <div class="skill-items">
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>PHP</span>
                <span>88%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:88%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>MySQL / MariaDB</span>
                <span>85%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:85%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Java (Core &amp; Servlets)</span>
                <span>80%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:80%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>System Architecture</span>
                <span>78%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:78%;"></div></div>
            </div>
          </div>
        </div>

        <!-- Category 2: Frontend & Web UI -->
        <div class="glass-card skill-category-card">
          <h3><i class="fa-solid fa-code"></i> Frontend &amp; Web</h3>
          <div class="skill-items">
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>HTML5 / Semantic Web</span>
                <span>92%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:92%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>CSS3 &amp; Glassmorphism</span>
                <span>90%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:90%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>JavaScript (ES6+)</span>
                <span>82%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:82%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Responsive Design</span>
                <span>90%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:90%;"></div></div>
            </div>
          </div>
        </div>

        <!-- Category 3: Application Types & Tools -->
        <div class="glass-card skill-category-card">
          <h3><i class="fa-solid fa-desktop"></i> Applications &amp; Tools</h3>
          <div class="skill-items">
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Web Applications</span>
                <span>88%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:88%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Desktop Applications</span>
                <span>75%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:75%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Mobile App Concepts</span>
                <span>70%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:70%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>XAMPP &amp; Apache</span>
                <span>85%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:85%;"></div></div>
            </div>
          </div>
        </div>

        <!-- Category 4: Professional & Soft Skills -->
        <div class="glass-card skill-category-card">
          <h3><i class="fa-solid fa-handshake"></i> Soft Skills</h3>
          <div class="skill-items">
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Communication</span>
                <span>95%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:95%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Teamwork</span>
                <span>92%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:92%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Time Management</span>
                <span>90%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:90%;"></div></div>
            </div>
            <div class="skill-item-row">
              <div class="skill-item-info">
                <span>Project Leadership</span>
                <span>85%</span>
              </div>
              <div class="skill-bar-track"><div class="skill-bar-fill" style="width:85%;"></div></div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- ===================================================================
       FEATURED PROJECTS SECTION (DYNAMIC MULTI-IMAGE CARDS)
       =================================================================== -->
  <section id="projects" class="section-padding">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// FEATURED SOFTWARE PROJECTS</span>
        <h2 class="section-title">Featured Applications &amp; Work</h2>
        <p class="section-subtitle">
          Explore real software solutions with UI mockups, architectural details, and database schema.
        </p>
      </div>

      <!-- Filter Buttons -->
      <div class="projects-filters">
        <button class="filter-btn active" data-filter="all">All Projects (<?= count($projectsList) ?>)</button>
        <button class="filter-btn" data-filter="web">Web Applications</button>
        <button class="filter-btn" data-filter="management">Management Systems</button>
        <button class="filter-btn" data-filter="enterprise">Enterprise Systems</button>
      </div>

      <!-- Projects Grid -->
      <div class="projects-grid">
        <?php foreach ($projectsList as $proj): ?>
          <?php
            $cat = $proj['category'] ?? 'web';
            $firstImg = !empty($proj['images'][0]) ? htmlspecialchars($proj['images'][0]) : 'images/profile.jpg';
            $imgCount = count($proj['images'] ?? []);
          ?>
          <div class="glass-card project-card" data-category="<?= htmlspecialchars($cat) ?>">
            
            <!-- Top UI Thumbnail Banner -->
            <div class="project-card-thumb-wrap">
              <img src="<?= $firstImg ?>" alt="<?= htmlspecialchars($proj['title']) ?> UI" class="project-card-thumb" onerror="this.src='images/profile.jpg'">
              <div class="project-img-count-badge">
                <i class="fa-solid fa-camera"></i> <?= $imgCount ?> UI Screenshots
              </div>
            </div>

            <!-- Card Body -->
            <div class="project-card-body">
              <div>
                <div class="project-header-box" style="margin-bottom:12px;">
                  <span class="project-category-tag"><?= htmlspecialchars($proj['category_name'] ?? ucfirst($cat)) ?></span>
                </div>
                <h3><?= htmlspecialchars($proj['title']) ?></h3>
                <p><?= htmlspecialchars($proj['short_desc']) ?></p>
                
                <div class="project-tech-stack">
                  <?php foreach ($proj['tech_stack'] ?? [] as $tech): ?>
                    <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="project-card-footer">
                <button class="btn btn-outline view-project-details" data-project-id="<?= htmlspecialchars($proj['id']) ?>">
                  <i class="fa-solid fa-images"></i> View UI &amp; Details
                </button>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- ===================================================================
       PROJECT MODAL WITH MULTI-IMAGE SLIDER / CAROUSEL
       =================================================================== -->
  <div id="project-modal" class="modal-overlay" onclick="closeProjectModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
      
      <!-- Top Fixed Header Bar -->
      <div class="modal-header-bar">
        <div class="modal-header-titles">
          <span id="modal-category" class="project-category-tag">Category</span>
          <h3 id="modal-title" class="modal-title-text">Project Title</h3>
        </div>
        <button id="modal-close" class="modal-close-btn" title="Close Preview (Esc)" onclick="closeProjectModal()" aria-label="Close modal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Scrollable Content Body -->
      <div class="modal-body-scrollable">
        <!-- Multi-Image Slider Viewport -->
        <div class="modal-gallery-container">
          <div class="gallery-slider-viewport">
            <button class="gallery-nav-btn gallery-prev-btn" id="gallery-prev" title="Previous Image" type="button">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            
            <img id="gallery-current-img" src="" alt="Screenshot" class="gallery-slide-img">

            <button class="gallery-nav-btn gallery-next-btn" id="gallery-next" title="Next Image" type="button">
              <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div id="gallery-counter" class="gallery-counter-badge">1 / 1</div>
          </div>

          <!-- Thumbnails Strip -->
          <div id="gallery-thumbs" class="gallery-thumbs-strip"></div>
        </div>

        <!-- Description & Tech Stack -->
        <div class="modal-section-block">
          <h4 class="modal-section-heading"><i class="fa-solid fa-circle-info"></i> Project Overview</h4>
          <p id="modal-desc" class="modal-desc-text">Detailed project description here.</p>
        </div>
        
        <div class="modal-section-block">
          <h4 class="modal-section-heading"><i class="fa-solid fa-layer-group"></i> Technologies &amp; Architecture</h4>
          <div id="modal-stack" class="modal-tags-container"></div>
        </div>
      </div>

      <!-- Bottom Fixed Footer Bar -->
      <div class="modal-footer-bar">
        <span class="modal-esc-hint"><i class="fa-solid fa-keyboard"></i> Press <kbd>ESC</kbd> to exit</span>
        <button id="modal-bottom-close" type="button" onclick="closeProjectModal()" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-xmark"></i> Close Preview
        </button>
      </div>

    </div>
  </div>

  <!-- Embed Projects JSON for JS modal -->
  <script>
    window.PROJECTS_DATA = <?= json_encode($projectsList) ?>;
  </script>

  <!-- ===================================================================
       INTERACTIVE DEVELOPER CLI TERMINAL (Sahashra Style)
       =================================================================== -->
  <section id="terminal" class="section-padding" style="background:rgba(255,255,255,0.01);">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// INTERACTIVE CLI INTERFACE</span>
        <h2 class="section-title">Minudi's Developer Terminal</h2>
        <p class="section-subtitle">
          Type terminal commands or click the interactive quick-action shortcuts below to inspect credentials, skills, and direct channels.
        </p>
      </div>

      <div class="terminal-window">
        <!-- Terminal Header -->
        <div class="terminal-header">
          <div class="terminal-dots">
            <div class="terminal-dot dot-red"></div>
            <div class="terminal-dot dot-yellow"></div>
            <div class="terminal-dot dot-green"></div>
          </div>
          <div class="terminal-title">minudi@nexus-dev-terminal: ~ (zsh)</div>
          <div class="terminal-version">PROD v2.4</div>
        </div>

        <!-- Terminal Body -->
        <div id="terminal-output" class="terminal-body">
          <div class="term-welcome">
            <div>╔═══════════════════════════════════════════════════════════════════╗</div>
            <div>║ &nbsp;Welcome to MINUDI SHASHINA Developer Terminal v2.4 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;║</div>
            <div>║ &nbsp;Type '<span class="term-command">help</span>' to inspect commands, or click quick buttons below.&nbsp; ║</div>
            <div>╚═══════════════════════════════════════════════════════════════════╝</div>
          </div>
        </div>

        <!-- Terminal Input Prompt -->
        <div class="terminal-actions-bar" style="border-top:1px solid var(--border-glass); padding:8px 18px;">
          <div class="terminal-input-row" style="width:100%;">
            <span class="terminal-prompt">guest@minudishashina : ~$</span>
            <input type="text" id="terminal-input" class="terminal-input" placeholder="Type a command (e.g. 'help', 'skills', 'about')..." autocomplete="off" />
          </div>
        </div>

        <!-- Quick Shortcut Action Buttons -->
        <div class="terminal-actions-bar">
          <span style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-right:4px;">Quick Actions:</span>
          <button class="terminal-btn" data-cmd="help">help</button>
          <button class="terminal-btn" data-cmd="about">about</button>
          <button class="terminal-btn" data-cmd="skills">skills</button>
          <button class="terminal-btn" data-cmd="education">education</button>
          <button class="terminal-btn" data-cmd="experience">experience</button>
          <button class="terminal-btn" data-cmd="projects">projects</button>
          <button class="terminal-btn" data-cmd="reference">reference</button>
          <button class="terminal-btn" data-cmd="contact">contact</button>
          <button class="terminal-btn" data-cmd="clear" style="color:#ef4444;">clear</button>
        </div>
      </div>

    </div>
  </section>

  <!-- ===================================================================
       OFFICIAL RECOMMENDATION REFERENCE SECTION
       =================================================================== -->
  <section class="section-padding">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// ACADEMIC &amp; VOCATIONAL RECOMMENDATION</span>
        <h2 class="section-title">Professional Reference</h2>
        <p class="section-subtitle">
          Verified academic and professional reference from the National Vocational Training Institute (NVTI).
        </p>
      </div>

      <div class="reference-card-wrapper">
        <div class="glass-card reference-card">
          <div class="ref-avatar">
            <i class="fa-solid fa-user-tie"></i>
          </div>
          <div class="ref-info">
            <h3>E. A. R. Lakshman</h3>
            <div class="ref-role">Software Developer Instructor</div>
            <div class="ref-org">National Vocational Training Institute (NVTI) - Baddegama</div>
            <a href="tel:+94718249323" class="ref-phone">
              <i class="fa-solid fa-phone"></i> +94 71 824 9323
            </a>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ===================================================================
       CONTACT SECTION
       =================================================================== -->
  <section id="contact" class="section-padding" style="background:rgba(255,255,255,0.01);">
    <div class="container">
      
      <div class="section-header">
        <span class="section-tag">// DIRECT CHANNEL</span>
        <h2 class="section-title">Let's Connect &amp; Collaborate</h2>
        <p class="section-subtitle">
          Have an inquiry about software projects, full-time openings, or collaboration? Reach out directly or send an instant message.
        </p>
      </div>

      <div class="contact-grid">
        
        <!-- Direct Contact Info Cards -->
        <div class="contact-channels">
          
          <div class="glass-card contact-channel-card">
            <div class="channel-icon">
              <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="channel-info">
              <h4>Direct Email</h4>
              <a href="mailto:minudi418@gmail.com">minudi418@gmail.com</a>
            </div>
          </div>

          <div class="glass-card contact-channel-card">
            <div class="channel-icon">
              <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div class="channel-info">
              <h4>Phone &amp; WhatsApp</h4>
              <a href="https://wa.me/94779945890" target="_blank">+94 77 994 5890</a>
            </div>
          </div>

          <div class="glass-card contact-channel-card">
            <div class="channel-icon">
              <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="channel-info">
              <h4>Residential Location</h4>
              <p>"Bhanu Tailors", Lawalhena Junction, Wanduramba Road, Baddegama, Galle</p>
            </div>
          </div>

          <div class="glass-card contact-channel-card">
            <div class="channel-icon">
              <i class="fa-solid fa-briefcase"></i>
            </div>
            <div class="channel-info">
              <h4>Availability Status</h4>
              <p style="color:var(--accent-emerald);">Open for Full-time Roles &amp; Technical Projects</p>
            </div>
          </div>

        </div>

        <!-- AJAX Contact Form -->
        <div class="glass-card">
          <form id="contact-form" class="contact-form" action="php/contact.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
            
            <div class="form-group">
              <label for="name"><i class="fa-solid fa-user"></i> Your Full Name</label>
              <input type="text" id="name" name="name" class="form-input" placeholder="e.g. John Perera" required />
            </div>

            <div class="form-group">
              <label for="email"><i class="fa-solid fa-at"></i> Your Email Address</label>
              <input type="email" id="email" name="email" class="form-input" placeholder="e.g. john@example.com" required />
            </div>

            <div class="form-group">
              <label for="subject"><i class="fa-solid fa-tag"></i> Subject</label>
              <input type="text" id="subject" name="subject" class="form-input" placeholder="e.g. Project Inquiry / Job Opportunity" />
            </div>

            <div class="form-group">
              <label for="message"><i class="fa-solid fa-message"></i> Your Message</label>
              <textarea id="message" name="message" class="form-textarea" placeholder="Write your message or inquiry here..." required></textarea>
            </div>

            <button type="submit" id="form-submit-btn" class="btn btn-primary" style="padding:15px; width:100%;">
              <i class="fa-solid fa-paper-plane"></i> Send Direct Message
            </button>
          </form>
        </div>

      </div>

    </div>
  </section>

  <!-- ===================================================================
       FOOTER
       =================================================================== -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-content">
        
        <div class="brand-logo">
          <div class="logo-icon"><i class="fa-solid fa-code"></i></div>
          <span class="logo-text-full">MINUDI<span>.DEV</span></span>
        </div>

        <nav>
          <ul class="nav-menu" style="display:flex; gap:20px;">
            <li><a href="#hero" class="nav-link">Home</a></li>
            <li><a href="#about" class="nav-link">About</a></li>
            <li><a href="#education" class="nav-link">Education</a></li>
            <li><a href="#experience" class="nav-link">Experience</a></li>
            <li><a href="#skills" class="nav-link">Skills</a></li>
            <li><a href="#projects" class="nav-link">Projects</a></li>
            <li><a href="#terminal" class="nav-link">Terminal</a></li>
            <li><a href="#contact" class="nav-link">Contact</a></li>
          </ul>
        </nav>

        <div style="display:flex; gap:12px;">
          <a href="mailto:minudi418@gmail.com" class="logo-icon" style="text-decoration:none;" title="Email"><i class="fa-solid fa-envelope"></i></a>
          <a href="https://wa.me/94779945890" target="_blank" class="logo-icon" style="text-decoration:none;" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
          <a href="admin/login.php" class="logo-icon" style="text-decoration:none;" title="Admin Control Center"><i class="fa-solid fa-gear"></i></a>
        </div>

      </div>

      <div class="footer-copy">
        <p>© 2026 Minudi Shashina Galbokka Hewage. All Rights Reserved. Built with precision &amp; passion.</p>
      </div>
    </div>
  </footer>

  <!-- Main JavaScript -->
  <script src="main.js"></script>
</body>
</html>
