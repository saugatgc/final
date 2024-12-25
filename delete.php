<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'recepies');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get the recipe_id from the URL
if (isset($_GET['recipe_id'])) {
    $recipe_id = $_GET['recipe_id'];
} else {
    echo "Recipe ID is missing.";
    exit();
}

// Delete photos related to the recipe
$delete_photos_sql = "DELETE FROM recipe_photos WHERE recipe_id = ?";
$stmt = $conn->prepare($delete_photos_sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$stmt->close();

// Delete steps related to the recipe
$delete_steps_sql = "DELETE FROM steps WHERE recipe_id = ?";
$stmt = $conn->prepare($delete_steps_sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$stmt->close();

// Delete ingredients related to the recipe
$delete_ingredients_sql = "DELETE FROM ingredients WHERE recipe_id = ?";
$stmt = $conn->prepare($delete_ingredients_sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$stmt->close();

// Delete the recipe itself
$delete_recipe_sql = "DELETE FROM recipes WHERE recipe_id = ?";
$stmt = $conn->prepare($delete_recipe_sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$stmt->close();

// Redirect to the recipe list or home page after deletion
header('Location: userdashboard.php');  // You can change this to whatever page you want to redirect to after deletion
exit();
?>
