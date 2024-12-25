<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php"); // If not admin, redirect to login page
    exit;
}

// Check if user_id is provided in the URL
if (!isset($_GET['user_id'])) {
    echo "No user ID provided.";
    exit;
}

// Get the user_id from the URL
$user_id = $_GET['user_id'];

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'recepies'); // Use your actual database credentials

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if the user is linked to any recipes
$sql_check_recipes = "SELECT COUNT(*) FROM recipes WHERE user_id = ?";
$stmt = $conn->prepare($sql_check_recipes);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($recipe_count);
$stmt->fetch();
$stmt->close();

if ($recipe_count > 0) {
    echo "Cannot delete this user as they are linked to one or more recipes.";
    exit;
}

// Proceed to delete the user
$sql_delete_user = "DELETE FROM users WHERE user_id = ?";
$stmt_delete = $conn->prepare($sql_delete_user);
$stmt_delete->bind_param("i", $user_id);

if ($stmt_delete->execute()) {
    // Successfully deleted user, redirect back to admin dashboard
    header("Location: admindashboard.php");
    exit;
} else {
    echo "Error deleting user: " . $stmt_delete->error;
}

// Close connection
$stmt_delete->close();
$conn->close();
?>
