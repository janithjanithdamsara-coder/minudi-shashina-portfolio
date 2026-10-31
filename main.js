/**
 * ===================================================================
 * MINUDI SHASHINA - PORTFOLIO INTERACTIVE JAVASCRIPT
 * Features: Three.js 3D Particles, Typing Effect, Terminal,
 * Custom Cursor, Multi-Image Project Gallery Slider, AJAX Contact Form
 * ===================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

  // 1. Initialize Custom Cursor
  initCustomCursor();

  // 2. Initialize Three.js 3D Ambient Particles Canvas
  initThreeJsCanvas();

  // 3. Initialize Dynamic Typing Effect
  initTypingEffect();

  // 4. Initialize Navigation & Mobile Drawer
  initNavigation();

  // 5. Initialize Project Filters & Multi-Image Modal Gallery
  initProjectsSystem();

  // 6. Initialize Interactive CLI Terminal
  initDeveloperTerminal();

  // 7. Initialize AJAX Contact Form & Toast Notifications
  initContactForm();

  // 8. Initialize Scroll-to-Top Widget
  initScrollTop();

});


/* ===================================================================
   1. CUSTOM CURSOR
   =================================================================== */
function initCustomCursor() {
  const cursor = document.getElementById('custom-cursor');
  const follower = document.getElementById('custom-cursor-follower');

  if (!cursor || !follower || window.innerWidth < 992) return;

  let mouseX = 0, mouseY = 0;
  let followerX = 0, followerY = 0;

  window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;

    cursor.style.left = `${mouseX}px`;
    cursor.style.top = `${mouseY}px`;
  });

  function renderFollower() {
    followerX += (mouseX - followerX) * 0.18;
    followerY += (mouseY - followerY) * 0.18;

    follower.style.left = `${followerX}px`;
    follower.style.top = `${followerY}px`;

    requestAnimationFrame(renderFollower);
  }
  renderFollower();

  const clickables = document.querySelectorAll('a, button, input, textarea, .glass-card, .filter-btn, .terminal-btn');
  clickables.forEach(el => {
    el.addEventListener('mouseenter', () => {
      follower.style.width = '50px';
      follower.style.height = '50px';
      follower.style.borderColor = 'var(--accent-cyan)';
    });
    el.addEventListener('mouseleave', () => {
      follower.style.width = '34px';
      follower.style.height = '34px';
      follower.style.borderColor = 'var(--accent-purple)';
    });
  });
}


/* ===================================================================
   2. THREE.JS 3D AMBIENT PARTICLES
   =================================================================== */
function initThreeJsCanvas() {
  const container = document.getElementById('canvas-container');
  if (!container || typeof THREE === 'undefined') return;

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 1, 1000);
  camera.position.z = 400;

  const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  container.appendChild(renderer.domElement);

  const particleCount = 750;
  const geometry = new THREE.BufferGeometry();
  const positions = new Float32Array(particleCount * 3);
  const colors = new Float32Array(particleCount * 3);

  const purple = new THREE.Color(0xa970ff);
  const cyan = new THREE.Color(0x00f2fe);
  const darkIndigo = new THREE.Color(0x8b5cf6);

  for (let i = 0; i < particleCount * 3; i += 3) {
    positions[i] = (Math.random() - 0.5) * 1000;
    positions[i + 1] = (Math.random() - 0.5) * 1000;
    positions[i + 2] = (Math.random() - 0.5) * 800;

    const mixedColor = Math.random() > 0.5 ? purple : (Math.random() > 0.5 ? cyan : darkIndigo);
    colors[i] = mixedColor.r;
    colors[i + 1] = mixedColor.g;
    colors[i + 2] = mixedColor.b;
  }

  geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

  const material = new THREE.PointsMaterial({
    size: 3.5,
    vertexColors: true,
    transparent: true,
    opacity: 0.75,
    blending: THREE.AdditiveBlending
  });

  const particleSystem = new THREE.Points(geometry, material);
  scene.add(particleSystem);

  let mouseX = 0, mouseY = 0;
  window.addEventListener('mousemove', (e) => {
    mouseX = (e.clientX - window.innerWidth / 2) * 0.05;
    mouseY = (e.clientY - window.innerHeight / 2) * 0.05;
  });

  function animate() {
    requestAnimationFrame(animate);

    particleSystem.rotation.x += 0.0006;
    particleSystem.rotation.y += 0.0008;

    camera.position.x += (mouseX - camera.position.x) * 0.04;
    camera.position.y += (-mouseY - camera.position.y) * 0.04;
    camera.lookAt(scene.position);

    renderer.render(scene, camera);
  }
  animate();

  window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });
}


