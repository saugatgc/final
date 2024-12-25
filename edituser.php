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

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'recepies'); // Use your actual database credentials

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch the user data based on user_id
$user_id = $_GET['user_id'];
$sql = "SELECT user_id, username, email, first_name, last_name, profile_picture, is_active FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "User not found.";
    exit;
}

// Fetch user data
$user = $result->fetch_assoc();

// Close connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include('navbar.php'); ?>    
<div class="admin-dashboard">
        <h2>Edit User</h2>
        
        <form action="updateuser.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture">
                <?php if ($user['profile_picture']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" width="100">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active">
                    <option value="1" <?php echo $user['is_active'] ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo !$user['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit">Update User</button>
        </form>
    </div>
</body>
</html>
