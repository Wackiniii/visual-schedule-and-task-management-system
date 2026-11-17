# Children, schedules, and tasks API
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
