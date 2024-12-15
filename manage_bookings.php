<?php
session_start();
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("location: index.php");
    exit;
}

$error = '';
$success = '';

// Handle booking deletion
if (isset($_GET['delete_id'])) {
    $booking_id = $_GET['delete_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get booking details first
        $sql = "SELECT movie_id, seats FROM bookings WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $booking = $result->fetch_assoc();
            
            // Delete the booking
            $sql = "DELETE FROM bookings WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            
            // Update available seats
            $sql = "UPDATE movies SET available_seats = available_seats + ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $booking['seats'], $booking['movie_id']);
            $stmt->execute();
            
            $conn->commit();
            $success = "Booking deleted successfully!";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error deleting booking: " . $e->getMessage();
    }
}

// Fetch all bookings with movie and user details
$sql = "SELECT b.*, m.title as movie_title, m.screening_time, m.location, u.username 
        FROM bookings b 
        JOIN movies m ON b.movie_id = m.id 
        JOIN users u ON b.user_id = u.id 
        ORDER BY b.booking_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Movie Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Movie Booking</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-item nav-link" href="index.php">Movies</a>
                <a class="nav-item nav-link active" href="manage_bookings.php">Manage Bookings</a>
                <a class="nav-item nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Bookings</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Movie</th>
                        <th>User</th>
                        <th>Seats</th>
                        <th>Screening Time</th>
                        <th>Location</th>
                        <th>Booking Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['movie_title']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['seats']); ?></td>
                                <td><?php echo htmlspecialchars($row['screening_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                                <td>
                                    <a href="?delete_id=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this booking? This will return the seats to available.')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 