/* ===================================================================
   3. DYNAMIC TYPING EFFECT
   =================================================================== */
function initTypingEffect() {
  const typingElement = document.querySelector('.typing-text');
  if (!typingElement) return;

  const roles = [
    'Software Developer',
    'Diploma in ICT (NVQ 5)',
    'Full-Stack Web & Systems',
    'IT Assistant'
  ];

  let roleIndex = 0;
  let charIndex = 0;
  let isDeleting = false;
  let typingDelay = 120;

  function type() {
    const currentRole = roles[roleIndex];

    if (isDeleting) {
      typingElement.textContent = currentRole.substring(0, charIndex - 1);
      charIndex--;
      typingDelay = 60;
    } else {
      typingElement.textContent = currentRole.substring(0, charIndex + 1);
      charIndex++;
      typingDelay = 120;
    }

    if (!isDeleting && charIndex === currentRole.length) {
      isDeleting = true;
      typingDelay = 2000;
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      roleIndex = (roleIndex + 1) % roles.length;
      typingDelay = 500;
    }

    setTimeout(type, typingDelay);
  }

  type();
}


/* ===================================================================
   4. NAVIGATION & MOBILE DRAWER
   =================================================================== */
function initNavigation() {
  const mobileToggle = document.getElementById('mobile-menu-toggle');
  const drawer = document.getElementById('mobile-nav-drawer');
  const overlay = document.getElementById('mobile-drawer-overlay');
  const closeBtn = document.getElementById('mobile-drawer-close');
  const navLinks = document.querySelectorAll('.nav-link, .mobile-nav-links a');

  function openDrawer() {
    drawer.classList.add('open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  if (mobileToggle) mobileToggle.addEventListener('click', openDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if (overlay) overlay.addEventListener('click', closeDrawer);

  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      closeDrawer();
    });
  });

  const sections = document.querySelectorAll('section[id]');
  window.addEventListener('scroll', () => {
    const scrollY = window.pageYOffset + 120;

    sections.forEach(sec => {
      const sectionHeight = sec.offsetHeight;
      const sectionTop = sec.offsetTop;
      const sectionId = sec.getAttribute('id');

      if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
        document.querySelectorAll(`.nav-link[href="#${sectionId}"]`).forEach(l => l.classList.add('active'));
      } else {
        document.querySelectorAll(`.nav-link[href="#${sectionId}"]`).forEach(l => l.classList.remove('active'));
      }
    });
  });
}


/* ===================================================================
   5. PROJECT FILTERS & MULTI-IMAGE MODAL GALLERY
   =================================================================== */
