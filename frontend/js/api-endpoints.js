/*
 * ADDED FILE: js/api-endpoints.js  (v2 - database connection layer)
 * Adds AuthAPI, UsersAPI, ChildrenAPI, SchedulesAPI, TasksAPI and HealthAPI on top
 * of the existing api.js (left untouched; only its API_BASE_URL is reused).
 * Every call goes to the PHP backend, which reads/writes MySQL (visual_schedule_db).
 * Load order on every page: api.js -> api-endpoints.js -> script.js -> page-init.js
 */

const USER_STORAGE_KEY = 'kiddopath_user';

/*
 * Calls the backend and returns the parsed JSON. Throws an Error with a readable
 * message (err.status = HTTP status) when the server is unreachable, returns
 * non-JSON (PHP/MySQL misconfiguration), or answers status != "success".
 */
async function apiCall(endpoint, method = 'GET', data = null) {
  const config = { method, headers: { 'Content-Type': 'application/json' } };
  if (data) config.body = JSON.stringify(data);

  let response, text;
  try {
    response = await fetch(`${API_BASE_URL}/${endpoint}`, config);
    text = await response.text();
  } catch {
    throw new Error(`Cannot reach the backend at ${API_BASE_URL}. Start the PHP server (php -S localhost:5000 inside /backend) and MySQL, then open db-check.html.`);
  }

  let json;
  try { json = JSON.parse(text); } catch {
    const err = new Error(`The backend sent an unexpected response (HTTP ${response.status}). Open db-check.html to test the database connection.`);
    err.status = response.status;
    throw err;
  }
  if (json.status !== 'success') {
    const err = new Error(json.message || `Request failed (HTTP ${response.status}).`);
    err.status = response.status;
    throw err;
  }
  return json;
}

/* ---------- Auth (backend/routes/auth.php -> users table) ---------- */
const AuthAPI = {
  login: (email, password) =>
    apiCall('routes/auth.php?action=login', 'POST', { email, password }),      // -> { user }
  register: (name, email, password, role) =>
    apiCall('routes/auth.php?action=register', 'POST', { name, email, password, role }), // INSERT into users
  // "Remember me" => localStorage, otherwise sessionStorage
  saveUser(user, remember = true) {
    localStorage.removeItem(USER_STORAGE_KEY);
    sessionStorage.removeItem(USER_STORAGE_KEY);
    (remember ? localStorage : sessionStorage).setItem(USER_STORAGE_KEY, JSON.stringify(user));
  },
  // Refresh the cached user (e.g. after UsersAPI.verify) in whichever storage holds it
  updateUser(user) {
    const store = localStorage.getItem(USER_STORAGE_KEY) ? localStorage
                : sessionStorage.getItem(USER_STORAGE_KEY) ? sessionStorage : null;
    if (store) store.setItem(USER_STORAGE_KEY, JSON.stringify(user));
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

/* ---------- Users (backend/routes/users.php) ---------- */
const UsersAPI = {
  // Confirms the cached user still exists in the database (404 => err.status === 404)
  verify: async (user) =>
    (await apiCall(`routes/users.php?id=${user.id}&email=${encodeURIComponent(user.email)}`)).data,
};

/* ---------- Children (backend/routes/children.php -> children table) ---------- */
const ChildrenAPI = {
  list: async (userId) => (await apiCall(`routes/children.php?user_id=${userId}`)).data,
  create: async (payload) => (await apiCall('routes/children.php', 'POST', payload)).data,
  update: async (id, payload) => (await apiCall(`routes/children.php?id=${id}`, 'PUT', payload)).data,
  remove: (id, userId) => apiCall(`routes/children.php?id=${id}&user_id=${userId}`, 'DELETE'),
};

/* ---------- Schedules (backend/routes/schedules.php -> schedules table) ---------- */
const SchedulesAPI = {
  list: async (userId, childId = null) =>
    (await apiCall(`routes/schedules.php?user_id=${userId}${childId ? `&child_id=${childId}` : ''}`)).data,
  create: async (payload) => (await apiCall('routes/schedules.php', 'POST', payload)).data,
  update: async (id, payload) => (await apiCall(`routes/schedules.php?id=${id}`, 'PUT', payload)).data,
  remove: (id, userId) => apiCall(`routes/schedules.php?id=${id}&user_id=${userId}`, 'DELETE'),
};

/* ---------- Tasks (backend/routes/tasks.php -> tasks table) ---------- */
const TasksAPI = {
  list: async (userId, scheduleId = null) =>
    (await apiCall(`routes/tasks.php?user_id=${userId}${scheduleId ? `&schedule_id=${scheduleId}` : ''}`)).data,
  create: async (payload) => (await apiCall('routes/tasks.php', 'POST', payload)).data,
  // PUT replaces all editable fields, so always send the full task
  update: async (id, payload) => (await apiCall(`routes/tasks.php?id=${id}`, 'PUT', payload)).data,
  remove: (id, userId) => apiCall(`routes/tasks.php?id=${id}&user_id=${userId}`, 'DELETE'),
};

/* ---------- Connection check (backend/routes/health.php) ---------- */
const HealthAPI = { check: () => apiCall('routes/health.php') };
