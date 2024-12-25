<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'recepies');  // Update with your actual DB credentials

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch the user's recipes from the database
$sql = "SELECT * FROM recipes WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="userdashboar.css">
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="dashboard">
        <h2>Welcome, <?php echo $_SESSION['email']; ?></h2>
        
       

        <a href="add.php">Add a New Recipe</a>
        
        <h3>Your Recipes:</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Preparation Time (min)</th>
                <th>Cooking Time (min)</th>
                <th>Difficulty</th>
                <th>Servings</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['preparation_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['cooking_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['difficulty_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['servings']); ?></td>
                    <td>
                        <a href="view.php?recipe_id=<?php echo $row['recipe_id']; ?>">View</a> |
                        <a href="edit.php?recipe_id=<?php echo $row['recipe_id']; ?>">Edit</a> |
                        <a href="delete.php?recipe_id=<?php echo $row['recipe_id']; ?>" onclick="return confirm('Are you sure you want to delete this recipe?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
