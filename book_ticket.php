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
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $movie_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $movie = $result->fetch_assoc();
            } else {
                header("location: index.php");
                exit;
            }
        }
        $stmt->close();
    }
} else {
    header("location: index.php");
    exit;
}

// Process booking
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seats = (int)$_POST['seats'];
    
    if ($seats <= 0 || $seats > $movie['available_seats']) {
        $error = "Invalid number of seats.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Check if seats are still available
            $sql = "SELECT available_seats FROM movies WHERE id = ? AND available_seats >= ? FOR UPDATE";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $movie_id, $seats);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                // Create booking
                $sql = "INSERT INTO bookings (movie_id, user_id, seats) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $movie_id, $_SESSION['user_id'], $seats);
                $stmt->execute();
                
                // Update available seats
                $sql = "UPDATE movies SET available_seats = available_seats - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $seats, $movie_id);
                $stmt->execute();
                
                $conn->commit();
                $success = "Booking successful!";
                
                // Refresh movie data
                $sql = "SELECT * FROM movies WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $movie_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $movie = $result->fetch_assoc();
            } else {
                throw new Exception("Not enough seats available.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - Movie Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Book Ticket</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                            <p><strong>Screening Time:</strong> <?php echo htmlspecialchars($movie['screening_time']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($movie['location']); ?></p>
                            <p><strong>Available Seats:</strong> <?php echo htmlspecialchars($movie['available_seats']); ?></p>
                        </div>
                        
                        <?php if ($movie['available_seats'] > 0): ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?movie_id=" . $movie_id; ?>" method="post">
                                <div class="mb-3">
                                    <label for="seats" class="form-label">Number of Seats</label>
                                    <input type="number" class="form-control" id="seats" name="seats" min="1" max="<?php echo $movie['available_seats']; ?>" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Book Now</button>
                                    <a href="index.php" class="btn btn-secondary">Back to Movies</a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">Sorry, this movie is sold out.</div>
                            <div class="d-grid">
                                <a href="index.php" class="btn btn-secondary">Back to Movies</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 