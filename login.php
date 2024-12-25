<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // If the user is logged in, redirect to the appropriate dashboard
    if ($_SESSION['is_admin']) {
        header('Location: admindashboard.php'); // Admin dashboard
    } else {
        header('Location: frontpage.php');  // Regular user dashboard
    }
    exit;
}

// Handle form submission for login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'recepies'); // Replace with your actual database credentials

    // Check for connection errors
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Prepare and execute query to check for the user
    $stmt = $conn->prepare("SELECT user_id, password_hash, is_admin, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $password_hash, $is_admin, $is_active);

    if ($stmt->num_rows > 0) {
        // User exists, verify password
        $stmt->fetch();

        // Check if the account is active
        if ($is_active == 0) {
            echo "Your account is deactivated. Please contact support.";
        } else {
            // Compare the entered password with the stored hashed password
            if (password_verify($password, $password_hash)) {
                // Password is correct, start a session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['is_admin'] = $is_admin;

                // Redirect based on user role
                if ($is_admin) {
                    header('Location: admindashboard.php'); // Admin dashboard
                } else {
                    header('Location: frontpage.php');  // Regular user dashboard
                }
                exit;
            } else {
                // Incorrect password
                echo 'Invalid password.';
            }
        }
    } else {
        // User not found
        echo "Email id not found.";
    }

    // Close connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div>
        <?php include('navbar.php'); ?>   
    </div>
    <div class="form-container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        </form>
    </div>
</body>
</html>
