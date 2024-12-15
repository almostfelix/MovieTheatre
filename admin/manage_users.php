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

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if user is not an admin
        $sql = "SELECT is_admin FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && !$user['is_admin']) {
            // Delete user's bookings first
            $sql = "DELETE FROM bookings WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Delete user
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $conn->commit();
            $success = "User and their bookings deleted successfully!";
        } else {
            throw new Exception("Cannot delete admin users.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }
}

// Handle admin status toggle
if (isset($_GET['toggle_admin']) && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $make_admin = $_GET['toggle_admin'] === '1' ? 1 : 0;
    
    try {
        $sql = "UPDATE users SET is_admin = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $make_admin, $user_id);
        
        if ($stmt->execute()) {
            $success = "User admin status updated successfully!";
        } else {
            throw new Exception("Failed to update user status.");
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all users with their booking counts
$sql = "SELECT u.*, COUNT(b.id) as total_bookings 
        FROM users u 
        LEFT JOIN bookings b ON u.id = b.user_id 
        GROUP BY u.id 
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Movie Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Movie Booking</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-item nav-link" href="../index.php">Movies</a>
                <a class="nav-item nav-link" href="../bookings/manage_bookings.php">Manage Bookings</a>
                <a class="nav-item nav-link active" href="manage_users.php">Manage Users</a>
                <a class="nav-item nav-link" href="../public/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Manage Users</h2>
        
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
                        <th>Username</th>
                        <th>Role</th>
                        <th>Total Bookings</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <?php if ($row['is_admin']): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['total_bookings']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <?php if (!$row['is_admin']): ?>
                                            <a href="?toggle_admin=1&user_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-warning btn-sm"
                                               onclick="return confirm('Make this user an admin?')">
                                                Make Admin
                                            </a>
                                            <a href="?delete_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure? This will delete all bookings for this user.')">
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <a href="?toggle_admin=0&user_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-info btn-sm"
                                               onclick="return confirm('Remove admin privileges from this user?')">
                                                Remove Admin
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 