<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'recepies');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch all recipes and their first photo
$recipes_sql = "SELECT r.recipe_id, r.title, r.description, r.preparation_time, r.cooking_time, r.difficulty_level, r.servings, u.username, rp.photo_url 
                FROM recipes r
                JOIN users u ON r.user_id = u.user_id
                LEFT JOIN recipe_photos rp ON r.recipe_id = rp.recipe_id
                GROUP BY r.recipe_id
                ORDER BY r.created_at DESC";
$recipes_result = $conn->query($recipes_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Sharing Platform</title>
    <link rel="stylesheet" href="frontpage.css">
    <style>
        /* Add your CSS styles here */
        .recipe-list { display: flex; flex-wrap: wrap; gap: 20px; }
        .recipe-item { width: 30%; border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
        .recipe-item img { width: 100%; height: 200px; object-fit: cover; }
        .recipe-item h3 { font-size: 1.5rem; }
        .recipe-item p { font-size: 1rem; color: #555; }
        .view-details { padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; }
    </style>
</head>
<body>
<header>
    <?php include('navbar.php'); ?>
    <h1>Recipe Sharing Platform</h1>
</header>

    <main>
        <div class="recipe-list">
            <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                <div class="recipe-item">
                    <!-- Display Recipe Photos -->
                    <?php if ($recipe['photo_url']): ?>
                        <img src="<?php echo $recipe['photo_url']; ?>" alt="Recipe Photo">
                    <?php else: ?>
                        <img src="default-recipe.png" alt="Default Recipe Photo">
                    <?php endif; ?>
                    <h3><a href="recipe_detail.php?recipe_id=<?php echo $recipe['recipe_id']; ?>"><?php echo $recipe['title']; ?></a></h3>
                    <p><strong>Preparation time:</strong> <?php echo $recipe['preparation_time']; ?> minutes | <strong>Cooking time:</strong> <?php echo $recipe['cooking_time']; ?> minutes</p>
                    <p><strong>Difficulty:</strong> <?php echo $recipe['difficulty_level']; ?> | <strong>Servings:</strong> <?php echo $recipe['servings']; ?></p>
                    <p><?php echo substr($recipe['description'], 0, 45) . '...'; ?></p>
                    <a class="view-details" href="recipe_detail.php?recipe_id=<?php echo $recipe['recipe_id']; ?>">View Details</a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>

<?php
$conn->close();
?>
