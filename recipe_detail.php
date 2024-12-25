<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'recepies');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get the recipe ID from the URL
if (isset($_GET['recipe_id'])) {
    $recipe_id = $_GET['recipe_id'];
} else {
    echo "Recipe not found.";
    exit;
}

// Fetch recipe details
$recipe_sql = "SELECT r.title, r.description, r.preparation_time, r.cooking_time, r.difficulty_level, r.servings, u.username, u.profile_picture FROM recipes r JOIN users u ON r.user_id = u.user_id WHERE r.recipe_id = ?";
$stmt = $conn->prepare($recipe_sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$recipe_result = $stmt->get_result();
$recipe = $recipe_result->fetch_assoc();

// Fetch recipe ingredients
$ingredients_sql = "SELECT name, quantity FROM ingredients WHERE recipe_id = ?";
$ingredients_stmt = $conn->prepare($ingredients_sql);
$ingredients_stmt->bind_param('i', $recipe_id);
$ingredients_stmt->execute();
$ingredients_result = $ingredients_stmt->get_result();

// Fetch recipe steps
$steps_sql = "SELECT step_number, description FROM steps WHERE recipe_id = ? ORDER BY step_number";  // Corrected query
$steps_stmt = $conn->prepare($steps_sql);
$steps_stmt->bind_param('i', $recipe_id);
$steps_stmt->execute();
$steps_result = $steps_stmt->get_result();

// Fetch recipe photos
$photos_sql = "SELECT photo_url FROM recipe_photos WHERE recipe_id = ?";
$photos_stmt = $conn->prepare($photos_sql);
$photos_stmt->bind_param('i', $recipe_id);
$photos_stmt->execute();
$photos_result = $photos_stmt->get_result();

// Fetch recipe comments
$comments_sql = "SELECT c.comment_text, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.recipe_id = ?";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param('i', $recipe_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $comment_text = $_POST['comment_text'];

        // Insert comment into the database
        $insert_comment_sql = "INSERT INTO comments (recipe_id, user_id, comment_text) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_comment_sql);
        $insert_stmt->bind_param('iis', $recipe_id, $user_id, $comment_text);
        $insert_stmt->execute();
    } else {
        echo "You must be logged in to comment.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $recipe['title']; ?></title>
    <link rel="stylesheet" href="recipe_detail.css">
</head>
<body>
<?php include('navbar.php'); ?>    


    <main>
     
    <div class="recipe-detail">
            <h2><?php echo $recipe['title']; ?></h2>
            <p><strong>Preparation time:</strong> <?php echo $recipe['preparation_time']; ?> minutes | <strong>Cooking time:</strong> <?php echo $recipe['cooking_time']; ?> minutes</p>
            <p><strong>Difficulty:</strong> <?php echo $recipe['difficulty_level']; ?> | <strong>Servings:</strong> <?php echo $recipe['servings']; ?></p>
            <p><?php echo nl2br($recipe['description']); ?></p>

            <h3>Ingredients</h3>
            <ul>
                <?php while ($ingredient = $ingredients_result->fetch_assoc()): ?>
                    <li><?php echo $ingredient['name']; ?> - <?php echo $ingredient['quantity']; ?></li>
                <?php endwhile; ?>
            </ul>

            <h3>Steps</h3>
            <ol>
                <?php while ($step = $steps_result->fetch_assoc()): ?>
                    <li><?php echo $step['description']; ?></li>
                <?php endwhile; ?>
            </ol>

            <h3>Recipe Photos</h3>
            <?php while ($photo = $photos_result->fetch_assoc()): ?>
                <img src="<?php echo $photo['photo_url']; ?>" alt="Recipe Photo">
            <?php endwhile; ?>

            <h3>Comments</h3>
            <div class="comments">
                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                    <div class="comment">
                        <strong><?php echo $comment['username']; ?>:</strong>
                        <p><?php echo $comment['comment_text']; ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
            <h3>Leave a Comment</h3>
<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Display comment form only if user is logged in -->
    <form action="comment.php" method="POST">
        <textarea name="comment_text" required></textarea>
        <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>" />
        <button type="submit">Submit Comment</button>
    </form>
<?php else: ?>
    <!-- Display message if user is not logged in -->
    <p>You must be logged in to leave a comment. <a href="login.php">Login here</a>.</p>
<?php endif; ?>


        </div>
    </main>
</body>
</html>

<?php
$conn->close();
?>