function initProjectsSystem() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  const projectCards = document.querySelectorAll('.project-card');

  // Filter Handling
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const filter = btn.getAttribute('data-filter');

      projectCards.forEach(card => {
        if (filter === 'all' || card.getAttribute('data-category') === filter) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // Modal Gallery System
  const modalOverlay = document.getElementById('project-modal');
  const modalTitle = document.getElementById('modal-title');
  const modalCategory = document.getElementById('modal-category');
  const modalDesc = document.getElementById('modal-desc');
  const modalStack = document.getElementById('modal-stack');
  const modalClose = document.getElementById('modal-close');
  const modalBottomClose = document.getElementById('modal-bottom-close');

  const galleryCurrentImg = document.getElementById('gallery-current-img');
  const galleryCounter = document.getElementById('gallery-counter');
  const galleryThumbs = document.getElementById('gallery-thumbs');
  const prevBtn = document.getElementById('gallery-prev');
  const nextBtn = document.getElementById('gallery-next');

  let currentProjectImages = [];
  let currentSlideIndex = 0;

  // Global Close function with scroll restoration
  function closeProjectModal() {
    if (modalOverlay) {
      modalOverlay.classList.remove('open');
    }
    document.body.style.overflow = '';
  }
  window.closeProjectModal = closeProjectModal;

  function updateGallerySlide(index) {
    if (!currentProjectImages.length || !galleryCurrentImg) return;
    currentSlideIndex = (index + currentProjectImages.length) % currentProjectImages.length;

    galleryCurrentImg.style.opacity = '0';
    setTimeout(() => {
      galleryCurrentImg.src = currentProjectImages[currentSlideIndex];
      galleryCurrentImg.style.opacity = '1';
    }, 120);

    if (galleryCounter) {
      galleryCounter.textContent = `${currentSlideIndex + 1} / ${currentProjectImages.length}`;
    }

    if (galleryThumbs) {
      const thumbItems = galleryThumbs.querySelectorAll('.gallery-thumb-item');
      thumbItems.forEach((t, i) => {
        if (i === currentSlideIndex) {
          t.classList.add('active');
        } else {
          t.classList.remove('active');
        }
      });
    }
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      updateGallerySlide(currentSlideIndex - 1);
    });
  }
  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      updateGallerySlide(currentSlideIndex + 1);
    });
  }

  // Keyboard navigation
  window.addEventListener('keydown', (e) => {
    if (modalOverlay && modalOverlay.classList.contains('open')) {
      if (e.key === 'ArrowLeft') updateGallerySlide(currentSlideIndex - 1);
      if (e.key === 'ArrowRight') updateGallerySlide(currentSlideIndex + 1);
      if (e.key === 'Escape' || e.keyCode === 27) closeProjectModal();
    }
  });

  // Open Project Details by Project ID
  function openProjectModalById(pId) {
    const projectsData = window.PROJECTS_DATA || [];
    const project = projectsData.find(p => p.id === pId);

    if (project && modalOverlay) {
      if (modalTitle) modalTitle.textContent = project.title;
      if (modalCategory) modalCategory.textContent = project.category_name || project.category;
      if (modalDesc) modalDesc.textContent = project.full_desc || project.short_desc;

      // Render Tech Tags
      if (modalStack) {
        modalStack.innerHTML = '';
        (project.tech_stack || []).forEach(tech => {
          const span = document.createElement('span');
          span.className = 'tech-tag';
          span.textContent = tech;
          modalStack.appendChild(span);
        });
      }

      // Initialize Images Gallery
      currentProjectImages = project.images && project.images.length ? project.images : ['images/profile.jpg'];
      currentSlideIndex = 0;

      // Populate Thumbnails Strip
      if (galleryThumbs) {
        galleryThumbs.innerHTML = '';
        currentProjectImages.forEach((imgSrc, i) => {
          const thumb = document.createElement('div');
          thumb.className = `gallery-thumb-item ${i === 0 ? 'active' : ''}`;
          thumb.innerHTML = `<img src="${imgSrc}" alt="UI Thumb" onerror="this.src='images/profile.jpg'">`;
          thumb.addEventListener('click', (e) => {
            e.stopPropagation();
            updateGallerySlide(i);
          });
          galleryThumbs.appendChild(thumb);
        });
      }

      // Show/Hide navigation arrows based on count
      if (currentProjectImages.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (galleryCounter) galleryCounter.style.display = 'none';
      } else {
        if (prevBtn) prevBtn.style.display = 'flex';
        if (nextBtn) nextBtn.style.display = 'flex';
        if (galleryCounter) galleryCounter.style.display = 'block';
      }

      updateGallerySlide(0);
      modalOverlay.classList.add('open');
      document.body.style.overflow = 'hidden'; // Lock background scroll
    }
  }
  window.openProjectModalById = openProjectModalById;

  // Bind view buttons
  const detailButtons = document.querySelectorAll('.view-project-details');
  detailButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const pId = btn.getAttribute('data-project-id');
      openProjectModalById(pId);
    });
  });

  // Also bind clicking on project card thumbnails
  document.querySelectorAll('.project-card-thumb-wrap').forEach(wrap => {
    wrap.style.cursor = 'pointer';
    wrap.addEventListener('click', (e) => {
      const card = wrap.closest('.project-card');
      const btn = card ? card.querySelector('.view-project-details') : null;
      if (btn) {
        const pId = btn.getAttribute('data-project-id');
        openProjectModalById(pId);
      }
    });
  });

  if (modalClose) {
    modalClose.addEventListener('click', (e) => {
      e.stopPropagation();
      closeProjectModal();
    });
  }

  if (modalBottomClose) {
    modalBottomClose.addEventListener('click', (e) => {
      e.stopPropagation();
      closeProjectModal();
    });
  }

  if (modalOverlay) {
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) closeProjectModal();
    });
  }
}


