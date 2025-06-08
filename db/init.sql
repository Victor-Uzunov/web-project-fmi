-- Select the database
USE university_courses;

-- Create the users table (if it doesn't exist)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Store hashed passwords
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS course_dependencies;
DROP TABLE IF EXISTS courses;

-- Create the courses table (department as ENUM, unique per user_id and course_code)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,              -- Link to the user who created the course (could be a regular user or 'system' user)
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    credits INT NOT NULL,
    department ENUM('Mathematics', 'Software Technologies', 'Informatics', 'Database', 'English', 'Soft Skills', 'Other') NOT NULL DEFAULT 'Other',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (user_id, course_code) -- Ensure course code is unique PER USER (including the 'system' user)
);

-- Create the course_dependencies table for prerequisites
CREATE TABLE IF NOT EXISTS course_dependencies (
    course_id INT NOT NULL,
    prerequisite_course_id INT NOT NULL,
    PRIMARY KEY (course_id, prerequisite_course_id), -- Composite primary key
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_course_id) REFERENCES courses(id) ON DELETE CASCADE
);

INSERT IGNORE INTO users (username, password_hash) VALUES
('system', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); -- Dummy hash, not used for login

-- Admin dependencies
INSERT IGNORE INTO course_dependencies (course_id, prerequisite_course_id) VALUES
((SELECT id FROM courses WHERE course_code = 'ADM201' AND user_id = (SELECT id FROM users WHERE username = 'admin')),
 (SELECT id FROM courses WHERE course_code = 'ADM101' AND user_id = (SELECT id FROM users WHERE username = 'admin')));


-- Insert Global Courses for 'system' user (ID derived from SELECT)
-- These courses are designed to form a large, interconnected core curriculum
INSERT IGNORE INTO courses (user_id, course_code, course_name, credits, department) VALUES
((SELECT id FROM users WHERE username = 'system'), 'GLO101', 'Global Intro to CS', 4, 'Software Technologies'),
((SELECT id FROM users WHERE username = 'system'), 'GLO102', 'Global Calculus I', 4, 'Mathematics'),
((SELECT id FROM users WHERE username = 'system'), 'GLO103', 'Global Physics I', 4, 'Other'),
((SELECT id FROM users WHERE username = 'system'), 'GLO201', 'Global Data Structures', 4, 'Informatics'),
((SELECT id FROM users WHERE username = 'system'), 'GLO202', 'Global Linear Algebra', 3, 'Mathematics'),
((SELECT id FROM users WHERE username = 'system'), 'GLO203', 'Global Object-Oriented Design', 4, 'Software Technologies'),
((SELECT id FROM users WHERE username = 'system'), 'GLO301', 'Global Algorithms', 4, 'Informatics'),
((SELECT id FROM users WHERE username = 'system'), 'GLO302', 'Global Probability & Stats', 3, 'Mathematics'),
((SELECT id FROM users WHERE username = 'system'), 'GLO303', 'Global Database Systems', 3, 'Database'),
((SELECT id FROM users WHERE username = 'system'), 'GLO401', 'Global Machine Learning', 4, 'Informatics'),
((SELECT id FROM users WHERE username = 'system'), 'GLO402', 'Global Compilers', 4, 'Software Technologies'),
((SELECT id FROM users WHERE username = 'system'), 'GLO403', 'Global Software Engineering', 3, 'Software Technologies'),
((SELECT id FROM users WHERE username = 'system'), 'GLO501', 'Global AI Ethics', 2, 'Soft Skills'),
((SELECT id FROM users WHERE username = 'system'), 'GLO502', 'Global Distributed Systems', 4, 'Software Technologies');


-- Add Dependencies for Global Courses
INSERT IGNORE INTO course_dependencies (course_id, prerequisite_course_id) VALUES
-- GLO201 (Data Structures) depends on GLO101 (Intro to CS) and GLO102 (Calc I)
((SELECT id FROM courses WHERE course_code = 'GLO201' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO101' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO201' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO102' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO202 (Linear Algebra) depends on GLO102 (Calc I)
((SELECT id FROM courses WHERE course_code = 'GLO202' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO102' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO203 (OOD) depends on GLO101 (Intro to CS)
((SELECT id FROM courses WHERE course_code = 'GLO203' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO101' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO301 (Algorithms) depends on GLO201 (Data Structures) and GLO202 (Linear Algebra)
((SELECT id FROM courses WHERE course_code = 'GLO301' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO201' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO301' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO202' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO302 (Prob & Stats) depends on GLO102 (Calc I)
((SELECT id FROM courses WHERE course_code = 'GLO302' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO102' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO303 (Database Systems) depends on GLO101 (Intro to CS) and GLO103 (Physics)
((SELECT id FROM courses WHERE course_code = 'GLO303' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO101' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO303' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO103' AND user_id = (SELECT id FROM users WHERE username = 'system'))), -- Cross-disciplinary

-- GLO401 (Machine Learning) depends on GLO301 (Algorithms) and GLO302 (Prob & Stats)
((SELECT id FROM courses WHERE course_code = 'GLO401' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO301' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO401' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO302' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO402 (Compilers) depends on GLO301 (Algorithms) and GLO203 (OOD)
((SELECT id FROM courses WHERE course_code = 'GLO402' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO301' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO402' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO203' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO403 (Software Engineering) depends on GLO203 (OOD) and GLO303 (DB Systems)
((SELECT id FROM courses WHERE course_code = 'GLO403' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO203' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO403' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO303' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO501 (AI Ethics) depends on GLO401 (Machine Learning) and GLO103 (Physics)
((SELECT id FROM courses WHERE course_code = 'GLO501' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO401' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO501' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO103' AND user_id = (SELECT id FROM users WHERE username = 'system'))),

-- GLO502 (Distributed Systems) depends on GLO301 (Algorithms) and GLO403 (Software Engineering)
((SELECT id FROM courses WHERE course_code = 'GLO502' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO301' AND user_id = (SELECT id FROM users WHERE username = 'system'))),
((SELECT id FROM courses WHERE course_code = 'GLO502' AND user_id = (SELECT id FROM users WHERE username = 'system')),
 (SELECT id FROM courses WHERE course_code = 'GLO403' AND user_id = (SELECT id FROM users WHERE username = 'system')));

SELECT 'init.sql script finished successfully with extensive Global and Victor data';
