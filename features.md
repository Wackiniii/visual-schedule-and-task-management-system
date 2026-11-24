# KiddoPath: app features

## Accounts and security
- **Register** with name, email, password and role (Caregiver, Occupational Therapist or SPED Teacher).
- **Password rules:** 8-72 characters with an uppercase letter, a lowercase letter, a number and a symbol. Passwords are stored hashed.
- **Email format** is validated on registration, and duplicate emails are rejected.
- **Login** with email and password, with a show/hide password button.
- **Remember me:** on keeps you signed in on this device, off ends the session when the browser closes.
- **Lockout:** 5 wrong passwords in a row lock the account for 15 minutes, even for the right password. A successful login resets the count.
- **Generic error:** unknown email and wrong password give the same message.
- **Logout** button, and every protected page sends logged-out visitors back to the login page.

## Dashboard
- A welcome message and one summary line for today: schedules, tasks, tasks done and number of children.
- **Child profiles:** add, edit and delete, with name, age, delay domain, notes and photo URL. Deleting a child also deletes their schedules and tasks.
- Each child is a large card with an **Open** button that goes straight to their child view.

## Schedule Builder
- Pick a **child** and a **date**. Dates that already have a schedule show as chips you can click.
- **Activity bank** of 9 preset activities (wake up, brush teeth, get dressed, eat breakfast, study, play, snack, bath, bedtime). Drag them in or tap them, or add a custom activity.
- Give each new task an optional **start time** and **duration**, then save.
- **Saved tasks** can be edited (name, time, duration, done) or deleted.
- **Delete a whole schedule** with its tasks.
- **Reusable task sets:**
  - Save the current tasks as a named set.
  - Use a set to load it into New Tasks for any date.
  - Delete a set whenever you want.

## Child View
- Choose the child, or arrive from the dashboard's Open button.
- A **timeline** of today's routine, sorted by time, with finished steps marked.
- A **Now card** for the current task, with its icon, name and time.
- **Pie countdown timer** with start and pause. It plays a chime and says "Time is up!" at the end.
- **Say it** reads the task aloud with text-to-speech.
- **All done!** marks the task complete and moves to the next one. The finished state shows "All tasks done. Great job!"
- **My Talking Board:** 8 tap-to-speak picture tiles (I want water, I am hungry, I need help, Bathroom please, Yes, No, I am tired, I want to play).

## Progress
- Starts on the user's first child, and shows "Create a child first" if there are none.
- **Today's numbers:** tasks, completed and completion rate, or "No schedule for this child."
- **Last 7 days activity:** a toggle button that stays hidden until opened. It shows one completion bar per day.

## Settings
- **Text size** (small, medium, large) and **contrast** (normal, high).
- **Sounds and speech** on or off, **voice speed**, and a **Test voice** button. The voice is fixed to English (US).
- **Task reminders** and **daily summary** switches. These are saved preferences only; nothing sends notifications yet.
- **Reset** returns every setting to default. Settings are saved on the device.

