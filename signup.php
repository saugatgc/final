<?php
// Start the session to manage any session variables later
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to user dashboard or home page if logged in
    header("Location: userdashboard.php"); // Replace with the actual dashboard page
    exit;
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    // Validate that the fields are not empty
    if (empty($username) || empty($email) || empty($password)) {
        echo "All fields are required!";
        exit;
    }

    // Hash the password using bcrypt
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Set the default values for 'is_active' and 'is_admin'
    $is_active = 0; // Default user is not active (0)
    $is_admin = 0; // Default user is not admin (0)

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'recepies'); // Replace with your actual database credentials

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL query to insert the user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, is_admin, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $username, $email, $password_hash, $first_name, $last_name, $is_admin, $is_active); // Bind the parameters

    // Execute the query
    if ($stmt->execute()) {
        // Successful registration, redirect to login page
        header("Location: login.php");
        exit;
    } else {
        // If there was an error, display it
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div>
        <?php include('navbar.php'); ?>   
    </div>
    <div class="form-container">
        <h2>Create an Account</h2>
        <form action="signup.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name">
            </div>
            <button type="submit">Sign Up</button>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>
