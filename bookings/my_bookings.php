<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("location: ../login.php");
    exit;
}

// Fetch user's bookings with movie details
$sql = "SELECT b.*, m.title as movie_title, m.screening_time, m.location 
        FROM bookings b 
        JOIN movies m ON b.movie_id = m.id 
        WHERE b.user_id = ? 
        ORDER BY m.screening_time ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Movie Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Movie Booking</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-item nav-link" href="../index.php">Movies</a>
                <a class="nav-item nav-link active" href="my_bookings.php">My Bookings</a>
                <div class="dropdown">
                        <button class="btn btn-secondary align-items-center bg-dark btn-link" style="color: white;" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li>
                                <div class="dropdown-item d-flex justify-content-between align-items-center">
                                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                    <?php if ($_SESSION['is_admin']): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">User</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="public/logout.php">Logout</a></li>
                        </ul>
                    </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>My Bookings</h2>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Movie</th>
                        <th>Screening Time</th>
                        <th>Location</th>
                        <th>Number of Seats</th>
                        <th>Booking Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['movie_title']); ?></td>
                                <td><?php echo htmlspecialchars($row['screening_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo htmlspecialchars($row['seats']); ?></td>
                                <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                                <td>
                                    <?php 
                                    $screening_time = strtotime($row['screening_time']);
                                    $current_time = time();
                                    if ($screening_time < $current_time):
                                    ?>
                                        <span class="badge bg-secondary">Past</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Upcoming</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">You haven't made any bookings yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 