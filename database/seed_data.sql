-- EduNexAI Comprehensive Seed Data Script
-- Inserts sample users, students, faculty, subjects, marks, attendance, assignments, fee ledgers, and AI prediction logs.

USE student_ai_system;

SET FOREIGN_KEY_CHECKS = 0;

-- Clean existing data except admin user if exists
TRUNCATE TABLE fee_payments;
TRUNCATE TABLE student_fees;
TRUNCATE TABLE assignment_submissions;
TRUNCATE TABLE assignments;
TRUNCATE TABLE prediction_history;
TRUNCATE TABLE predictions;
TRUNCATE TABLE attendance;
TRUNCATE TABLE marks;
TRUNCATE TABLE subjects;
TRUNCATE TABLE students;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

-- Password Hash for all demo accounts: "Password@123" -> $2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036
-- Password Hash for Admin: "Admin@123" -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- 1. Insert Users (1 Admin, 3 Faculty, 10 Students)
INSERT INTO users (id, name, email, password, role, enrollment_no, first_login) VALUES
(1, 'System Administrator', 'admin@edunexai.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 0),
(2, 'Prof. Rajesh Sharma', 'prof.sharma@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'faculty', NULL, 0),
(3, 'Dr. Ananya Verma', 'prof.verma@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'faculty', NULL, 0),
(4, 'Prof. Vikram Gupta', 'prof.gupta@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'faculty', NULL, 0),

-- Students
(5, 'Aarav Mehta', 'aarav@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026001', 0),
(6, 'Priya Patel', 'priya@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026002', 0),
(7, 'Rohan Deshmukh', 'rohan@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026003', 0),
(8, 'Ananya Sen', 'ananya@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026004', 0),
(9, 'Kabir Nair', 'kabir@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026005', 0),
(10, 'Sneha Joshi', 'sneha@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026006', 0),
(11, 'Aditya Rao', 'aditya@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026007', 0),
(12, 'Diya Kapoor', 'diya@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026008', 0),
(13, 'Siddharth Iyer', 'siddharth@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026009', 0),
(14, 'Neha Redy', 'neha@edunexai.com', '$2y$10$e.w2cQd.j7v14tF/OxeKue.e8uL84wJq2W2w0aG52y21588647036', 'student', 'EN2026010', 0);

-- 2. Insert Students Records
INSERT INTO students (student_id, user_id, class, roll_number, attendance) VALUES
(1, 5, 'BTech-CS', 'CS-101', 94.50),
(2, 6, 'BTech-CS', 'CS-102', 88.00),
(3, 7, 'BTech-AI', 'AI-201', 76.50),
(4, 8, 'BTech-AI', 'AI-202', 91.20),
(5, 9, 'BTech-IT', 'IT-301', 58.00),
(6, 10, 'BTech-CS', 'CS-103', 96.00),
(7, 11, 'BTech-IT', 'IT-302', 64.00),
(8, 12, 'BTech-AI', 'AI-203', 82.50),
(9, 13, 'BTech-CS', 'CS-104', 52.00),
(10, 14, 'BTech-IT', 'IT-303', 89.00);

-- 3. Insert Subjects
INSERT INTO subjects (subject_id, subject_name, subject_code, faculty_id) VALUES
(1, 'Data Structures & Algorithms', 'CS301', 2),
(2, 'Machine Learning & AI', 'AI401', 3),
(3, 'Database Management Systems', 'CS302', 2),
(4, 'Web Application Development', 'IT301', 4),
(5, 'Computer Networks & Security', 'IT302', 4),
(6, 'Operating Systems', 'CS303', 2);

-- 4. Insert Marks for Students
INSERT INTO marks (mark_id, student_id, subject_id, internal_marks, external_marks, total_marks, exam_date) VALUES
-- Aarav Mehta (High Performer)
(1, 1, 1, 28, 65, 93, '2026-05-10'),
(2, 1, 2, 29, 66, 95, '2026-05-12'),
(3, 1, 3, 27, 63, 90, '2026-05-15'),
-- Priya Patel (Good)
(4, 2, 1, 24, 58, 82, '2026-05-10'),
(5, 2, 3, 25, 60, 85, '2026-05-15'),
(6, 2, 6, 23, 56, 79, '2026-05-18'),
-- Rohan Deshmukh (Average)
(7, 3, 2, 20, 48, 68, '2026-05-12'),
(8, 3, 4, 21, 50, 71, '2026-05-16'),
-- Ananya Sen (High)
(9, 4, 2, 28, 64, 92, '2026-05-12'),
(10, 4, 4, 27, 62, 89, '2026-05-16'),
-- Kabir Nair (Poor Risk)
(11, 5, 4, 12, 32, 44, '2026-05-16'),
(12, 5, 5, 14, 30, 44, '2026-05-20'),
-- Sneha Joshi (Top)
(13, 6, 1, 30, 68, 98, '2026-05-10'),
(14, 6, 3, 29, 67, 96, '2026-05-15'),
-- Aditya Rao (Average)
(15, 7, 4, 18, 42, 60, '2026-05-16'),
(16, 7, 5, 17, 40, 57, '2026-05-20'),
-- Diya Kapoor (Good)
(17, 8, 2, 24, 57, 81, '2026-05-12'),
-- Siddharth Iyer (High Risk / Fail)
(18, 9, 1, 10, 25, 35, '2026-05-10'),
(19, 9, 6, 11, 28, 39, '2026-05-18'),
-- Neha Redy (Good)
(20, 10, 4, 26, 61, 87, '2026-05-16');

-- 5. Insert Attendance Records
INSERT INTO attendance (student_id, subject_id, present_days, total_days, percentage) VALUES
(1, 1, 47, 50, 94.00),
(1, 2, 48, 50, 96.00),
(1, 3, 46, 50, 92.00),
(2, 1, 44, 50, 88.00),
(2, 3, 45, 50, 90.00),
(3, 2, 38, 50, 76.00),
(3, 4, 39, 50, 78.00),
(4, 2, 46, 50, 92.00),
(5, 4, 29, 50, 58.00),
(5, 5, 28, 50, 56.00),
(6, 1, 49, 50, 98.00),
(6, 3, 48, 50, 96.00),
(7, 4, 32, 50, 64.00),
(8, 2, 41, 50, 82.00),
(9, 1, 26, 50, 52.00),
(10, 4, 45, 50, 90.00);

-- 6. Insert Predictions & History
INSERT INTO predictions (student_id, predicted_score, result, risk_level) VALUES
(1, 93.00, 'Excellent', 'None'),
(2, 82.00, 'Good', 'Low'),
(3, 70.00, 'Good', 'Low'),
(4, 91.00, 'Excellent', 'None'),
(5, 44.00, 'Poor', 'High'),
(6, 97.00, 'Excellent', 'None'),
(7, 58.00, 'Average', 'Medium'),
(8, 81.00, 'Good', 'Low'),
(9, 37.00, 'Poor', 'High'),
(10, 87.00, 'Good', 'Low');

INSERT INTO prediction_history (student_id, predicted_score, result, risk_level) VALUES
(1, 93.00, 'Excellent', 'None'),
(2, 82.00, 'Good', 'Low'),
(3, 70.00, 'Good', 'Low'),
(4, 91.00, 'Excellent', 'None'),
(5, 44.00, 'Poor', 'High'),
(6, 97.00, 'Excellent', 'None'),
(7, 58.00, 'Average', 'Medium'),
(8, 81.00, 'Good', 'Low'),
(9, 37.00, 'Poor', 'High'),
(10, 87.00, 'Good', 'Low');

-- 7. Insert Assignments
INSERT INTO assignments (assignment_id, title, description, subject_id, faculty_id, due_date, total_marks) VALUES
(1, 'Binary Search Tree & Graph Traversal Implementation', 'Write clean C++/Java code implementing BST insert, search, delete, BFS, and DFS operations.', 1, 2, '2026-10-15 23:59:00', 50),
(2, 'Machine Learning Supervised Classifier Project', 'Train a Decision Tree and Random Forest model on the provided dataset and submit report with metrics.', 2, 3, '2026-10-20 23:59:00', 100),
(3, 'Database ER Model & SQL Normalization', 'Design an normalized 3NF relational schema for an e-commerce platform and write 10 complex SQL queries.', 3, 2, '2026-10-18 23:59:00', 50),
(4, 'Responsive Full-Stack Portfolio App', 'Build a modern web app using HTML5, CSS3, JS, and PHP with MySQL integration.', 4, 4, '2026-10-25 23:59:00', 100);

-- 8. Insert Assignment Submissions
INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, submitted_at, status, marks_obtained, feedback) VALUES
(1, 1, 'Submitted code for BST and Graph Traversal algorithms with test cases.', '2026-10-10 14:30:00', 'graded', 48, 'Outstanding implementation and clean modular code architecture!'),
(1, 2, 'Completed assignment with C++ solution.', '2026-10-12 11:20:00', 'graded', 42, 'Good attempt. Ensure memory deallocation for dynamic nodes.'),
(2, 4, 'Submitted python notebook containing DecisionTree and RandomForest accuracy report.', '2026-10-14 16:45:00', 'graded', 95, 'Excellent hyperparameter tuning and visualization plots.'),
(3, 1, 'SQL scripts and ER diagrams attached.', '2026-10-11 09:15:00', 'graded', 49, 'Flawless 3NF normalization.');

-- 9. Insert Student Fees
INSERT INTO student_fees (fee_id, student_id, total_fee, paid_fee, due_date, status) VALUES
(1, 1, 50000.00, 50000.00, '2026-11-01', 'paid'),
(2, 2, 50000.00, 50000.00, '2026-11-01', 'paid'),
(3, 3, 50000.00, 25000.00, '2026-10-30', 'partial'),
(4, 4, 50000.00, 50000.00, '2026-11-01', 'paid'),
(5, 5, 50000.00, 0.00, '2026-10-15', 'pending'),
(6, 6, 50000.00, 50000.00, '2026-11-01', 'paid'),
(7, 7, 50000.00, 25000.00, '2026-10-30', 'partial'),
(8, 8, 50000.00, 50000.00, '2026-11-01', 'paid'),
(9, 9, 50000.00, 0.00, '2026-10-15', 'pending'),
(10, 10, 50000.00, 50000.00, '2026-11-01', 'paid');

-- 10. Insert Fee Payments
INSERT INTO fee_payments (payment_id, fee_id, student_id, amount_paid, payment_method, transaction_id, receipt_no, payment_date, payment_status) VALUES
(1, 1, 1, 50000.00, 'UPI', 'TXN982734101', 'RCPT-2026-001', '2026-09-01 10:15:00', 'success'),
(2, 2, 2, 50000.00, 'Credit Card', 'TXN982734102', 'RCPT-2026-002', '2026-09-02 11:30:00', 'success'),
(3, 3, 3, 25000.00, 'Net Banking', 'TXN982734103', 'RCPT-2026-003', '2026-09-05 14:20:00', 'success'),
(4, 4, 4, 50000.00, 'UPI', 'TXN982734104', 'RCPT-2026-004', '2026-09-03 09:45:00', 'success'),
(5, 6, 6, 50000.00, 'UPI', 'TXN982734106', 'RCPT-2026-006', '2026-09-04 15:10:00', 'success'),
(6, 7, 7, 25000.00, 'Debit Card', 'TXN982734107', 'RCPT-2026-007', '2026-09-10 16:50:00', 'success'),
(7, 8, 8, 50000.00, 'UPI', 'TXN982734108', 'RCPT-2026-008', '2026-09-06 12:00:00', 'success'),
(8, 10, 10, 50000.00, 'Net Banking', 'TXN982734110', 'RCPT-2026-010', '2026-09-08 17:30:00', 'success');
