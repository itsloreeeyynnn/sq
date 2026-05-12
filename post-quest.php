<?php
include 'includes/auth.php';
include 'includes/db.php';

if (isset($_POST['post_quest'])) {

    $client_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $reward = $_POST['reward'];
    $deadline = $_POST['deadline'];
    $difficulty = $_POST['difficulty'];

    $query = "INSERT INTO quests
    (client_id, category_id, title, description, reward, deadline, difficulty)
    VALUES
    ('$client_id', '$category_id', '$title', '$description', '$reward', '$deadline', '$difficulty')";

    mysqli_query($conn, $query);

    $success = "Quest posted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Quest</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="form-container">

        <div class="form-card profile-card">

            <h1>Post a Side Quest</h1>

            <?php if (isset($success)) { ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php } ?>

            <form method="POST">

                <input type="text" name="title" placeholder="Quest Title" required>

                <textarea name="description" placeholder="Quest Description" required></textarea>

                <input type="number" name="reward" placeholder="Reward Amount" required>

                <input type="datetime-local" name="deadline" required>

                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <option value="1">🎨 Design</option>
                    <option value="2">💻 Programming</option>
                    <option value="3">📚 Academic</option>
                    <option value="4">✍️ Writing</option>
                </select>

                <select name="difficulty" required>
                    <option value="">Select Difficulty</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                    <option value="risky">Risky</option>
                </select>

                <button type="submit" name="post_quest" class="btn">
                    Publish Quest
                </button>

            </form>

        </div>

        <div class="stats-container">

            <div class="stats-card">
                <h2>📋 Total Quests Posted</h2>
                <p><?php echo $total_quests; ?></p>
            </div>

        </div>

    </div>

</body>

</html>