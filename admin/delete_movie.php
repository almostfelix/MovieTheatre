<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete associated bookings first
        $sql = "DELETE FROM bookings WHERE movie_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        
        // Then delete the movie
        $sql = "DELETE FROM movies WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success_message'] = "Movie and associated bookings deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting movie: " . $e->getMessage();
    }
}

header("location: ../index.php");
exit;