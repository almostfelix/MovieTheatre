<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("location: ../index.php");
    exit;
}

$error = '';
$success = '';
$movie = null;

// Get movie details
if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    $sql = "SELECT * FROM movies WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $movie_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $movie = $result->fetch_assoc();
            } else {
                header("location: ../index.php");
                exit;
            }
        }
        $stmt->close();
    }
} else {
    header("location: ../index.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $screening_time = $_POST['screening_time'];
    $location = trim($_POST['location']);
    $available_seats = (int)$_POST['available_seats'];
    
    if (empty($title) || empty($screening_time) || empty($location) || $available_seats < 0) {
        $error = "Please fill all fields correctly.";
    } else {
        $sql = "UPDATE movies SET title = ?, screening_time = ?, location = ?, available_seats = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssis", $title, $screening_time, $location, $available_seats, $movie_id);
            
            if ($stmt->execute()) {
                $success = "Movie updated successfully!";
                // Refresh movie data
                $sql = "SELECT * FROM movies WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $movie_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $movie = $result->fetch_assoc();
            } else {
                $error = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie - Movie Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Edit Movie</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $movie_id; ?>" method="post">
                            <div class="mb-3">
                                <label for="title" class="form-label">Movie Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="screening_time" class="form-label">Screening Time</label>
                                <input type="datetime-local" class="form-control" id="screening_time" name="screening_time" 
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($movie['screening_time'])); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($movie['location']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="available_seats" class="form-label">Available Seats</label>
                                <input type="number" class="form-control" id="available_seats" name="available_seats" min="0" value="<?php echo htmlspecialchars($movie['available_seats']); ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Movie</button>
                                <a href="../index.php" class="btn btn-secondary">Back to Movies</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 