/* ADDED: API layer + shared helpers on top of api.js. Load order: api.js, api-endpoints.js, script.js */
const $ = (id) => document.getElementById(id);
const USER_KEY = 'kiddopath_user';

async function apiCall(endpoint, method, data) {
  const res = await apiRequest(endpoint, method, data);
  if (res.status !== 'success') throw new Error(res.message);
  return res;
}

const AuthAPI = {
  login: (email, password) => apiCall('routes/auth.php?action=login', 'POST', { email, password }),
  register: (name, email, password, role) => apiCall('routes/auth.php?action=register', 'POST', { name, email, password, role }),
  saveUser(user, remember = true) { // "remember me" => localStorage, otherwise sessionStorage
    localStorage.removeItem(USER_KEY); sessionStorage.removeItem(USER_KEY);
    (remember ? localStorage : sessionStorage).setItem(USER_KEY, JSON.stringify(user));
  },
  getCurrentUser: () => JSON.parse(localStorage.getItem(USER_KEY) || sessionStorage.getItem(USER_KEY) || 'null'),
  logout() { localStorage.removeItem(USER_KEY); sessionStorage.removeItem(USER_KEY); },
};

// list(userId, filterId) filters by `filter` (e.g. schedules by child_id); update() must receive the full record
const crud = (path, filter) => ({
  list: async (uid, fid) => (await apiCall(`routes/${path}.php?user_id=${uid}${fid ? `&${filter}=${fid}` : ''}`)).data,
  create: async (body) => (await apiCall(`routes/${path}.php`, 'POST', body)).data,
  update: async (id, body) => (await apiCall(`routes/${path}.php?id=${id}`, 'PUT', body)).data,
  remove: (id, uid) => apiCall(`routes/${path}.php?id=${id}&user_id=${uid}`, 'DELETE'),
});
const ChildrenAPI = crud('children'), SchedulesAPI = crud('schedules', 'child_id'), TasksAPI = crud('tasks', 'schedule_id'), TaskSetsAPI = crud('task_sets');

// Auth guard + navbar; the hamburger is wired by hand because Bootstrap JS is not loaded
function initPage(active) {
  const user = requireAuth();
  if (!user) return { id: 0, name: '' }; // redirecting to login
  renderGlassNavbar(active);
  const btn = document.querySelector('[data-bs-toggle="collapse"]');
  btn.addEventListener('click', () => $(btn.dataset.bsTarget.slice(1)).classList.toggle('show'));
  return user;
}

const fillSelect = (select, items, placeholder) =>
  select.innerHTML = (placeholder ? `<option value="">${placeholder}</option>` : '') +
    items.map(i => `<option value="${i.id}">${escapeHtml(i.name)}</option>`).join('');
