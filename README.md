# Movie Booking System

A comprehensive web-based movie ticket booking system built with PHP, MySQL, and Bootstrap. The system allows users to book movie tickets and administrators to manage movies and bookings.

## Features

### Level 2 Requirements ✓
- **Database Table Display**
  - Movies table data displayed in a clean, tabular format on the main page
  - Read operations implemented for viewing movie details
  - Responsive table design using Bootstrap

### Level 3 Requirements ✓
- **Complete CRUD Operations**
  - **Create**: Add new movies (admin)
  - **Read**: View movie listings and details
  - **Update**: Edit movie information (admin)
  - **Delete**: Remove movies and their associated bookings (admin)

### Level 4 Requirements ✓
- **Security Implementation**
  - Session-based authentication system
  - Role-based access control (Admin/User)
  - Secure password hashing
  - Protected routes and operations
  - Login/Register functionality
  - SQL injection prevention using prepared statements

### Level 5 Requirements ✓
- **Advanced Features**
  - **Multiple Table Relations**:
    - Movies table with Bookings table (one-to-many)
    - Users table with Bookings table (one-to-many)
    - Complex JOIN operations for booking management
  - **Frontend Enhancements**:
    - Bootstrap 5 for modern UI
    - Responsive design
    - Interactive confirmations
    - Status badges for bookings
  - **Backend Features**:
    - Transaction management
    - Data validation
    - Seat availability tracking
    - Booking conflict prevention

## Quick Start Guide

1. **Environment Setup**
   - Install XAMPP, WAMP, or similar PHP development environment
   - PHP version 7.0 or higher required
   - MySQL 5.7 or higher

2. **Database Setup**
   ```sql
   CREATE DATABASE movie_booking;
   USE movie_booking;
   ```

3. **Project Installation**
   - Clone or download the project to your web server directory
   - Configure database connection in `config.php`:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'movie_booking');
     ```

4. **Admin Account Setup**
   - Visit `create_admin.php` in your browser
   - Use the following details:
     - Admin Key: `webfejlesztes2024`
     - Choose your admin username and password
     - Password must be at least 6 characters
   - After creation, you can log in with these credentials

5. **Testing the System**
   - Create a regular user account through the registration page
   - Log in as admin to add movies
   - Test booking functionality with regular user account

## Database Structure

### Movies Table
```sql
CREATE TABLE movies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    screening_time DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    available_seats INT NOT NULL DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Bookings Table
```sql
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    movie_id INT NOT NULL,
    user_id INT NOT NULL,
    seats INT NOT NULL,
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);
```

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## User Roles and Permissions

### Admin Users
- View all movies and bookings
- Add new movies
- Edit existing movies
- Delete movies
- Manage bookings
- View booking details for all users
- Delete bookings

### Regular Users
- View available movies
- Make bookings
- View their own bookings
- See booking status (upcoming/past)

## Security Features

1. **Authentication**
   - Secure password hashing using PHP's password_hash()
   - Session-based user tracking
   - Protected routes with role verification
   - Admin key requirement for admin creation

2. **Data Protection**
   - Prepared statements for all SQL queries
   - Input validation and sanitization
   - XSS prevention through htmlspecialchars
   - CSRF protection through session tokens

3. **Transaction Management**
   - Atomic operations for bookings
   - Seat availability synchronization
   - Booking conflict prevention
   - Data consistency maintenance

## File Structure and Purpose

```
├── config.php           # Database and system configuration
├── index.php           # Main movie listing page
├── login.php           # User authentication
├── register.php        # New user registration
├── logout.php          # Session termination
├── create_admin.php    # Admin account creation
├── add_movie.php       # Movie creation (admin)
├── edit_movie.php      # Movie modification (admin)
├── delete_movie.php    # Movie removal (admin)
├── book_ticket.php     # Ticket booking
├── my_bookings.php     # User booking history
├── manage_bookings.php # Admin booking management
└── movie_bookings.php  # Per-movie booking view
```

## Technologies Used

- **Backend**: 
  - PHP 7+ (OOP Style)
  - MySQL Database
  - PDO/MySQLi

- **Frontend**: 
  - HTML5
  - CSS3
  - Bootstrap 5
  - JavaScript
  - Responsive Design

- **Security**: 
  - PHP Sessions
  - Password Hashing
  - Prepared Statements
  - Input Validation

## Development Notes

1. **Database Considerations**
   - All tables use InnoDB engine for transaction support
   - Foreign key constraints maintain data integrity
   - Indexes on frequently queried columns

2. **Security Implementation**
   - All user inputs are validated and sanitized
   - Passwords are hashed using secure algorithms
   - Session management prevents unauthorized access

3. **Performance Optimizations**
   - Efficient database queries with proper indexing
   - Minimal database connections
   - Optimized JOIN operations

## Support and Contribution

- Report issues through the issue tracker
- Follow the coding standards when contributing
- Write tests for new features
- Document any changes or additions

## License

This project licensed under the Apache License 2.0.