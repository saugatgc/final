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

// Retrieve recipe_id from URL
if (isset($_GET['recipe_id'])) {
    $recipe_id = $_GET['recipe_id'];
} else {
    echo "No recipe ID provided.";
    exit();
}

// Fetch existing recipe data
$recipe_sql = "SELECT * FROM recipes WHERE recipe_id = ?";
$stmt = $conn->prepare($recipe_sql);
$stmt->bind_param('i', $recipe_id);
$stmt->execute();
$recipe_result = $stmt->get_result();
$recipe = $recipe_result->fetch_assoc();
$stmt->close();

// If recipe does not exist, show error message
if (!$recipe) {
    echo "Recipe not found.";
    exit();
}

// Handle form submission for updating the recipe
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $preparation_time = $_POST['preparation_time'];
    $cooking_time = $_POST['cooking_time'];
    $difficulty_level = $_POST['difficulty_level'];
    $servings = $_POST['servings'];

    // Update recipe in the 'recipes' table
    $update_recipe_sql = "UPDATE recipes SET title = ?, description = ?, preparation_time = ?, cooking_time = ?, difficulty_level = ?, servings = ?, updated_at = NOW() WHERE recipe_id = ?";
    $stmt = $conn->prepare($update_recipe_sql);
    $stmt->bind_param('ssiiisi', $title, $description, $preparation_time, $cooking_time, $difficulty_level, $servings, $recipe_id);
    
    if ($stmt->execute()) {
        // Step 1: Update Ingredients
        if (!empty($_POST['ingredients'])) {
            // Delete existing ingredients first
            $delete_ingredients_sql = "DELETE FROM ingredients WHERE recipe_id = ?";
            $delete_stmt = $conn->prepare($delete_ingredients_sql);
            $delete_stmt->bind_param('i', $recipe_id);
            $delete_stmt->execute();
            $delete_stmt->close();

            // Insert new ingredients
            foreach ($_POST['ingredients'] as $ingredient) {
                $ingredient_name = $ingredient['name'];
                $ingredient_quantity = $ingredient['quantity'];
                
                $ingredient_sql = "INSERT INTO ingredients (recipe_id, name, quantity) VALUES (?, ?, ?)";
                $ingredient_stmt = $conn->prepare($ingredient_sql);
                $ingredient_stmt->bind_param('iss', $recipe_id, $ingredient_name, $ingredient_quantity);
                $ingredient_stmt->execute();
                $ingredient_stmt->close();
            }
        }

        // Step 2: Update Steps
        if (!empty($_POST['steps'])) {
            // Step 1: Delete existing steps for the given recipe_id
            $delete_sql = "DELETE FROM steps WHERE recipe_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $recipe_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        
            // Step 2: Insert new steps
            $step_number = 1;
            foreach ($_POST['steps'] as $step_description) {
                echo "$step_description<br>";
                echo "$recipe_id<br>";
                echo "$step_number<br>";
                $step_sql = "INSERT INTO steps (recipe_id,step_number, description) VALUES (?, ?, ?)";
                $step_stmt = $conn->prepare($step_sql);
                $step_stmt->bind_param('iis', $recipe_id, $step_number, $step_description); // +1 to start step number from 1
                $step_stmt->execute();
                $step_stmt->close();
                $step_number = $step_number + 1;
            }
        }

        // Step 3: Update Photos
        if (!empty($_FILES['photos']['name'][0])) {
            $photos = $_FILES['photos'];
            foreach ($photos['name'] as $key => $photo_name) {
                $photo_tmp = $photos['tmp_name'][$key];
                $photo_ext = pathinfo($photo_name, PATHINFO_EXTENSION);
                $photo_url = 'uploads/' . uniqid('photo_') . '.' . $photo_ext;
        
                if (move_uploaded_file($photo_tmp, $photo_url)) {
                    // Prepare the SQL query to insert photo URL
                    $photo_sql = "INSERT INTO recipe_photos (recipe_id, photo_url) VALUES (?, ?)";
                    
                    // Check if the statement prepares correctly
                    $photo_stmt = $conn->prepare($photo_sql);
                    
                    if ($photo_stmt === false) {
                        die('Error preparing statement: ' . $conn->error);
                    }
        
                    // Bind parameters to the statement (note: no reference here)
                    // 'i' - integer for recipe_id, 's' - string for photo_url
                    $photo_stmt->bind_param('is', $recipe_id, $photo_url);
        
                    // Execute the prepared statement
                    if ($photo_stmt->execute()) {
                        echo "Photo uploaded successfully!";
                    } else {
                        echo "Error executing statement: " . $photo_stmt->error;
                    }
                    
                    // Close the statement
                    $photo_stmt->close();
                }
            }
        }

        // Redirect to the updated recipe's view page
        header('Location: view.php?recipe_id=' . $recipe_id);
        exit();
    } else {
        echo "Error: " . $stmt->error;
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="edit.css">
</head>
<body>
<?php include('navbar.php'); ?>   
<div class="form-container">
        <h2>Edit Recipe</h2>
        <form action="edit.php?recipe_id=<?php echo $recipe_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Recipe Title</label>
                <input type="text" id="title" name="title" value="<?php echo $recipe['title']; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Recipe Description</label>
                <textarea id="description" name="description" rows="4" required><?php echo $recipe['description']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="preparation_time">Preparation Time (in minutes)</label>
                <input type="number" id="preparation_time" name="preparation_time" value="<?php echo $recipe['preparation_time']; ?>" required>
            </div>
            <div class="form-group">
                <label for="cooking_time">Cooking Time (in minutes)</label>
                <input type="number" id="cooking_time" name="cooking_time" value="<?php echo $recipe['cooking_time']; ?>" required>
            </div>
            <div class="form-group">
                <label for="difficulty_level">Difficulty Level</label>
                <select id="difficulty_level" name="difficulty_level" required>
                    <option value="Easy" <?php echo $recipe['difficulty_level'] == 'Easy' ? 'selected' : ''; ?>>Easy</option>
                    <option value="Medium" <?php echo $recipe['difficulty_level'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Hard" <?php echo $recipe['difficulty_level'] == 'Hard' ? 'selected' : ''; ?>>Hard</option>
                </select>
            </div>
            <div class="form-group">
                <label for="servings">Number of Servings</label>
                <input type="number" id="servings" name="servings" value="<?php echo $recipe['servings']; ?>" required>
            </div>

            <!-- Ingredients Section -->
            <div class="form-group">
                <label>Ingredients</label>
                <div id="ingredients">
                    <?php
                    // Fetch existing ingredients
                    $ingredients_sql = "SELECT * FROM ingredients WHERE recipe_id = ?";
                    $ingredient_stmt = $conn->prepare($ingredients_sql);
                    $ingredient_stmt->bind_param('i', $recipe_id);
                    $ingredient_stmt->execute();
                    $ingredient_result = $ingredient_stmt->get_result();
                    $ingredient_stmt->close();

                    $ingredient_counter = 0;
                    while ($ingredient = $ingredient_result->fetch_assoc()) {
                        echo '<div class="ingredient">';
                        echo '<input type="text" name="ingredients[' . $ingredient_counter . '][name]" value="' . $ingredient['name'] . '" placeholder="Ingredient Name" required>';
                        echo '<input type="text" name="ingredients[' . $ingredient_counter . '][quantity]" value="' . $ingredient['quantity'] . '" placeholder="Quantity" required>';
                        echo '</div>';
                        $ingredient_counter++;
                    }
                    ?>
                </div>
                <button type="button" id="add-ingredient">Add Ingredient</button>
            </div>

            <!-- Steps Section -->
            <div class="form-group">
                <label>Steps</label>
                <div id="steps">
                    <?php
                    // Fetch existing steps
                    $steps_sql = "SELECT * FROM steps WHERE recipe_id = ? ORDER BY step_number";
                    $step_stmt = $conn->prepare($steps_sql);
                    $step_stmt->bind_param('i', $recipe_id);
                    $step_stmt->execute();
                    $step_result = $step_stmt->get_result();
                    $step_stmt->close();

                    $step_counter = 0;
                    while ($step = $step_result->fetch_assoc()) {
                        echo '<textarea name="steps[' . $step_counter . ']" placeholder="Step Description" required>' . $step['description'] . '</textarea>';
                        $step_counter++;
                    }
                    ?>
                </div>
                <button type="button" id="add-step">Add Step</button>
            </div>

            <!-- Photos Section -->
            <div class="form-group">
                <label for="photos">Recipe Photos</label>
                <input type="file" name="photos[]" multiple>
            </div>
            

            <button type="submit">Update Recipe</button>
        </form>
    </div>

    <script>
        // Dynamically add more ingredient fields
        document.getElementById('add-ingredient').addEventListener('click', function() {
            var ingredientsDiv = document.getElementById('ingredients');
            var newIngredient = document.createElement('div');
            newIngredient.classList.add('ingredient');
            newIngredient.innerHTML = '<input type="text" name="ingredients[' + ingredientsDiv.children.length + '][name]" placeholder="Ingredient Name" required> <input type="text" name="ingredients[' + ingredientsDiv.children.length + '][quantity]" placeholder="Quantity" required>';
            ingredientsDiv.appendChild(newIngredient);
        });

        // Dynamically add more steps fields
        document.getElementById('add-step').addEventListener('click', function() {
            var stepsDiv = document.getElementById('steps');
            var newStep = document.createElement('textarea');
            newStep.name = 'steps[' + stepsDiv.children.length + ']';
            newStep.placeholder = 'Step Description';
            newStep.required = true;
            stepsDiv.appendChild(newStep);
        });
    </script>
</body>
</html>
