<link rel="stylesheet" href="./navbar.css">

<?php 
 // Start the session to check the user status ?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="frontpage.php" class="navbar-brand">
           <h2>Nepal Recipe Hub </h2>
        </a>
        <ul class="navbar-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['is_admin'] == 1): ?>
                    <!-- If the user is an admin -->
                    <li><a href="admindashboard.php">Admin Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <!-- If the user is a regular user -->
                    <li><a href="frontpage.php">Home</a></li>
                    <li><a href="userdashboard.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            <?php else: ?>
                <!-- If the user is not logged in -->
                <li><a href="frontpage.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
