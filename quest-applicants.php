<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$quest_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$quest = $conn->query("
    SELECT * FROM quests 
    WHERE quest_id = $quest_id AND client_id = $user_id
")->fetch_assoc();

if (!$quest) {
    die("Quest not found or unauthorized access.");
}

if (isset($_GET['action']) && isset($_GET['app_id'])) {

    $action = $_GET['action'];
    $app_id = (int)$_GET['app_id'];

    $app = $conn->query("
        SELECT * FROM applications 
        WHERE application_id = $app_id AND quest_id = $quest_id
    ")->fetch_assoc();

    if ($app) {

        if ($action === 'accept') {

            $conn->query("
                UPDATE applications 
                SET status = 'accepted'
                WHERE application_id = $app_id
            ");

            $conn->query("
                UPDATE quests 
                SET status = 'in_progress'
                WHERE quest_id = $quest_id
            ");

            $conn->query("
                INSERT INTO notifications (user_id, message, link)
                VALUES (
                    {$app['student_id']},
                    'Your application has been accepted!',
                    'messages.php'
                )
            ");
        }

        if ($action === 'reject') {

            $conn->query("
                UPDATE applications 
                SET status = 'rejected'
                WHERE application_id = $app_id
            ");

            $conn->query("
                INSERT INTO notifications (user_id, message)
                VALUES (
                    {$app['student_id']},
                    'Your application was not selected.'
                )
            ");
        }
    }

    header("Location: quest-applicants.php?id=$quest_id");
    exit();
}

$applications = $conn->query("
    SELECT a.*, u.full_name, u.level
    FROM applications a
    JOIN users u ON a.student_id = u.user_id
    WHERE a.quest_id = $quest_id
    ORDER BY a.applied_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quest Applications</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

    <h1><?php echo htmlspecialchars($quest['title']); ?></h1>
    <p style="color:#aaa;"><?php echo htmlspecialchars($quest['description']); ?></p>

    <h2 style="margin-top:2rem;">Applicants</h2>

    <?php if ($applications->num_rows > 0): ?>

        <div class="quest-grid">

            <?php while ($app = $applications->fetch_assoc()): ?>

                <div class="quest-card">

                    <h3><?php echo htmlspecialchars($app['full_name']); ?></h3>
                    <p>⭐ Level <?php echo $app['level']; ?></p>

                    <p>Status: 
                        <strong><?php echo strtoupper($app['status']); ?></strong>
                    </p>

                    <?php if ($app['status'] === 'pending'): ?>

                        <a href="?id=<?php echo $quest_id; ?>&action=accept&app_id=<?php echo $app['application_id']; ?>" class="btn">
                            ✅ Accept
                        </a>

                        <a href="?id=<?php echo $quest_id; ?>&action=reject&app_id=<?php echo $app['application_id']; ?>" class="btn-clear">
                            ❌ Reject
                        </a>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <p style="color:#aaa;">No applications yet.</p>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>