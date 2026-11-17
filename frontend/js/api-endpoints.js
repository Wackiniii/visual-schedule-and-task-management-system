/*
 * ADDED FILE: js/api-endpoints.js
 * Why: index.html / script.js call AuthAPI.* but the existing api.js only
 * defines apiRequest(). This file ADDS AuthAPI, ChildrenAPI, SchedulesAPI and
 * TasksAPI on top of apiRequest() WITHOUT touching api.js.
 * Load order on every page: api.js -> api-endpoints.js -> script.js
 */

const USER_STORAGE_KEY = 'kiddopath_user';

/* Wraps apiRequest(): returns the response, throws Error(message) if status != success */
async function apiCall(endpoint, method = 'GET', data = null) {
  const res = await apiRequest(endpoint, method, data);
  if (!res || res.status !== 'success') {
    throw new Error((res && res.message) || 'Request failed.');
  }
  return res;
}

/* ---------- Auth (backend/routes/auth.php) ---------- */
const AuthAPI = {
  login: (email, password) =>
    apiCall('routes/auth.php?action=login', 'POST', { email, password }), // -> { user }
  register: (name, email, password, role) =>
    apiCall('routes/auth.php?action=register', 'POST', { name, email, password, role }),
  // "Remember me" => localStorage, otherwise sessionStorage
  saveUser(user, remember = true) {
    localStorage.removeItem(USER_STORAGE_KEY);
    sessionStorage.removeItem(USER_STORAGE_KEY);
    (remember ? localStorage : sessionStorage).setItem(USER_STORAGE_KEY, JSON.stringify(user));
  },
  getCurrentUser() {
    try {
      const raw = localStorage.getItem(USER_STORAGE_KEY) || sessionStorage.getItem(USER_STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch { return null; }
  },
  logout() {
    localStorage.removeItem(USER_STORAGE_KEY);
    sessionStorage.removeItem(USER_STORAGE_KEY);
  },
};

/* ---------- Children (backend/routes/children.php) ---------- */
const ChildrenAPI = {
  list: async (userId) => (await apiCall(`routes/children.php?user_id=${userId}`)).data,
  create: async (payload) => (await apiCall('routes/children.php', 'POST', payload)).data,
  update: async (id, payload) => (await apiCall(`routes/children.php?id=${id}`, 'PUT', payload)).data,
  remove: (id, userId) => apiCall(`routes/children.php?id=${id}&user_id=${userId}`, 'DELETE'),
};

/* ---------- Schedules (backend/routes/schedules.php) ---------- */
const SchedulesAPI = {
  list: async (userId, childId = null) =>
    (await apiCall(`routes/schedules.php?user_id=${userId}${childId ? `&child_id=${childId}` : ''}`)).data,
  create: async (payload) => (await apiCall('routes/schedules.php', 'POST', payload)).data,
  update: async (id, payload) => (await apiCall(`routes/schedules.php?id=${id}`, 'PUT', payload)).data,
  remove: (id, userId) => apiCall(`routes/schedules.php?id=${id}&user_id=${userId}`, 'DELETE'),
};

/* ---------- Tasks (backend/routes/tasks.php) ---------- */
const TasksAPI = {
  list: async (userId, scheduleId = null) =>
    (await apiCall(`routes/tasks.php?user_id=${userId}${scheduleId ? `&schedule_id=${scheduleId}` : ''}`)).data,
  create: async (payload) => (await apiCall('routes/tasks.php', 'POST', payload)).data,
  // PUT replaces all editable fields, so send the full task + new values
  update: async (id, payload) => (await apiCall(`routes/tasks.php?id=${id}`, 'PUT', payload)).data,
  remove: (id, userId) => apiCall(`routes/tasks.php?id=${id}&user_id=${userId}`, 'DELETE'),
};
