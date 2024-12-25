<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'recepies');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to comment.";
    exit;
}

// Ensure that comment text and recipe ID are passed correctly
if (isset($_POST['comment_text']) && isset($_POST['recipe_id'])) {
    $user_id = $_SESSION['user_id'];
    $recipe_id = $_POST['recipe_id'];
    $comment_text = $_POST['comment_text'];

    // Sanitize the comment text to prevent XSS attacks
    $comment_text = htmlspecialchars($comment_text, ENT_QUOTES, 'UTF-8');

    // Insert comment into the database
    $insert_comment_sql = "INSERT INTO comments (recipe_id, user_id, comment_text) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_comment_sql);
    $insert_stmt->bind_param('iis', $recipe_id, $user_id, $comment_text);
    $insert_stmt->execute();

    // Redirect back to the recipe detail page after comment submission
    header("Location: recipe_detail.php?recipe_id=" . $recipe_id);
    exit();
} else {
    echo "Invalid comment data.";
    exit;
}

$conn->close();
?>
