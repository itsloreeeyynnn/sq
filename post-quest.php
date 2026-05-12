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

$client_id = $_SESSION['user_id'];

$quests = $conn->query("
    SELECT q.*,
        (SELECT COUNT(*) FROM applications a WHERE a.quest_id = q.quest_id) AS applicant_count,
        (SELECT COUNT(*) FROM submissions s WHERE s.quest_id = q.quest_id) AS submission_count
    FROM quests q
    WHERE q.client_id = $client_id
    ORDER BY q.created_at DESC
");

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

            <h2>Post a Side Quest</h2>

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

        <!-- QUEST LIST -->
        <div class="quest-list">
                <h2>Your Quests</h2>
            <?php while ($q = $quests->fetch_assoc()): ?>

                <div class="quest-card">

                    <h2><?php echo htmlspecialchars($q['title']); ?></h2>

                    <p><?php echo htmlspecialchars($q['description']); ?></p>

                    <div class="quest-info">
                        💰 ₱<?php echo number_format($q['reward'], 2); ?>
                    </div>

                    <div class="quest-info">
                        📌 Status: <strong><?php echo strtoupper($q['status']); ?></strong>
                    </div>

                    <div class="quest-info">
                        👥 Applicants: <?php echo $q['applicant_count']; ?>
                    </div>

                    <div class="quest-info">
                        📦 Submissions: <?php echo $q['submission_count']; ?>
                    </div>

                    <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap;">

                        <a href="submission-review.php?quest_id=<?php echo $q['quest_id']; ?>" class="btn">
                            📦 Review Submissions
                        </a>

                        <a href="quest-applicants.php?id=<?php echo $q['quest_id']; ?>" class="btn-clear">
                            👥 View Applicants
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    </div>

</body>

</html>