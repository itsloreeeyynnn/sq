<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Get client info
$user_result = $conn->query("SELECT full_name, level FROM users WHERE user_id = $user_id");
$user = $user_result->fetch_assoc();

// Total quests posted by client
$quests_result = $conn->query("
    SELECT COUNT(*) as total
    FROM quests
    WHERE client_id = $user_id
");
$total_quests = $quests_result->fetch_assoc()['total'];

// Active (open) quests
$active_result = $conn->query("
    SELECT COUNT(*) as total 
    FROM quests 
    WHERE client_id = $user_id AND status = 'open'
");
$active_quests = $active_result->fetch_assoc()['total'];

// Total applicants received
$applicants_result = $conn->query("
    SELECT COUNT(*) as total
    FROM applications a
    JOIN quests q ON a.quest_id = q.quest_id
    WHERE q.client_id = $user_id
");
$total_applicants = $applicants_result->fetch_assoc()['total'];

// Accepted freelancers (completed hires)
$accepted_result = $conn->query("
    SELECT COUNT(*) as total
    FROM applications a
    JOIN quests q ON a.quest_id = q.quest_id
    WHERE q.client_id = $user_id AND a.status = 'accepted'
");
$accepted_hires = $accepted_result->fetch_assoc()['total'];

// Total spent (reward sum of accepted quests)
$spent_result = $conn->query("
    SELECT COALESCE(SUM(q.reward), 0) as total
    FROM quests q
    JOIN applications a ON a.quest_id = q.quest_id
    WHERE q.client_id = $user_id AND a.status = 'accepted'
");
$total_spent = $spent_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Dashboard</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> 🧭</h1>
    <p style="color:#aaa; margin-bottom:1.5rem;">
        Manage your quests and find the right adventurers.
    </p>

    <div class="dashboard-cards">

        <a href="post-quest.php" class="dashboard-card">
            <h2>📝 Total Quests</h2>
            <p><?php echo $total_quests; ?></p>
        </a>

        <a href="quest-active-client.php" class="dashboard-card">
            <h2>🟢 Active Quests</h2>
            <p><?php echo $active_quests; ?></p>
        </a>

        <a href="hired.php" class="dashboard-card">
            <h2>🤝 Hired adventurers</h2>
            <p><?php echo $accepted_hires; ?></p>
        </a>

        <a href="analytics.php" class="dashboard-card">
            <h2>💰 Total Spent</h2>
            <p>₱<?php echo number_format($total_spent, 0); ?></p>
        </a>

    </div>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>