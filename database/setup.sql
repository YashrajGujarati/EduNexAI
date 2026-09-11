-- EduNexAI Full Database Setup
-- Run this to create all tables and default admin user

USE student_ai_system;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS fee_payments;
DROP TABLE IF EXISTS student_fees;
DROP TABLE IF EXISTS assignment_submissions;
DROP TABLE IF EXISTS assignments;
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

-- Assignments table
CREATE TABLE assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subject_id INT NOT NULL,
    faculty_id INT NOT NULL,
    due_date DATETIME NOT NULL,
    total_marks INT DEFAULT 100,
    file_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Assignment Submissions table
CREATE TABLE assignment_submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('submitted', 'late', 'graded') DEFAULT 'submitted',
    marks_obtained INT DEFAULT NULL,
    feedback TEXT DEFAULT NULL,
    UNIQUE KEY unique_student_assignment (assignment_id, student_id),
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Student Fees table
CREATE TABLE student_fees (
    fee_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL UNIQUE,
    total_fee DECIMAL(10,2) NOT NULL DEFAULT 50000.00,
    paid_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    due_date DATE DEFAULT NULL,
    status ENUM('paid', 'partial', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Fee Payments table
CREATE TABLE fee_payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    fee_id INT NOT NULL,
    student_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    receipt_no VARCHAR(50) UNIQUE NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('success', 'failed', 'pending') DEFAULT 'success',
    FOREIGN KEY (fee_id) REFERENCES student_fees(fee_id) ON DELETE CASCADE,
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
