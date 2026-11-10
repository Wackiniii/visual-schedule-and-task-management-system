CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'caregiver', -- parent, caregiver, ot, teacher
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE children (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    delay_domain VARCHAR(100), -- speech, motor, cognitive, etc.
    notes TEXT,
    avatar VARCHAR(50) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE schedules (
    id SERIAL PRIMARY KEY,
    child_id INT NOT NULL REFERENCES children(id) ON DELETE CASCADE,
    schedule_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_child_date UNIQUE (child_id, schedule_date)
);

CREATE TABLE tasks (
    id SERIAL PRIMARY KEY,
    schedule_id INT NOT NULL REFERENCES schedules(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(100) DEFAULT 'default.png',
    task_time TIME NULL,
    duration INT DEFAULT 15, -- duration in minutes
    sort_order INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'pending', -- pending, in_progress, done
    completed_at TIMESTAMP NULL
);

-- OPTIONAL SEED DATA FOR TESTING
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Demo Parent', 'parent@example.com', '$2y$10$samplehashhere', 'parent');

INSERT INTO children (user_id, name, age, delay_domain) 
VALUES (1, 'Leo', 8, 'Speech Delay');