/* ===================================================================
   6. INTERACTIVE DEVELOPER CLI TERMINAL (Sahashra Style)
   =================================================================== */
function initDeveloperTerminal() {
  const terminalInput = document.getElementById('terminal-input');
  const terminalOutput = document.getElementById('terminal-output');
  const actionButtons = document.querySelectorAll('.terminal-btn');

  if (!terminalInput || !terminalOutput) return;

  const commands = {
    'help': `
      <div class="term-res-title">Available Terminal Commands:</div>
      <div>• <span class="term-command">about</span>       : View Minudi's background and bio</div>
      <div>• <span class="term-command">skills</span>      : Output technical skills and capabilities</div>
      <div>• <span class="term-command">education</span>   : View NVTI and school academic records</div>
      <div>• <span class="term-command">experience</span>  : Show career history and internships</div>
      <div>• <span class="term-command">projects</span>    : List featured live software projects</div>
      <div>• <span class="term-command">reference</span>   : Show official instructor reference</div>
      <div>• <span class="term-command">contact</span>     : Display direct contact channels</div>
      <div>• <span class="term-command">clear</span>       : Clear the terminal screen</div>
    `,
    'about': `
      <div class="term-res-title">// ABOUT MINUDI SHASHINA</div>
      <div>Full Name   : Minudi Shashina Galbokka Hewage</div>
      <div>Title       : Software Developer & IT Assistant</div>
      <div>Location    : Baddegama, Galle, Sri Lanka</div>
      <div>Bio         : NVQ Level 4 Software Developer & NVQ Level 5 ICT Diploma candidate at NVTI Baddegama. Experienced in enterprise software & IT support at Sakwa Canneries & Exports (Pvt) Ltd.</div>
    `,
    'skills': `
      <div class="term-res-title">// TECHNICAL STACK MATRIX</div>
      <div>• Backend   : PHP, MySQL / MariaDB, Java (J2EE Servlets), Apache XAMPP</div>
      <div>• Frontend  : HTML5, CSS3 (Modern Glassmorphism), JavaScript (ES6+), AJAX</div>
      <div>• Systems   : Web Applications, Desktop Apps, Mobile Apps, IT Support</div>
      <div>• Soft Skills: Effective Communication, Teamwork, Leadership, Time Management</div>
    `,
    'education': `
      <div class="term-res-title">// ACADEMIC & VOCATIONAL CREDENTIALS</div>
      <div>1. Diploma in ICT - NVQ Level V (2026 - Present) | NVTI Baddegama</div>
      <div>2. Software Developer - NVQ Level IV (2025) | NVTI Baddegama</div>
      <div>3. English Diploma Certificate (2024) | INPM</div>
      <div>4. G.C.E. O/L Exam (English Medium) (2020/2021) | Christ Church Girls' College Baddegama</div>
    `,
    'experience': `
      <div class="term-res-title">// WORK EXPERIENCE TIMELINE</div>
      <div>• Software Developer & IT Assistant (Feb 2026 - Aug 2026) | Sakwa Canneries & Exports</div>
      <div>• Dance Teacher (Aug 2023 - Mar 2025) | G/Ganegama Roman Catholic Primary School</div>
      <div>• Business Development Associate (Jul 2021 - Jul 2022) | Chords by Semi Circle</div>
      <div>• English Teacher (Dec 2020 - Dec 2021) | Edexer Online Institute</div>
    `,
    'projects': `
      <div class="term-res-title">// REAL SOFTWARE PROJECTS (FROM REPOSITORIES)</div>
      <div>1. BloomyHaven — Floral E-Commerce & Admin CMS (PHP/MySQL - 3 Screenshots)</div>
      <div>2. BMC Machine Center — Industrial Equipment & Invoicing (Java Swing/HTML - 3 Screenshots)</div>
      <div>3. Gourmet POS — Restaurant Order & Automated Billing (Java Swing/MySQL - 3 Screenshots)</div>
      <div>4. NexaSphere University — Academic & Admissions Portal (PHP/MySQL - 3 Screenshots)</div>
      <div>5. VogueVibe Fashion — Multi-Tier Apparel & Supplier Platform (PHP/MySQL - 3 Screenshots)</div>
    `,
    'reference': `
      <div class="term-res-title">// OFFICIAL RECOMMENDATION REFERENCE</div>
      <div>Instructor : E. A. R. Lakshman</div>
      <div>Role       : Software Developer Instructor</div>
      <div>Institute  : National Vocational Training Institute (NVTI) Baddegama</div>
      <div>Phone      : +94 71 824 9323</div>
    `,
    'contact': `
      <div class="term-res-title">// DIRECT CHANNELS</div>
      <div>Email      : minudi418@gmail.com</div>
      <div>Phone/WA   : +94 77 994 5890</div>
      <div>Address    : "Bhanu Tailors", Lawalhena Junction, Wanduramba Road, Baddegama</div>
    `
  };

  function escapeHtml(str) {
    const p = document.createElement('p');
    p.textContent = str;
    return p.innerHTML;
  }

  function executeCommand(cmd) {
    const cleanCmd = cmd.trim().toLowerCase();

    const line = document.createElement('div');
    line.innerHTML = `<span class="terminal-prompt">guest@minudishashina : ~$</span> <span style="color:#fff">${escapeHtml(cmd)}</span>`;
    terminalOutput.appendChild(line);

    if (cleanCmd === 'clear') {
      terminalOutput.innerHTML = '';
      return;
    }

    const response = document.createElement('div');
    response.style.marginBottom = '12px';

    if (commands[cleanCmd]) {
      response.innerHTML = commands[cleanCmd];
    } else if (cleanCmd !== '') {
      response.innerHTML = `<div style="color:#ef4444">Command not recognized: '${escapeHtml(cleanCmd)}'. Type '<span class="term-command">help</span>' to view all commands.</div>`;
    }

    terminalOutput.appendChild(response);
    terminalOutput.scrollTop = terminalOutput.scrollHeight;
  }

  terminalInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      const val = terminalInput.value;
      if (val.trim()) {
        executeCommand(val);
        terminalInput.value = '';
      }
    }
  });

  actionButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const cmd = btn.getAttribute('data-cmd');
      if (cmd) {
        executeCommand(cmd);
      }
    });
  });
}


