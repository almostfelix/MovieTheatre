<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$error = '';
$success = '';
$movie = null;

// Get movie details
if (isset($_GET['movie_id'])) {
    $movie_id = $_GET['movie_id'];
    $sql = "SELECT * FROM movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $movie = $result->fetch_assoc();
    } else {
        header("location: index.php");
        exit;
    }
} else {
    header("location: index.php");
    exit;
}

// Handle booking deletion (admin only)
if (isset($_GET['delete_id']) && $_SESSION['is_admin']) {
    $booking_id = $_GET['delete_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get booking details first
        $sql = "SELECT seats FROM bookings WHERE id = ? AND movie_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $movie_id);
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
            $stmt->bind_param("ii", $booking['seats'], $movie_id);
            $stmt->execute();
            
            $conn->commit();
            $success = "Booking deleted successfully!";
            
            // Refresh movie data
            $sql = "SELECT * FROM movies WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $movie_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $movie = $result->fetch_assoc();
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error deleting booking: " . $e->getMessage();
    }
}

// Fetch bookings for this movie
if ($_SESSION['is_admin']) {
    // Admin sees all bookings
    $sql = "SELECT b.*, u.username 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.movie_id = ? 
            ORDER BY b.booking_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
} else {
    // Users see only their bookings
    $sql = "SELECT b.*, u.username 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.movie_id = ? AND b.user_id = ? 
            ORDER BY b.booking_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $movie_id, $_SESSION['user_id']);
}

$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Bookings - <?php echo htmlspecialchars($movie['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Movie Booking</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-item nav-link" href="index.php">Movies</a>
                <?php if ($_SESSION['is_admin']): ?>
                    <a class="nav-item nav-link" href="manage_bookings.php">All Bookings</a>
                <?php else: ?>
                    <a class="nav-item nav-link" href="my_bookings.php">My Bookings</a>
                <?php endif; ?>
                <span class="nav-item nav-link text-light">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="nav-item nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="mb-0">Movie Details</h2>
            </div>
            <div class="card-body">
                <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                <p><strong>Screening Time:</strong> <?php echo htmlspecialchars($movie['screening_time']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($movie['location']); ?></p>
                <p><strong>Available Seats:</strong> <?php echo htmlspecialchars($movie['available_seats']); ?></p>
            </div>
        </div>

        <h3>Bookings</h3>
        
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
                        <?php if ($_SESSION['is_admin']): ?>
                            <th>User</th>
                        <?php endif; ?>
                        <th>Seats</th>
                        <th>Booking Time</th>
                        <?php if ($_SESSION['is_admin']): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings->num_rows > 0): ?>
                        <?php while($row = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <?php if ($_SESSION['is_admin']): ?>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($row['seats']); ?></td>
                                <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                                <?php if ($_SESSION['is_admin']): ?>
                                    <td>
                                        <a href="?movie_id=<?php echo $movie_id; ?>&delete_id=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this booking?')">
                                            Delete
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $_SESSION['is_admin'] ? '5' : '3'; ?>" class="text-center">
                                No bookings found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Back to Movies</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 