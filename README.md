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
  - **Frontend Enhancements**:
    - Bootstrap 5 for modern UI
    - Responsive design
    - Interactive confirmations
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
   - The `create_admin.php` page is shown during first start
   - Once an admin account exists, this page will be disabled
   - Use the provided admin key (`key=webfejlesztes2024`)
   - Choose your admin username and password
   - Password must be at least 6 characters
   - After creation, you can log in with these credentials
   - Additional admin accounts must be created through the registration page and then assigned administrator roles via the manage users page.

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

## Security Features

1. **Authentication**

   - **Secure Password Hashing**: User passwords are securely hashed using PHP's `password_hash()` function.
     - Registration implementation in [`public/register.php`](public/register.php).
     - Password verification during login using `password_verify()` in [`public/login.php`](public/login.php).
   - **Session Management**: The system uses PHP sessions to securely track user authentication.
     - Sessions are configured with secure parameters in [`public/login.php`](public/login.php), including `httponly` and `samesite='Strict'`.
     - Session IDs are regenerated upon login using `session_regenerate_id(true)` to prevent session fixation attacks.
   - **Role-Based Access Control**: Access to administrative features is restricted based on user roles.
     - Admin privileges are checked using the `is_admin` session variable.
     - See implementation in [`admin/create_admin.php`](admin/create_admin.php) and [`admin/`](admin/).

2. **Data Protection**

   - **Prepared Statements**: All SQL queries use prepared statements with bound parameters to prevent SQL injection attacks.
     - Implemented in [`public/login.php`](public/login.php), [`public/register.php`](public/register.php), and [`admin/create_admin.php`](admin/create_admin.php).
   - **Input Validation**: User inputs are validated and sanitized before processing.
     - Example: Ensuring passwords match and meet length requirements in [`public/register.php`](public/register.php).
   - **Cross-Site Scripting (XSS) Prevention**: Outputs are escaped using `htmlspecialchars()` to prevent XSS attacks.
     - Applied when displaying user-generated content.

3. **Session Security**

   - **Secure Session Cookies**: Sessions are configured with `session_set_cookie_params()` to enhance security.
     - Parameters include `'httponly' => true` and `'samesite' => 'Strict'` in [`public/login.php`](public/login.php).
   - **Session Fixation Prevention**: Sessions are regenerated upon login to prevent fixation attacks.
     - Implemented using `session_regenerate_id(true)` in [`public/login.php`](public/login.php).

4. **Access Control**

   - **Protected Routes**: Unauthorized access to restricted pages redirects users appropriately.
     - Checks are performed at the beginning of scripts like [`admin/create_admin.php`](admin/create_admin.php).
   - **Admin Key Verification**: Admin account creation requires a valid admin key.
     - Implemented in [`admin/manage_users.php`](admin/manage_users.php) to prevent unauthorized admin access.

5. **Password Policies**

   - **Minimum Password Length**: Passwords must be at least 6 characters long.
     - Enforced in [`public/register.php`](public/register.php) and [`admin/create_admin.php`](admin/create_admin.php).

## Project Structure

```
movie-booking-system/
├── admin/
│   ├── add_movie.php      # Movie creation interface
│   ├── create_admin.php   # Admin account setup
│   ├── delete_movie.php   # Movie deletion handler  
│   ├── edit_movie.php     # Movie editing interface
│   └── manage_users.php   # User management dashboard
│
├── bookings/
│   ├── book_ticket.php    # Ticket booking interface
│   ├── manage_bookings.php # Admin booking management
│   ├── movie_bookings.php # Per-movie booking view
│   └── my_bookings.php    # User booking history
│
├── config/
│   └── config.php         # Database and system configuration
│
├── public/
│   ├── login.php         # User authentication
│   ├── logout.php        # Session termination
│   └── register.php      # New user registration
│
├── index.php             # Main movie listing page
├── LICENSE              # Apache 2.0 license
└── README.md            # Project documentation
```

## Technologies Used

- **Backend**: 
  - PHP 7+
  - MySQL Database

- **Frontend**: 
  - HTML5
  - CSS3
  - Bootstrap 5
  - Responsive Design

- **Security**: 
  - PHP Sessions
  - Password Hashing
  - Prepared Statements
  - Input Validation

## Support and Contribution

- Report issues through the issue tracker
- Follow the coding standards when contributing
- Write tests for new features
- Document any changes or additions

## License

This project licensed under the Apache License 2.0.