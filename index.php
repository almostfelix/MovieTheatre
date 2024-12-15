<?php
session_start();
require_once __DIR__ . '/config/config.php';

// First check if any admin exists
if (!checkAdminExists($conn)) {
    header("location: admin/create_admin.php");
    exit;
}

// Then check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("location: public/login.php");
    exit;
}

// Fetch all movies
$sql = "SELECT m.*, COUNT(b.id) as total_bookings 
        FROM movies m 
        LEFT JOIN bookings b ON m.id = b.movie_id 
        GROUP BY m.id 
        ORDER BY m.screening_time";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Movie Booking</a>
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['is_admin']): ?>
                        <a class="nav-item nav-link" href="bookings/manage_bookings.php">Manage Bookings</a>
                        <a class="nav-item nav-link" href="admin/manage_users.php">Manage Users</a>
                    <?php else: ?>
                        <a class="nav-item nav-link" href="bookings/my_bookings.php">My Bookings</a>
                    <?php endif; ?>
                    <span class="nav-item nav-link text-light">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 
                    <?php echo ($_SESSION['is_admin'] ? '(Admin)' : '(User)'); ?></span>
                    <a class="nav-item nav-link" href="public/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-item nav-link" href="public/login.php">Login</a>
                    <a class="nav-item nav-link" href="public/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Available Movies</h2>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="admin/add_movie.php" class="btn btn-primary mb-3">Add New Movie</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Screening Time</th>
                        <th>Location</th>
                        <th>Available Seats</th>
                        <th>Total Bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['screening_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo htmlspecialchars($row['available_seats']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_bookings']); ?></td>
                                <td>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="bookings/book_ticket.php?movie_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Book Ticket</a>
                                        <a href="bookings/movie_bookings.php?movie_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View Bookings</a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                        <a href="admin/edit_movie.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="admin/delete_movie.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure? This will delete all bookings for this movie.')">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No movies available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>