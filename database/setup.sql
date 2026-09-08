-- EduNexAI Full Database Setup
-- Run this to create all tables and default admin user

USE student_ai_system;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS prediction_history;
DROP TABLE IF EXISTS predictions;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS marks;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- Users table (admin, faculty, student)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','faculty','student') NOT NULL,
    enrollment_no VARCHAR(50) UNIQUE DEFAULT NULL,
    first_login TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class VARCHAR(50),
    roll_number VARCHAR(20),
    attendance DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subjects table
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20),
    faculty_id INT DEFAULT NULL,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Marks table
CREATE TABLE marks (
    mark_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject_id INT,
    internal_marks INT DEFAULT 0,
    external_marks INT DEFAULT 0,
    total_marks INT DEFAULT 0,
    exam_date DATE DEFAULT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

-- Attendance table
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject_id INT,
    present_days INT DEFAULT 0,
    total_days INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

-- Predictions table
CREATE TABLE predictions (
    prediction_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    predicted_score DECIMAL(5,2),
    result VARCHAR(20),
    risk_level VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Prediction history table
CREATE TABLE prediction_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    predicted_score DECIMAL(5,2),
    result VARCHAR(20),
    risk_level VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Default Admin User
-- Email: admin@edunexai.com | Password: Admin@123
INSERT INTO users (name, email, password, role, enrollment_no, first_login)
VALUES (
    'Admin',
    'admin@edunexai.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    NULL,
    0
);
