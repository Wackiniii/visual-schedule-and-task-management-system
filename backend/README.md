# Setup Guide

# 0. Cloning Repository
1. Copy our Repo `https://github.com/Wackiniii/visual-schedule-and-task-management-system.git`
2. Open **Visual Studio Code**
3. Click **Source Control icon** on the left side (or press `Ctrl + Shift + G`)
4. CLick Clone Repository and paste the repo link of the project then select the destination folder on your PC

# 1. Setting up Database
1. Open **XAMPP control panel** then start both Apache and MySQL
2. Click Admin button next to MySQL module that will redirect you to phpmyadmin `http://localhost/phpmyadmin`
3. Click SQL tab on top of the page
4. Copy the schema from `database/schema.sql`, paste it into the SQL query box then click Go

# 2. Setting up PHP built-in server for backend and frontend
1. Open terminal via View > Terminal (or press `` Ctrl + ` ``)
2. Terminal 1 (Backend API)
```bash
   cd backend
   php -S localhost:5000
```
- copy the following above
- Leave the terminal running. It should result to `PHP Development Server (http://localhost:5000) started`

3. Terminal 2 (Frontend API)
```bash
    cd frontend
    php -S localhost:3000
```
- click + icon and copy the following above
- Leave this terminal running. It should result to `PHP Development Server (http://localhost:3000) started`

# 3. Opening the application
- Copy and paste the link in the browser `http://localhost:3000/`

# 4. Closing the application
- close both terminal by pressing `Ctrl + C`

# 5. Children, schedules, and tasks API
All endpoints return JSON in the form `{"status":"success","data":...}` or
`{"status":"error","message":"..."}`. IDs are positive integers unless noted.
Use JSON request bodies for `POST` and `PUT`. `PUT` replaces the editable fields;
omitted optional fields are cleared (except a task's omitted `icon`, which uses
`fa-tasks`).

## Children (`/routes/children.php`)
- `GET ?user_id=<id>` lists that user's children; add `&id=<child-id>` for one child.
- `POST` with `user_id`, `name`, and `age` creates a child. Optional fields:
  `delay_domain`, `notes`, and `photo_url`.
- `PUT?id=<child-id>` with the same fields updates a child.
- `DELETE?id=<child-id>&user_id=<id>` deletes a child.

## Schedules (`/routes/schedules.php`)
- `GET ?user_id=<id>` lists that user's schedules. Optional filters: `id` and
  `child_id`.
- `POST` with `user_id`, `child_id`, and `date` (`YYYY-MM-DD`) creates a schedule.
- `PUT?id=<schedule-id>` with `user_id`, `child_id`, and `date` updates it.
- `DELETE?id=<schedule-id>&user_id=<id>` deletes a schedule.

## Tasks (`/routes/tasks.php`)
- `GET ?user_id=<id>` lists that user's tasks. Optional filters: `id` and
  `schedule_id`.
- `POST` with `user_id`, `schedule_id`, and `name` creates a task. Optional fields:
  `icon`, `time` (`HH:MM` or `HH:MM:SS`), `duration` (non-negative minutes), and
  `status` (`pending` or `completed`). Completed tasks get a `completed_at` time.
- `PUT?id=<task-id>` with `user_id`, `schedule_id`, and `name` updates a task;
  its optional fields follow the create rules.
- `DELETE?id=<task-id>&user_id=<id>` deletes a task.

The endpoints enforce ownership by matching the supplied `user_id` to the child
owner, including through schedules and tasks. The current login endpoint does not
issue or verify an authentication token, so `user_id` is caller-supplied and is
not an authentication mechanism; add server-side authentication before exposing
these APIs to untrusted clients.
