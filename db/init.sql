CREATE DATABASE IF NOT EXISTS university_courses;

USE university_courses;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS course_dependencies;
DROP TABLE IF EXISTS courses;

CREATE TABLE IF NOT EXISTS courses (
    user_id INT NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    credits INT NOT NULL,
    department ENUM('Mathematics', 'Software Technologies', 'Informatics', 'Database', 'English', 'Soft Skills', 'Other') NOT NULL DEFAULT 'Other',
    source_type ENUM('system', 'imported', 'added') NOT NULL DEFAULT 'added',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, course_code),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_dependencies (
    course_user_id INT NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    prereq_user_id INT NOT NULL,
    prerequisite_course_code VARCHAR(20) NOT NULL,
    PRIMARY KEY (course_user_id, course_code, prereq_user_id, prerequisite_course_code),
    FOREIGN KEY (course_user_id, course_code) REFERENCES courses(user_id, course_code) ON DELETE CASCADE,
    FOREIGN KEY (prereq_user_id, prerequisite_course_code) REFERENCES courses(user_id, course_code) ON DELETE CASCADE
);

DELETE FROM course_dependencies WHERE course_user_id = 1;
DELETE FROM courses WHERE user_id = 1;
INSERT INTO users (id, username, password_hash) VALUES
(1, 'system', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX')
ON DUPLICATE KEY UPDATE username = username;

INSERT INTO courses (course_code, user_id, course_name, credits, department, source_type) VALUES
('MATH101', 1, 'Calculus I', 6, 'Mathematics', 'system'),
('MATH102', 1, 'Linear Algebra', 5, 'Mathematics', 'system'),
('MATH201', 1, 'Calculus II', 6, 'Mathematics', 'system'),
('MATH202', 1, 'Discrete Mathematics', 5, 'Mathematics', 'system'),

('SWE101', 1, 'Introduction to Programming', 6, 'Software Technologies', 'system'),
('SWE102', 1, 'Object-Oriented Programming', 6, 'Software Technologies', 'system'),
('SWE201', 1, 'Data Structures and Algorithms', 6, 'Software Technologies', 'system'),
('SWE202', 1, 'Software Engineering Principles', 5, 'Software Technologies', 'system'),

('INF101', 1, 'Computer Architecture', 5, 'Informatics', 'system'),
('INF102', 1, 'Operating Systems', 5, 'Informatics', 'system'),
('INF201', 1, 'Computer Networks', 5, 'Informatics', 'system'),
('INF202', 1, 'System Programming', 6, 'Informatics', 'system'),

('DB101', 1, 'Introduction to Databases', 5, 'Database', 'system'),
('DB102', 1, 'SQL Programming', 4, 'Database', 'system'),
('DB201', 1, 'Database Design', 5, 'Database', 'system'),
('DB202', 1, 'Advanced Database Systems', 6, 'Database', 'system'),

('ENG101', 1, 'Academic Writing', 4, 'English', 'system'),
('ENG102', 1, 'Technical Communication', 3, 'English', 'system'),
('ENG201', 1, 'Professional Writing', 4, 'English', 'system'),

('SSK101', 1, 'Team Collaboration', 3, 'Soft Skills', 'system'),
('SSK102', 1, 'Project Management', 4, 'Soft Skills', 'system'),
('SSK201', 1, 'Leadership Skills', 4, 'Soft Skills', 'system');

INSERT INTO course_dependencies (course_user_id, course_code, prereq_user_id, prerequisite_course_code) VALUES
(1, 'MATH201', 1, 'MATH101'),
(1, 'MATH202', 1, 'MATH101'),

(1, 'SWE102', 1, 'SWE101'),
(1, 'SWE201', 1, 'SWE102'),
(1, 'SWE202', 1, 'SWE201'),

(1, 'INF102', 1, 'INF101'),
(1, 'INF201', 1, 'INF102'),
(1, 'INF202', 1, 'INF102'),

(1, 'DB102', 1, 'DB101'),
(1, 'DB201', 1, 'DB102'),
(1, 'DB202', 1, 'DB201'),

(1, 'ENG201', 1, 'ENG101'),

(1, 'SSK201', 1, 'SSK101');