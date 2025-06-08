-- Select the database
USE university_courses;

-- Create the users table (if it doesn't exist)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Store hashed passwords
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the courses table (modified department to ENUM, unique per user_id and course_code)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,              -- Link to the user who created the course
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    credits INT NOT NULL,
    department ENUM('Mathematics', 'Software Technologies', 'Informatics', 'Database', 'English', 'Soft Skills', 'Other') NOT NULL DEFAULT 'Other',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (user_id, course_code) -- Ensure course code is unique PER USER
);

-- Create the course_dependencies table for prerequisites
CREATE TABLE IF NOT EXISTS course_dependencies (
    course_id INT NOT NULL,
    prerequisite_course_id INT NOT NULL,
    PRIMARY KEY (course_id, prerequisite_course_id), -- Composite primary key
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_course_id) REFERENCES courses(id) ON DELETE CASCADE
);
