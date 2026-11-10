CREATE DATABASE IF NOT EXISTS visual_schedule
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE visual_schedule;

--1. USERS & SETTINGS
CREATE TABLE users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100)  NOT NULL,
  email          VARCHAR(150)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255)  NOT NULL,
  role           ENUM('parent','caregiver','ot','teacher') NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_settings (
  user_id              INT UNSIGNED PRIMARY KEY,
  font_size            ENUM('S','M','L')          NOT NULL DEFAULT 'M',
  color_contrast       ENUM('normal','high')      NOT NULL DEFAULT 'normal',
  sound_enabled        BOOLEAN                    NOT NULL DEFAULT TRUE,
  language             ENUM('en','fil')           NOT NULL DEFAULT 'en',
  notify_reminders     BOOLEAN                    NOT NULL DEFAULT TRUE,
  notify_completions   BOOLEAN                    NOT NULL DEFAULT TRUE,
  notify_weekly_report BOOLEAN                    NOT NULL DEFAULT FALSE,
  tts_language         VARCHAR(10)                NOT NULL DEFAULT 'en',
  tts_speed            DECIMAL(2,1)               NOT NULL DEFAULT 1.0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

--2. CHILDREN
CREATE TABLE children (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,              -- owner / primary caregiver
  name          VARCHAR(100) NOT NULL,
  age           TINYINT UNSIGNED NOT NULL,
  delay_domain  VARCHAR(100),                       -- e.g. speech, motor, cognitive
  notes         TEXT,
  photo_url     VARCHAR(255),
  avatar        VARCHAR(20),                        -- emoji/avatar for child view
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_children_user (user_id)
) ENGINE=InnoDB;

--Lets OTs / teachers / other caregivers view or edit a child's data
CREATE TABLE child_access (
  child_id      INT UNSIGNED NOT NULL,
  user_id       INT UNSIGNED NOT NULL,
  access_level  ENUM('view','edit') NOT NULL DEFAULT 'view',
  granted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (child_id, user_id),
  FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)  REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

--3. ACTIVITY LIBRARY
CREATE TABLE activity_categories (
  id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name   VARCHAR(50) NOT NULL UNIQUE,               -- hygiene, food, school, play
  color  CHAR(7)     NOT NULL DEFAULT '#4A90D9'
) ENGINE=InnoDB;

CREATE TABLE activities (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           INT UNSIGNED NULL,              -- NULL = built-in activity
  category_id       INT UNSIGNED NOT NULL,
  name              VARCHAR(100) NOT NULL,
  icon              VARCHAR(50),
  image_url         VARCHAR(255),                   -- custom uploaded image
  default_duration  SMALLINT UNSIGNED DEFAULT 5,    -- minutes
  FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES activity_categories(id)
) ENGINE=InnoDB;

--4. SCHEDULES & TASKS
CREATE TABLE schedules (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  child_id       INT UNSIGNED NOT NULL,
  schedule_date  DATE NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_child_date (child_id, schedule_date),
  FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tasks (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  schedule_id    INT UNSIGNED NOT NULL,
  activity_id    INT UNSIGNED NULL,                 -- source from library (optional)
  name           VARCHAR(100) NOT NULL,
  icon           VARCHAR(50),
  image_url      VARCHAR(255),
  task_time      TIME NULL,
  duration       SMALLINT UNSIGNED DEFAULT 5,       -- minutes
  reminder       BOOLEAN NOT NULL DEFAULT FALSE,
  sort_order     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  status         ENUM('pending','in_progress','done') NOT NULL DEFAULT 'pending',
  completed_at   DATETIME NULL,
  FOREIGN KEY (schedule_id) REFERENCES schedules(id)  ON DELETE CASCADE,
  FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL,
  INDEX idx_tasks_schedule (schedule_id, sort_order),
  INDEX idx_tasks_completed (completed_at)
) ENGINE=InnoDB;

--5. REWARDS
CREATE TABLE rewards (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  child_id        INT UNSIGNED NOT NULL,
  title           VARCHAR(100) NOT NULL,            -- e.g. "Extra screen time"
  icon            VARCHAR(50),
  tasks_required  SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reward_earnings (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reward_id   INT UNSIGNED NOT NULL,
  child_id    INT UNSIGNED NOT NULL,
  earned_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  given_at    DATETIME NULL,                        -- NULL = earned but not yet given
  FOREIGN KEY (reward_id) REFERENCES rewards(id)  ON DELETE CASCADE,
  FOREIGN KEY (child_id)  REFERENCES children(id) ON DELETE CASCADE
) ENGINE=InnoDB;

--6. TIMER PRESETS (visual countdown + caregiver reminders)
CREATE TABLE timer_presets (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id          INT UNSIGNED NOT NULL,
  child_id         INT UNSIGNED NULL,
  reminder_text    VARCHAR(150) NOT NULL,           -- "Time to brush teeth"
  icon             VARCHAR(50),
  minutes          SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  warning_seconds  SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  sound_cues       BOOLEAN NOT NULL DEFAULT TRUE,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)  REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE SET NULL
) ENGINE=InnoDB;

--7. COMMUNICATION BOARD (picture cards + text-to-speech)
CREATE TABLE board_categories (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id   INT UNSIGNED NULL,                      -- NULL = built-in (Food, Emotions, Activities)
  child_id  INT UNSIGNED NULL,                      -- optional per-child board
  name      VARCHAR(50) NOT NULL,
  icon      VARCHAR(50),
  FOREIGN KEY (user_id)  REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE picture_cards (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id  INT UNSIGNED NOT NULL,
  word         VARCHAR(50) NOT NULL,                -- word spoken by TTS
  icon         VARCHAR(50),
  image_url    VARCHAR(255),                        -- uploaded photo
  sort_order   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES board_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

--8. USABILITY TESTING (setup time, errors, SUS)
CREATE TABLE usability_sessions (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  participant_label   VARCHAR(50) NOT NULL,         -- anonymized, e.g. "P01"
  participant_role    ENUM('parent','caregiver','ot','teacher') NOT NULL,
  task_name           VARCHAR(100) NOT NULL,
  setup_time_seconds  DECIMAL(6,1),
  errors              SMALLINT UNSIGNED DEFAULT 0,
  completed           BOOLEAN NOT NULL DEFAULT TRUE,
  feedback            TEXT,
  tested_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE sus_responses (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  participant_label  VARCHAR(50) NOT NULL,
  q1  TINYINT UNSIGNED NOT NULL, q2  TINYINT UNSIGNED NOT NULL,
  q3  TINYINT UNSIGNED NOT NULL, q4  TINYINT UNSIGNED NOT NULL,
  q5  TINYINT UNSIGNED NOT NULL, q6  TINYINT UNSIGNED NOT NULL,
  q7  TINYINT UNSIGNED NOT NULL, q8  TINYINT UNSIGNED NOT NULL,
  q9  TINYINT UNSIGNED NOT NULL, q10 TINYINT UNSIGNED NOT NULL,
  
  --Standard SUS formula: odd items (score-1), even items (5-score), sum x 2.5
  sus_score DECIMAL(4,1) GENERATED ALWAYS AS (
    ((q1 + q3 + q5 + q7 + q9 - 5) + (25 - (q2 + q4 + q6 + q8 + q10))) * 2.5
  ) STORED,
  submitted_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_sus_range CHECK (
    q1  BETWEEN 1 AND 5 AND q2  BETWEEN 1 AND 5 AND q3 BETWEEN 1 AND 5 AND
    q4  BETWEEN 1 AND 5 AND q5  BETWEEN 1 AND 5 AND q6 BETWEEN 1 AND 5 AND
    q7  BETWEEN 1 AND 5 AND q8  BETWEEN 1 AND 5 AND q9 BETWEEN 1 AND 5 AND
    q10 BETWEEN 1 AND 5
  )
) ENGINE=InnoDB;
