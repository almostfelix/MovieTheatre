<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

// If admin user already exists and someone is not logged in as admin, prevent access
$sql = "SELECT id FROM users WHERE is_admin = 1";
$result = $conn->query($sql);
if ($result->num_rows > 0 && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])) {
    die("Admin user already exists. This script is disabled for security reasons.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_key = $_POST['admin_key'];
    
    // Simple security key to prevent unauthorized admin creation
    $correct_admin_key = 'webfejlesztes2024'; // You should change this to something more secure
    
    if ($admin_key !== $correct_admin_key) {
        $error = "Invalid admin key.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if username exists
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "This username is already taken.";
            } else {
                // Insert admin user
                $sql = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)";
                if ($stmt = $conn->prepare($sql)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->bind_param("ss", $username, $hashed_password);
                    
                    if ($stmt->execute()) {
                        $success = "Admin user created successfully! You can now login.";
                    } else {
                        $error = "Something went wrong. Please try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - Movie Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Create Admin User</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <br>
                                <a href="login.php" class="alert-link">Go to Login</a>
                            </div>
                        <?php endif; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Password must be at least 6 characters long.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_key" class="form-label">Admin Key</label>
                                <input type="password" class="form-control" id="admin_key" name="admin_key" required>
                                <div class="form-text">Enter the admin key provided by the system administrator.</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Create Admin User</button>
                                <a href="index.php" class="btn btn-secondary">Back to Home</a>
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