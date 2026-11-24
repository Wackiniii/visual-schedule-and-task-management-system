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

## Known limits
- Login gives no session or token, so a request can claim any `user_id`.
- Lockout is per account, not per IP.
- The backend PHP has not been run, because there was no PHP or MySQL where I built it. Test it on XAMPP.
