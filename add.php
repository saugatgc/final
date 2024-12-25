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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Step 1: Insert the recipe into the database
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $preparation_time = $_POST['preparation_time'];
    $cooking_time = $_POST['cooking_time'];
    $difficulty_level = $_POST['difficulty_level'];
    $servings = $_POST['servings'];
    // Insert recipe into the 'recipes' table

    $valid_difficulty_levels = ['Easy', 'Medium', 'Hard'];
    if (!in_array($difficulty_level, $valid_difficulty_levels)) {
        die('Invalid difficulty level');
    }

    $recipe_sql = "INSERT INTO recipes (user_id, title, description, preparation_time, cooking_time, difficulty_level, servings) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($recipe_sql);
    $stmt->bind_param('issiiis', $user_id, $title, $description, $preparation_time, $cooking_time, $difficulty_level, $servings);
    
    if ($stmt->execute()) {
        // Get the recipe_id of the newly added recipe
        $recipe_id = $stmt->insert_id;
        $stmt->close();
        
        // Step 2: Add Ingredients (If provided)
        if (!empty($_POST['ingredients'])) {
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

        // Step 3: Add Steps (If provided)
        if (!empty($_POST['steps'])) {
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

        // Step 4: Add Photos (If provided)
       // Move the uploaded file to the "uploads" folder
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
    


        // Redirect to the newly created recipe's view page
        // echo "$difficulty_level";
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
    <title>Add New Recipe</title>
    <link rel="stylesheet" href="add.css">
</head>
<body>
<?php include('navbar.php'); ?>    
<div class="form-container">
        <h2>Add a New Recipe</h2>
        <form action="add.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Recipe Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Recipe Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="preparation_time">Preparation Time (in minutes)</label>
                <input type="number" id="preparation_time" name="preparation_time" required>
            </div>
            <div class="form-group">
                <label for="cooking_time">Cooking Time (in minutes)</label>
                <input type="number" id="cooking_time" name="cooking_time" required>
            </div>
            <div class="form-group">
                <label for="difficulty_level">Difficulty Level</label>
                <select id="difficulty_level" name="difficulty_level" required>
                    <option value="Easy">Easy</option>
                    <option value="Medium">Medium</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>
            <div class="form-group">
                <label for="servings">Number of Servings</label>
                <input type="number" id="servings" name="servings" required>
            </div>
            
            <!-- Ingredients Section -->
            <div class="form-group">
                <label>Ingredients</label>
                <div id="ingredients">
                    <div class="ingredient">
                        <input type="text" name="ingredients[0][name]" placeholder="Ingredient Name" required>
                        <input type="text" name="ingredients[0][quantity]" placeholder="Quantity" required>
                    </div>
                </div>
                <button type="button" id="add-ingredient">Add Ingredient</button>
            </div>
            
            <!-- Steps Section -->
            <div class="form-group">
                <label>Steps</label>
                <div id="steps">
                    <textarea name="steps[0]" name="steps[0][step_description]" placeholder="Step Description" required></textarea>
                </div>
                <button type="button" id="add-step">Add Step</button>
            </div>

            <!-- Photos Section -->
            <div class="form-group">
                <label for="photos">Recipe Photos</label>
                <input type="file" name="photos[]" multiple>
            </div>

            <button type="submit">Submit Recipe</button>
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
