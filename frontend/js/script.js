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
    {
      id: 'dashboard',
      label: 'Dashboard',
      href: 'dashboard.html',
      svg: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5z"/></svg>',
    },
    {
      id: 'schedule',
      label: 'Schedule Builder',
      href: 'schedule-creator.html',
      svg: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></svg>',
    },
    {
      id: 'child',
      label: 'Child View',
      href: 'child-view.html',
      svg: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/></svg>',
    },
    {
      id: 'progress',
      label: 'Progress',
      href: 'progress.html',
      svg: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 4 3 5-6"/></svg>',
    },
    {
      id: 'settings',
      label: 'Settings',
      href: 'settings.html',
      svg: '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    },
  ];

  const linksHtml = navItems
    .map(
      (item) => `
      <a href="${item.href}" class="nav-pill-link ${activePage === item.id ? 'active' : ''}">
        ${item.svg}
        <span>${item.label}</span>
      </a>`
    )
    .join('');

  container.innerHTML = `
    <nav class="glass-navbar" style="padding: 0.75rem 1.25rem;">
      <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
        <a href="dashboard.html" class="brand-logo">
          <span class="brand-badge">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 3l1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3z"/></svg>
          </span>
          <span>KiddoPath</span>
        </a>

        <div class="d-flex flex-wrap align-items-center gap-1">
          ${linksHtml}
        </div>

        <div class="d-flex align-items-center gap-3">
          <div class="text-end">
            <div class="fw-bold small">${userName}</div>
            <span class="badge-pastel badge-lavender" style="font-size: 0.72rem; padding: 0.18rem 0.6rem;">${userRole}</span>
          </div>
          <button type="button" id="logoutBtn" class="btn-pastel btn-pastel-danger px-3" style="min-height: 42px; padding: 0.45rem 0.95rem;">
            <span>Logout</span>
          </button>
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
    wrapper.style.cssText = 'position:fixed;bottom:1.25rem;right:1.25rem;z-index:2000;display:flex;flex-direction:column;gap:0.6rem;';
    document.body.appendChild(wrapper);
  }

  const tileMap = { sky: 'tile-sky', mint: 'tile-mint', peach: 'tile-peach', rose: 'tile-rose' };
  const toastEl = document.createElement('div');
  toastEl.className = 'toast-glass p-3 d-flex align-items-center gap-3';
  toastEl.style.cssText = 'min-width:260px;max-width:360px;';
  toastEl.innerHTML = `
    <div class="pastel-icon-tile ${tileMap[type] || 'tile-sky'}" style="width:38px;height:38px;border-radius:12px;font-size:1rem;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <div class="fw-semibold small flex-grow-1">${escapeHtml(message)}</div>
  `;

  wrapper.appendChild(toastEl);
  setTimeout(() => {
    toastEl.style.opacity = '0';
    toastEl.style.transform = 'translateY(8px)';
    toastEl.style.transition = 'all 0.25s ease';
    setTimeout(() => toastEl.remove(), 260);
  }, 3000);
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
