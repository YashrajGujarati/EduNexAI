

CREATE DATABASE IF NOT EXISTS student_ai_system;

USE student_ai_system;



CREATE TABLE users (

id INT AUTO_INCREMENT PRIMARY KEY,

name VARCHAR(100) NOT NULL,

email VARCHAR(100) UNIQUE NOT NULL,

password VARCHAR(255) NOT NULL,

role ENUM('admin','faculty','student') NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);



CREATE TABLE students (

student_id INT AUTO_INCREMENT PRIMARY KEY,

user_id INT NOT NULL,

class VARCHAR(50),

roll_number VARCHAR(20),

attendance DECIMAL(5,2),

FOREIGN KEY (user_id) REFERENCES users(id)

ON DELETE CASCADE

);


CREATE TABLE subjects (

subject_id INT AUTO_INCREMENT PRIMARY KEY,

subject_name VARCHAR(100),

faculty_id INT,

FOREIGN KEY (faculty_id)

REFERENCES users(id)

ON DELETE SET NULL

);



CREATE TABLE marks (

mark_id INT AUTO_INCREMENT PRIMARY KEY,

student_id INT,

subject_id INT,

internal_marks INT,

external_marks INT,

total_marks INT,

FOREIGN KEY(student_id)

REFERENCES students(student_id)

ON DELETE CASCADE,

FOREIGN KEY(subject_id)

REFERENCES subjects(subject_id)

ON DELETE CASCADE

);



CREATE TABLE attendance (

attendance_id INT AUTO_INCREMENT PRIMARY KEY,

student_id INT,

subject_id INT,

present_days INT,

total_days INT,

percentage DECIMAL(5,2),

FOREIGN KEY(student_id)

REFERENCES students(student_id)

ON DELETE CASCADE,

FOREIGN KEY(subject_id)

REFERENCES subjects(subject_id)

ON DELETE CASCADE

);



CREATE TABLE predictions (

prediction_id INT AUTO_INCREMENT PRIMARY KEY,

student_id INT,

predicted_score DECIMAL(5,2),

result VARCHAR(20),

risk_level VARCHAR(20),

FOREIGN KEY(student_id)

REFERENCES students(student_id)

ON DELETE CASCADE

);
