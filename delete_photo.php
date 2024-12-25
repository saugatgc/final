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

// Fetch recipe details
$recipe_sql = "SELECT * FROM recipes WHERE recipe_id = ?";
$stmt = $conn->prepare($recipe_sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$recipe_result = $stmt->get_result();
$recipe = $recipe_result->fetch_assoc();
$stmt->close();

// If the recipe is not found
if (!$recipe) {
    echo "Recipe not found.";
    exit();
}

// Fetch ingredients
$ingredients_sql = "SELECT * FROM ingredients WHERE recipe_id = ?";
$ingredient_stmt = $conn->prepare($ingredients_sql);
$ingredient_stmt->bind_param('i', $recipe_id);
$ingredient_stmt->execute();
$ingredients_result = $ingredient_stmt->get_result();
$ingredient_stmt->close();

// Fetch steps
$steps_sql = "SELECT * FROM steps WHERE recipe_id = ? ORDER BY step_number";
$step_stmt = $conn->prepare($steps_sql);
$step_stmt->bind_param('i', $recipe_id);
$step_stmt->execute();
$steps_result = $step_stmt->get_result();
$step_stmt->close();

// Fetch photos
$photos_sql = "SELECT * FROM recipe_photos WHERE recipe_id = ?";
$photo_stmt = $conn->prepare($photos_sql);
$photo_stmt->bind_param('i', $recipe_id);
$photo_stmt->execute();
$photos_result = $photo_stmt->get_result();
$photo_stmt->close();

// Check if a photo is to be deleted
if (isset($_GET['delete_photo_id'])) {
    $photo_id = $_GET['delete_photo_id'];

    // Delete the photo from the database
    $delete_photo_sql = "DELETE FROM recipe_photos WHERE photo_id = ?";
    $delete_photo_stmt = $conn->prepare($delete_photo_sql);
    $delete_photo_stmt->bind_param('i', $photo_id);
    $delete_photo_stmt->execute();
    $delete_photo_stmt->close();

    // Redirect back to the recipe page
    header("Location: view.php?recipe_id=$recipe_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Recipe</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include('navbar.php'); ?>    
<div class="recipe-container">
        <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
        <p><strong>Preparation Time:</strong> <?php echo $recipe['preparation_time']; ?> minutes</p>
        <p><strong>Cooking Time:</strong> <?php echo $recipe['cooking_time']; ?> minutes</p>
        <p><strong>Difficulty Level:</strong> <?php echo $recipe['difficulty_level']; ?></p>
        <p><strong>Servings:</strong> <?php echo $recipe['servings']; ?></p>

        <h3>Ingredients</h3>
        <ul>
            <?php while ($ingredient = $ingredients_result->fetch_assoc()) { ?>
                <li><?php echo htmlspecialchars($ingredient['name']) . ": " . htmlspecialchars($ingredient['quantity']); ?></li>
            <?php } ?>
        </ul>

        <h3>Steps</h3>
        <ol>
            <?php while ($step = $steps_result->fetch_assoc()) { ?>
                <li><?php echo nl2br(htmlspecialchars($step['description'])); ?></li>
            <?php } ?>
        </ol>

        <h3>Photos</h3>
        <div class="photos">
            <?php while ($photo = $photos_result->fetch_assoc()) { ?>
                <div class="photo">
                    <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Recipe Photo" class="recipe-photo">
                    <a href="view.php?recipe_id=<?php echo $recipe_id; ?>&delete_photo_id=<?php echo $photo['photo_id']; ?>" onclick="return confirm('Are you sure you want to delete this photo?')">Delete</a>
                </div>
            <?php } ?>
        </div>

        <div class="buttons">
            <a href="edit.php?recipe_id=<?php echo $recipe_id; ?>">Edit Recipe</a>
            <a href="delete.php?recipe_id=<?php echo $recipe_id; ?>" onclick="return confirm('Are you sure you want to delete this recipe?')">Delete Recipe</a>
        </div>
    </div>
</body>
</html>