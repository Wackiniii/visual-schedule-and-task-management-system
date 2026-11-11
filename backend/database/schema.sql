CREATE DATABASE IF NOT EXISTS visual_schedule_db;
USE visual_schedule_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Caregiver', 'Occupational Therapist', 'SPED Teacher') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    delay_domain VARCHAR(100),
    notes TEXT,
    photo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-tasks',
    time TIME,
    duration INT COMMENT 'in minutes',
    status ENUM('pending', 'completed') DEFAULT 'pending',
    completed_at DATETIME NULL,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
);
