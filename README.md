# Minudi Shashina - Software Engineer & Full-Stack Developer Portfolio

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![CSS3 Glassmorphism](https://img.shields.io/badge/Design-Cyber--Amethyst%20Glassmorphism-a970ff?style=for-the-badge)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Security](https://img.shields.io/badge/OWASP-Hardened%20Security-22c55e?style=for-the-badge&logo=securityscorecard&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)

A high-performance, responsive software developer portfolio and back-office CMS engineered for **Minudi Shashina** (NVQ 4 Software Developer & NVQ 5 ICT Candidate). Features a **Cyber-Amethyst Dark Glassmorphism** theme, multi-image interactive project modals, an interactive developer CLI terminal, real-time visitor analytics, and comprehensive enterprise-grade security hardening.

---

## 🌟 Key Highlights & Features

### 1. 🎨 Cyber-Amethyst Glassmorphic UI/UX
- Premium dark-mode cyber aesthetic with violet (`#a970ff`) and electric cyan (`#00f2fe`) neon gradients.
- Ultra-smooth CSS3 backdrop filters, subtle glowing borders, and fluid hover micro-interactions.
- 100% mobile responsive across all modern smartphones, tablets, laptops, and ultra-wide desktop displays.

### 2. 🖼️ Multi-Image Project Showcase & Modal Carousel
- Showcases real software engineering projects with multi-screenshot galleries:
  - **BloomyHaven:** Floral E-Commerce & Gift Boutique Web Application
  - **BMC Machine Center:** Agricultural Machinery Inventory & Repair Management System
  - **Gourmet POS:** Touchscreen Restaurant & Cafe Point of Sale System
  - **NexaSphere University:** Higher Education Student Information System (SIS)
  - **VogueVibe Fashion:** High-End Apparel E-Commerce Store
- Full-screen protected modal carousel with sticky header, independent smooth scroll body, responsive thumbnails strip, keyboard navigation (`ArrowLeft`, `ArrowRight`, `Esc`), and backdrop-click dismissal.

### 3. 💻 Interactive Developer Terminal (CLI)
- Interactive Sahashra-style in-browser terminal emulator.
- Supports commands: `help`, `about`, `skills`, `projects`, `contact`, `clear`, `whoami`, `version`, `hire`.
- Quick-action clickable shortcut tags for rapid inspection.

### 4. 📊 Back-Office Admin Dashboard & Analytics (`/admin`)
- **Real-Time Analytics:** Tracks unique visitor counts, total page views, visitor devices (Desktop, Mobile, Tablet), operating systems, and top browsers.
- **Project Management (CRUD):** Add, update, and delete projects with multi-image screenshot upload.
- **Security Audit Logs:** Tracks admin sign-ins, project modifications, and critical system events.

### 5. 🛡️ 10-Point Enterprise Security Hardening
- **Authentication & Passwords:** Bcrypt (`password_hash` with `PASSWORD_BCRYPT`, cost 12), zero MD5 usage.
- **Brute-Force Rate Limiting:** Automatic lockout after 5 failed login attempts for 15 minutes.
- **Session Hardening:** `HttpOnly`, `SameSite=Strict`, session ID regeneration upon privilege escalation, and session destruction on logout.
- **CSRF Defense:** Cryptographic anti-CSRF token verification on all POST forms and administrative actions.
- **XSS Sanitization:** Strict `htmlspecialchars()` escaping across all dynamic DOM rendering.
- **Folder & File Upload Armor:** Protected `.htaccess` rules in `data/` and `uploads/` directories blocking all direct execution of `.php`, `.phtml`, and script files.
- **HTTP Security Headers:** `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, `Referrer-Policy: strict-origin-when-cross-origin`.

---

## 📂 Project Structure

```text
minudi-shashina-portfolio/
├── .gitignore               # Ignored dynamic logs and environment caches
├── .htaccess                # Apache security headers, error routing, and rewrite rules
├── index.php                # Main portfolio application
├── styles.css               # Cyber-Amethyst theme and responsive media queries
├── main.js                  # Frontend interactive scripts, CLI terminal, and modal gallery
├── README.md                # Project documentation
├── admin/
│   ├── index.php            # Administrative control center & project management
│   ├── login.php            # Secure admin authentication with rate-limiting
│   └── logout.php           # Session teardown
├── data/
│   ├── .htaccess            # Strict file-access restriction for data files
│   └── projects.json        # Structured project database
├── images/
│   └── profile.jpg          # Profile portraits & branding assets
├── php/
│   ├── contact.php          # Contact inquiry form processor & CSRF validator
│   ├── security.php         # Central security functions, session manager & rate limiter
│   └── tracker.php          # Real-time visitor analytics counter
└── uploads/
    ├── .htaccess            # Upload folder armor (disables PHP script execution)
    └── projects/            # Real project UI screenshots
```

---

## 🚀 Installation & Local Setup

### Requirements
- PHP 8.0 or higher
- Apache Web Server (e.g. XAMPP, WampServer, or LAMP)
- `mod_rewrite` and `mod_headers` enabled in Apache

### Steps
1. Clone the repository into your local server web root:
   ```bash
   cd c:/xampp/htdocs/
   git clone https://github.com/janithjanithdamsara-coder/minudi-shashina-portfolio.git "minudi akka"
   ```

2. Start **Apache** in your XAMPP Control Panel.

3. Open your browser and navigate to:
   ```text
   http://localhost/minudi%20akka/index.php
   ```

4. Access the Admin Control Panel:
   ```text
   http://localhost/minudi%20akka/admin/
   ```
   - **Default Username:** `admin`
   - **Default Password:** `admin123` *(Remember to update credentials in production)*

---

## 👩‍💻 About Minudi Shashina
- **Diploma in ICT (NVQ Level V):** National Vocational Training Institute (NVTI) - Baddegama
- **Software Developer (NVQ Level IV):** National Vocational Training Institute (NVTI) - Baddegama
- **English Diploma:** Institute of Nations Professional Management (INPM)
- **Industrial Experience:** Sakwa Canneries (Pvt) Ltd (Database administration & inventory workflows)

---

## 📄 License
This project is open-source and available under the [MIT License](LICENSE).
