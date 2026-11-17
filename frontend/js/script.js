const SETTINGS_STORAGE_KEY = 'kiddopath_settings';

const DEFAULT_SETTINGS = {
  fontSize: 'medium',
  contrast: 'normal',
  soundEnabled: true,
  voiceRate: 0.95,
  language: 'en-US',
  taskReminders: true,
  dailySummary: false,
};

function getAppSettings() {
  try {
    const saved = JSON.parse(localStorage.getItem(SETTINGS_STORAGE_KEY) || '{}');
    return { ...DEFAULT_SETTINGS, ...saved };
  } catch {
    return { ...DEFAULT_SETTINGS };
  }
}

function saveAppSettings(partial) {
  const updated = { ...getAppSettings(), ...partial };
  localStorage.setItem(SETTINGS_STORAGE_KEY, JSON.stringify(updated));
  applyAccessibilitySettings();
  return updated;
}

function applyAccessibilitySettings() {
  const settings = getAppSettings();
  document.documentElement.setAttribute('data-font-size', settings.fontSize || 'medium');
  document.documentElement.setAttribute('data-contrast', settings.contrast || 'normal');
}

applyAccessibilitySettings();

function requireAuth() {
  const user = AuthAPI.getCurrentUser();
  if (!user || !user.id) {
    window.location.href = 'index.html';
    return null;
  }
  return user;
}

function redirectIfAuthenticated() {
  const user = AuthAPI.getCurrentUser();
  if (user && user.id) {
    window.location.href = 'dashboard.html';
  }
}

function renderGlassNavbar(activePage = '') {
  const container = document.getElementById('appNavbar');
  if (!container) return;

  const user = AuthAPI.getCurrentUser();
  const userName = user ? escapeHtml(user.name) : 'Caregiver';
  const userRole = user ? escapeHtml(user.role) : 'Caregiver';

  const navItems = [
    { id: 'dashboard', label: 'Dashboard', icon: 'fa-house-heart', href: 'dashboard.html' },
    { id: 'schedule', label: 'Schedule Builder', icon: 'fa-calendar-plus', href: 'schedule-creator.html' },
    { id: 'child', label: 'Child View', icon: 'fa-child-reaching', href: 'child-view.html' },
    { id: 'progress', label: 'Progress', icon: 'fa-chart-pie', href: 'progress.html' },
    { id: 'settings', label: 'Settings', icon: 'fa-sliders', href: 'settings.html' },
  ];

  const linksHtml = navItems
    .map(
      (item) => `
      <a href="${item.href}" class="nav-pill-link ${activePage === item.id ? 'active' : ''}">
        <i class="fa-solid ${item.icon}"></i>
        <span>${item.label}</span>
      </a>`
    )
    .join('');

  container.innerHTML = `
    <nav class="glass-navbar py-2 px-3 px-lg-4">
      <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between gap-2">
        <a href="dashboard.html" class="brand-logo">
          <span class="brand-badge"><i class="fa-solid fa-sparkles"></i></span>
          <span>KiddoPath</span>
        </a>

        <button class="btn btn-pastel-glass d-lg-none px-3" type="button" data-bs-toggle="collapse" data-bs-target="#kiddoNavMenu" aria-expanded="false" aria-label="Toggle navigation">
          <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse justify-content-between align-items-center mt-2 mt-lg-0" id="kiddoNavMenu">
          <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-1 gap-lg-2 mx-lg-auto my-2 my-lg-0">
            ${linksHtml}
          </div>

          <div class="d-flex align-items-center justify-content-between justify-content-lg-end gap-2 pt-2 pt-lg-0 border-top border-lg-0">
            <div class="text-end me-1">
              <div class="fw-bold small lh-1">${userName}</div>
              <span class="badge-pastel badge-lavender mt-1" style="font-size: 0.72rem; padding: 0.2rem 0.6rem;">${userRole}</span>
            </div>
            <button type="button" id="logoutBtn" class="btn-pastel btn-pastel-danger px-3" title="Log Out">
              <i class="fa-solid fa-right-from-bracket"></i>
              <span class="d-none d-sm-inline">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </nav>
  `;

  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      AuthAPI.logout();
      window.location.href = 'index.html';
    });
  }
}

function showToast(message, type = 'sky') {
  let wrapper = document.getElementById('kiddoToastContainer');
  if (!wrapper) {
    wrapper = document.createElement('div');
    wrapper.id = 'kiddoToastContainer';
    wrapper.className = 'position-fixed bottom-0 end-0 p-3 d-flex flex-column gap-2';
    wrapper.style.zIndex = '1090';
    document.body.appendChild(wrapper);
  }

  const iconMap = {
    sky: { icon: 'fa-circle-info', tile: 'tile-sky' },
    mint: { icon: 'fa-circle-check', tile: 'tile-mint' },
    peach: { icon: 'fa-bell', tile: 'tile-peach' },
    rose: { icon: 'fa-circle-exclamation', tile: 'tile-rose' },
  };
  const cfg = iconMap[type] || iconMap.sky;

  const toastEl = document.createElement('div');
  toastEl.className = 'toast-glass p-3 d-flex align-items-center gap-3';
  toastEl.style.minWidth = '270px';
  toastEl.style.maxWidth = '360px';
  toastEl.innerHTML = `
    <div class="pastel-icon-tile ${cfg.tile}" style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
      <i class="fa-solid ${cfg.icon}"></i>
    </div>
    <div class="fw-semibold small flex-grow-1">${escapeHtml(message)}</div>
  `;

  wrapper.appendChild(toastEl);
  setTimeout(() => {
    toastEl.style.opacity = '0';
    toastEl.style.transform = 'translateY(8px)';
    toastEl.style.transition = 'all 0.25s ease';
    setTimeout(() => toastEl.remove(), 260);
  }, 3200);
}

function speakText(text) {
  const settings = getAppSettings();
  if (!settings.soundEnabled || !('speechSynthesis' in window)) return;

  window.speechSynthesis.cancel();
  const utterance = new SpeechSynthesisUtterance(text);
  utterance.lang = settings.language || 'en-US';
  utterance.rate = settings.voiceRate || 0.95;
  utterance.pitch = 1.05;
  window.speechSynthesis.speak(utterance);
}

function playSuccessChime() {
  const settings = getAppSettings();
  if (!settings.soundEnabled) return;
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    const ctx = new AudioCtx();
    const notes = [523.25, 659.25, 783.99];
    notes.forEach((freq, idx) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(0.08, ctx.currentTime + idx * 0.11);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + idx * 0.11 + 0.35);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(ctx.currentTime + idx * 0.11);
      osc.stop(ctx.currentTime + idx * 0.11 + 0.36);
    });
  } catch {
    // Audio playback blocked before interaction
  }
}

function formatTime12h(timeStr) {
  if (!timeStr) return '08:00 AM';
  const [hourRaw, minuteRaw] = timeStr.split(':');
  let hour = parseInt(hourRaw, 10);
  const minute = (minuteRaw || '00').padStart(2, '0');
  const ampm = hour >= 12 ? 'PM' : 'AM';
  hour = hour % 12 || 12;
  return `${hour}:${minute} ${ampm}`;
}

function getTodayISO() {
  const now = new Date();
  const offset = now.getTimezoneOffset() * 60000;
  return new Date(now.getTime() - offset).toISOString().split('T')[0];
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