/* ===================================================================
   7. AJAX CONTACT FORM & TOASTS
   =================================================================== */
function initContactForm() {
  const form = document.getElementById('contact-form');
  const submitBtn = document.getElementById('form-submit-btn');

  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending Message...';
    submitBtn.disabled = true;

    try {
      const response = await fetch('php/contact.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      });

      const data = await response.json();

      if (response.ok && data.status === 'success') {
        showToast(data.message || 'Message sent successfully!', 'success');
        form.reset();
      } else {
        showToast(data.message || 'Failed to send message. Please try again.', 'error');
      }
    } catch (err) {
      showToast('Network error or server unreachable. Please reach out directly via Email.', 'error');
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  });
}

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast ${type === 'error' ? 'toast-error' : ''}`;
  toast.innerHTML = `
    <i class="fa-solid ${type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'}" style="color:${type === 'error' ? '#ef4444' : 'var(--accent-emerald)'}; font-size:1.2rem;"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => toast.remove(), 400);
  }, 4500);
}


/* ===================================================================
   8. SCROLL TO TOP WIDGET
   =================================================================== */
function initScrollTop() {
  const scrollBtn = document.getElementById('scroll-top-btn');
  if (!scrollBtn) return;

  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 400) {
      scrollBtn.classList.add('visible');
    } else {
      scrollBtn.classList.remove('visible');
    }
  });

  scrollBtn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}
