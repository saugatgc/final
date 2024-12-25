<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php"); // If not admin, redirect to login page
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the form data
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $is_active = $_POST['is_active'];
    
    // Profile picture upload handling
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        // Ensure the file is an image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            $profile_picture = uniqid() . '-' . $_FILES['profile_picture']['name'];
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], 'uploads/' . $profile_picture);
        } else {
            echo "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            exit;
        }
    }

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'recepies'); // Use your actual database credentials

    // Check for connection errors
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Update query to modify the user information
    $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, is_active = ?";

    // If a new profile picture is uploaded, add it to the query
    if ($profile_picture) {
        $sql .= ", profile_picture = ?";
    }

    $sql .= " WHERE user_id = ?";

    $stmt = $conn->prepare($sql);

    // Bind parameters
    if ($profile_picture) {
        $stmt->bind_param("ssssisi", $username, $email, $first_name, $last_name, $is_active, $profile_picture, $user_id);
    } else {
        $stmt->bind_param("ssssii", $username, $email, $first_name, $last_name, $is_active, $user_id);
    }

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to the admin dashboard
        header("Location: admindashboard.php");
        exit;
    } else {
        echo "Error updating user: " . $stmt->error;
    }

    // Close connection
    $stmt->close();
    $conn->close();
}
?>
