# University Course Manager

A modern web application for managing university courses, built with PHP and MySQL. This application provides an intuitive interface for students and administrators to manage course enrollments, view course relationships, and track academic progress.

## Features

- 🔐 Secure user authentication and authorization
- 📊 Interactive course relationship visualization
- 📝 Course management and enrollment system
- 📈 Course dependency graphs and analytics
- 🔄 Real-time course status updates
- 👥 User role management (Students/Administrators)
- 📱 Responsive design for all devices

## Architecture and Project Structure

The project follows a modular architecture with clear separation of concerns:

```
├── app/                    # Main application directory
│   ├── api/               # API endpoints
│   ├── js/                # JavaScript files
│   ├── templates/         # HTML templates
│   ├── config.php         # Configuration settings
│   ├── auth.php           # Authentication logic
│   └── course_manager.php # Core business logic
├── db/                    # Database initialization scripts
└── docker-compose.yml     # Docker configuration
```

## Prerequisites

- Docker and Docker Compose
- Git
- PHP 8.1 or higher (for local development)
- MySQL 8.0 (for local development)

## Setup and Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/university-course-manager.git
   cd university-course-manager
   ```

2. Start the application using Docker Compose:
   ```bash
   docker-compose up -d
   ```

3. The application will be available at `http://localhost:8000`

### Environment Variables

The following environment variables are used in the Docker setup:

```env
# Database Configuration
MYSQL_ROOT_PASSWORD=your_root_password
MYSQL_DATABASE=university_courses
MYSQL_USER=php_user
MYSQL_PASSWORD=php_password
```

## API Documentation

### Course Graph Data
- **Endpoint**: `/api/courses_graph_data.php`
- **Method**: GET
- **Description**: Retrieves course relationship data for visualization
- **Response**: JSON object containing course nodes and relationships

### My Courses Graph Data
- **Endpoint**: `/api/my_courses_graph_data.php`
- **Method**: GET
- **Description**: Retrieves enrolled courses data for the current user
- **Response**: JSON object containing user's course data

## Docker and Deployment

The application is containerized using Docker with two main services:

1. **PHP Application**
   - PHP 8.1
   - Apache web server
   - Exposed on port 8000

2. **MySQL Database**
   - MySQL 8.0
   - Exposed on port 3307
   - Persistent volume for data storage

To deploy:
```bash
docker-compose up -d
```

## Technologies Used

- **Backend**: PHP 8.1
- **Database**: MySQL 8.0
- **Containerization**: Docker & Docker Compose
- **Frontend**: HTML, CSS, JavaScript
- **Authentication**: Custom PHP authentication system
- **Data Visualization**: JavaScript graph visualization libraries

## Contributing

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Style Guidelines

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Include comments for complex logic
- Update documentation for new features

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Authors

- Victor Uzunov (uzunovvictor@gmail.com)
- Petar Kirilov (petar.kirilov17@gmail.com)
