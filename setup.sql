-- ============================================================
-- Student Result Management System - Database Setup
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_result_db;
USE student_result_db;

-- -----------------------------------------------
-- Table: admin
-- Stores admin login credentials
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- Table: students
-- Stores student personal and login info
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    course VARCHAR(100) NOT NULL,
    semester INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- Table: subjects
-- Stores subject info with max marks
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_name VARCHAR(100) NOT NULL,
    max_marks INT NOT NULL DEFAULT 100,
    pass_marks INT NOT NULL DEFAULT 35,
    semester INT NOT NULL,
    course VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- Table: results
-- Stores marks for each student per subject
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    marks_obtained INT NOT NULL DEFAULT 0,
    exam_year VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_result (student_id, subject_id, exam_year)
);

-- -----------------------------------------------
-- Default Admin Account
-- Username: admin | Password: admin123
-- -----------------------------------------------
INSERT INTO admin (username, password, full_name)
VALUES ('admin', MD5('admin123'), 'College Administrator');

-- -----------------------------------------------
-- Sample Students (password = roll number)
-- -----------------------------------------------
INSERT INTO students (roll_number, full_name, email, phone, course, semester, password) VALUES
('BCA2024001', 'Ravi Kumar Sharma', 'ravi@example.com', '9876543210', 'BCA', 2, MD5('BCA2024001')),
('BCA2024002', 'Priya Singh', 'priya@example.com', '9876543211', 'BCA', 2, MD5('BCA2024002')),
('MCA2024001', 'Amit Patel', 'amit@example.com', '9876543212', 'MCA', 1, MD5('MCA2024001'));

-- -----------------------------------------------
-- Sample Subjects
-- -----------------------------------------------
INSERT INTO subjects (subject_code, subject_name, max_marks, pass_marks, semester, course) VALUES
('BCA201', 'Data Structures', 100, 35, 2, 'BCA'),
('BCA202', 'Database Management', 100, 35, 2, 'BCA'),
('BCA203', 'Operating Systems', 100, 35, 2, 'BCA'),
('BCA204', 'Computer Networks', 100, 35, 2, 'BCA'),
('MCA101', 'Advanced Algorithms', 100, 35, 1, 'MCA'),
('MCA102', 'Software Engineering', 100, 35, 1, 'MCA');

-- -----------------------------------------------
-- Sample Results
-- -----------------------------------------------
INSERT INTO results (student_id, subject_id, marks_obtained, exam_year) VALUES
(1, 1, 82, '2024'),
(1, 2, 76, '2024'),
(1, 3, 68, '2024'),
(1, 4, 90, '2024'),
(2, 1, 55, '2024'),
(2, 2, 62, '2024'),
(2, 3, 48, '2024'),
(2, 4, 71, '2024');
