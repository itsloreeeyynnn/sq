<?php
include 'includes/auth.php';
include 'includes/db.php';

$client_id = $_SESSION['user_id'];

if (isset($_POST['post_quest'])) {

    $category_id = (int) $_POST['category_id'];
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $reward = (float) $_POST['reward'];
    $deadline = $_POST['deadline'];
    $difficulty = mysqli_real_escape_string($conn, $_POST['difficulty']);

    if ($reward < 1) {
        $error = "Reward must be greater than 0.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO quests
            (client_id, category_id, title, description, reward, deadline, difficulty)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iissdss",
            $client_id,
            $category_id,
            $title,
            $description,
            $reward,
            $deadline,
            $difficulty
        );

        $stmt->execute();

        header("Location: post-quest.php?success=1");
        exit();
    }
}

if (isset($_GET['success'])) {
    $success = "Quest posted successfully!";
}

$quests = $conn->query("
    SELECT q.*,
        (
            SELECT COUNT(*)
            FROM applications a
            WHERE a.quest_id = q.quest_id
        ) AS applicant_count,

        (
            SELECT COUNT(*)
            FROM submissions s
            WHERE s.quest_id = q.quest_id
        ) AS submission_count

    FROM quests q
    WHERE q.client_id = $client_id
    ORDER BY q.created_at DESC
");

$categories = $conn->query("
    SELECT *
    FROM categories
    ORDER BY category_name ASC
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

        <h2>⚔️ Post a Side Quest</h2>

        <?php if (isset($success)) { ?>
            <div class="success-message">
                <?php echo $success; ?>
            </div>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <form method="POST">

            <input
                type="text"
                name="title"
                placeholder="Quest Title"
                required
            >

            <textarea
                name="description"
                placeholder="Quest Description"
                required
            ></textarea>

            <input
                type="number"
                step="0.01"
                min="1"
                name="reward"
                placeholder="Reward Amount"
                required
            >

            <input
                type="datetime-local"
                name="deadline"
                required
            >

           
            <select name="category_id" required>

                <option value="">Select Category</option>

                <?php while ($cat = $categories->fetch_assoc()): ?>

                    <option value="<?php echo $cat['category_id']; ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>

                <?php endwhile; ?>

            </select>

           
            <select name="difficulty" required>

                <option value="">Select Difficulty</option>
                <option value="easy">⚡ Easy</option>
                <option value="medium">🔥 Medium</option>
                <option value="hard">💀 Hard</option>
                <option value="risky">☠️ Risky</option>

            </select>

            <button type="submit" name="post_quest" class="btn">
                Publish Quest
            </button>

        </form>

    </div>

    
    <div class="quest-list">

        <h2 style="margin-bottom:1rem;">
            📋 Your Quests
        </h2>

        <?php if ($quests->num_rows > 0): ?>

            <div class="quest-grid">

                <?php while ($q = $quests->fetch_assoc()): ?>

                    <div class="quest-card">

                        <h2>
                            <?php echo htmlspecialchars($q['title']); ?>
                        </h2>

                        <p>
                            <?php echo htmlspecialchars($q['description']); ?>
                        </p>

                        <div class="quest-info">
                            💰 ₱<?php echo number_format($q['reward'], 2); ?>
                        </div>

                        <div class="quest-info">
                            📌 Status:
                            <strong>
                                <?php echo strtoupper($q['status']); ?>
                            </strong>
                        </div>

                        <div class="quest-info">
                            👥 Applicants:
                            <?php echo $q['applicant_count']; ?>
                        </div>

                        <div class="quest-info">
                            📦 Submissions:
                            <?php echo $q['submission_count']; ?>
                        </div>

                        <div class="quest-info">
                            📅 Deadline:
                            <?php echo date('M d, Y h:i A', strtotime($q['deadline'])); ?>
                        </div>

                        <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap;">

                            <a
                                href="submission-review.php?quest_id=<?php echo $q['quest_id']; ?>"
                                class="btn"
                            >
                                📦 Review Submissions
                            </a>

                            <a
                                href="hired.php?quest_id=<?php echo $q['quest_id']; ?>"
                                class="btn-clear"
                            >
                                👥 Hired Adventurers
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="quest-card" style="text-align:center;">

                <h2>No quests posted yet ⚔️</h2>

                <p style="color:#aaa;">
                    Your posted quests will appear here.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>