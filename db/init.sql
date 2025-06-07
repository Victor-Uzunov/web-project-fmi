-- Select the database
USE university_courses;

-- Create the users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Store hashed passwords
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the courses table (modified to include user_id)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,              -- Link to the user who created the course
    course_code VARCHAR(20) NOT NULL,  -- UNIQUE constraint removed for now,
                                       -- as different users might use the same code.
                                       -- If code must be unique across all courses,
                                       -- consider a composite unique key (user_id, course_code)
    course_name VARCHAR(255) NOT NULL,
    credits INT NOT NULL,
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
