/*
 * ADDED FILE: js/page-init.js
 * Shared bootstrap for the protected pages (dashboard, schedule-creator,
 * child-view, progress, settings). Uses requireAuth() + renderGlassNavbar()
 * from script.js and wires the mobile menu button, because Bootstrap's JS
 * (data-bs-toggle) is not loaded in this project.
 */
function initPage(activePage) {
  const user = requireAuth();           // redirects to index.html when logged out
  if (!user) return null;
  renderGlassNavbar(activePage);
  const toggle = document.querySelector('[data-bs-toggle="collapse"]');
  if (toggle) {
    toggle.addEventListener('click', () => {
      document.querySelector(toggle.dataset.bsTarget).classList.toggle('show');
    });
  }
  return user;
}

/* Small shared helpers for the pages */
function childInitials(name) {
  return (name || '?').trim().split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
}
function childAvatarHtml(child) {
  return child.photo_url
    ? `<img class="child-avatar" src="${escapeHtml(child.photo_url)}" alt="${escapeHtml(child.name)}" />`
    : `<span class="child-avatar">${escapeHtml(childInitials(child.name))}</span>`;
}
function fillSelect(select, items, labelFn, placeholder) {
  select.innerHTML = (placeholder ? `<option value="">${escapeHtml(placeholder)}</option>` : '') +
    items.map(i => `<option value="${i.id}">${escapeHtml(labelFn(i))}</option>`).join('');
}