## Look and accessibility
- "Sticker Book" design: warm paper background, flat saturated colors, thick outlines, chunky rounded shapes, with the child view as the most colorful screen.
- Responsive layout with a mobile menu.
- Meets WCAG AA contrast, with large touch targets (64px on the child view's main buttons) and a visible focus ring.
- Meaning is never carried by color alone, since icons, labels and check marks back it up.
- Nothing animates on its own, and reduced-motion is respected.
- Toast messages confirm saves and show errors.

## Under the hood
- **Database tables:** `users`, `children`, `schedules`, `tasks` and `task_sets`, all in the MySQL database `visual_schedule_db`.
- **API routes:** `auth`, `children`, `schedules`, `tasks` and `task_sets`, all returning JSON.
- **Ownership:** each route matches the request's `user_id` to the owner of the child, schedule, task or set.

**prbr**

# KiddoPath: Feature Implementation Documentation

## Sprint 1 (Building of the Backend and Database)

**Goal:** a PHP and MySQL backend that stores and serves all app data as JSON.

**Database (`database/schema.sql`, MySQL database `visual_schedule_db`)**
- `users`: name, unique email, hashed password, role (Caregiver, Occupational Therapist or SPED Teacher).
- `children`: belongs to a user, with name, age, delay domain, notes and photo URL.
- `schedules`: belongs to a child, one per date.
- `tasks`: belongs to a schedule, with name, icon, time, duration, status (pending or completed) and completed-at time.
- Foreign keys use `ON DELETE CASCADE`, so deleting a child removes their schedules and tasks.

**Backend (`backend/`)**
- `config/db.php`: MySQL connection and CORS settings for the frontend on `localhost:3000`. The backend runs on `localhost:5000`.
- `config/api.php`: shared helpers for JSON responses, request validation (ids, strings, times, durations) and prepared queries.
- `routes/children.php`, `schedules.php`, `tasks.php`: create, read, update and delete (GET, POST, PUT, DELETE).
- Every response is `{"status":"success","data":...}` or `{"status":"error","message":"..."}`.
- All SQL uses prepared statements. Each route checks that the request's `user_id` owns the child, schedule or task.
- Endpoints are documented in `docs/API.md`.

**Setup:** start Apache and MySQL in XAMPP, import `schema.sql`, then start the two PHP servers (ports 5000 and 3000) as described in the README.

## Sprint 2 (Building of the Authentication Page and User Dashboard)

**Goal:** let users create accounts, sign in securely, and manage child profiles.

**Authentication**
- `index.html` (login) and `registration.html` (name, email, role, password, confirm password).
- `routes/auth.php` handles register and login. Passwords are stored hashed.
- Password rules: 8-72 characters with an uppercase letter, a lowercase letter, a number and a symbol. The server and the registration page check the same rule.
- Email format validation, and duplicate emails are rejected.
- "Remember me" keeps the login in `localStorage`. Without it, the login lives in `sessionStorage`.
- **Lockout:** 5 wrong passwords in a row lock the account for 15 minutes, even for the right password. A successful login resets the count. This uses two extra `users` columns, `failed_attempts` and `locked_until`.
- Unknown email and wrong password return the same message, so emails cannot be probed.
- Every protected page checks the login and redirects to the login page if it is missing. A logout button clears the login.

**Frontend plumbing**
- `js/api-endpoints.js` defines `AuthAPI`, `ChildrenAPI`, `SchedulesAPI` and `TasksAPI` on top of `api.js`.
- `script.js` renders the navbar, toast messages and the mobile menu.

**Dashboard (`dashboard.html`)**
- Welcome message and a one-line summary for today: schedules, tasks, tasks done and children.
- Child profiles: add, edit and delete (name, age, delay domain, notes, photo URL).
- Each child is a large card with an **Open** button that goes to their child view.

## Sprint 3 (Building of the Schedule Builder, Child View and Progress)

**Goal:** build the core scheduling features that caregivers use and children follow.

**Schedule Builder (`schedule-creator.html`)**
- Choose a child and date. Dates that already have a schedule show as chips.
- Activity bank of 9 presets that can be dragged or tapped in, plus custom activities.
- Optional start time and duration per task.
- Saved tasks can be edited (name, time, duration, done) and deleted. A whole schedule can be deleted along with its tasks.
- **Reusable task sets:** save the current tasks as a named set, use a set on any date, or delete it. This adds the `task_sets` table and `routes/task_sets.php`, scoped to each user.

**Child View (`child-view.html`)**
- Timeline of today's tasks, sorted by time.
- A "Now" card for the current task with a pie countdown timer (start and pause). It plays a chime and speaks "Time is up!" at the end.
- "Say it" reads the task aloud with text-to-speech.
- "All done!" marks the task complete and moves to the next one.
- My Talking Board: 8 tap-to-speak picture tiles.

**Progress (`progress.html`)**
- Starts on the user's first child, or shows "Create a child first" if there are none.
- Today's tasks, completed count and completion rate, or "No schedule for this child."
- A "Last 7 days activity" toggle that stays hidden until opened.

## Sprint 4 (Building of the Settings and Accessibility)

**Goal:** let users adjust the app to the child's needs and make every screen accessible.

**Settings (`settings.html`, saved on the device)**
- Text size (small, medium, large) and contrast (normal, high), applied instantly.
- Sounds and speech on or off, voice speed, and a Test voice button.
- The voice is fixed to English (US).
- Task reminders and daily summary switches. These are saved preferences only; nothing sends notifications yet.
- Reset to defaults.

**Accessibility and design**
- "Sticker Book" look: warm paper background, flat saturated colors, thick outlines and chunky rounded shapes. The child view is the most colorful screen.
- Text and controls meet WCAG AA contrast. Targets are at least 48px, and 64px on the child view's main buttons.
- A visible focus ring, and meaning is never carried by color alone (icons, labels and check marks back it up).
- High-contrast mode goes pure black on white with thicker outlines.
- Nothing animates on its own, and reduced-motion is respected.
- Responsive layout with a mobile menu, and two-column talking board tiles on phones.
- Design decisions are recorded in `PRODUCT.md` and `DESIGN.md`.

## Verification
- A browser test (`tests/e2e.py`, 88 checks) clicks through every page and button against a mock of the API, and all checks pass.
- The PHP backend has not been run against real MySQL. Test it on XAMPP before release.
- Known limit: login issues no session or token, so any request can claim any `user_id`.

## Known limits
- Login gives no session or token, so a request can claim any `user_id`.
- Lockout is per account, not per IP.
- The backend PHP has not been run, because there was no PHP or MySQL where I built it. Test it on XAMPP.
