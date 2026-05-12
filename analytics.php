<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

/* ---------------------------
   TOTAL SPENT
---------------------------- */
$total_spent = $conn->query("
    SELECT COALESCE(SUM(q.reward), 0) AS total
    FROM quests q
    INNER JOIN applications a ON a.quest_id = q.quest_id
    WHERE q.client_id = $user_id
    AND a.status = 'accepted'
")->fetch_assoc()['total'];

/* ---------------------------
   QUEST STATS
---------------------------- */
$quest_stats = $conn->query("
    SELECT 
        COUNT(*) AS total_quests,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_quests,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_quests
    FROM quests
    WHERE client_id = $user_id
")->fetch_assoc();

/* ---------------------------
   SPENDING PER QUEST
---------------------------- */
$breakdown = $conn->query("
    SELECT 
        q.quest_id,
        q.title,
        q.reward,
        q.status,
        COUNT(a.application_id) AS applicants,
        SUM(CASE WHEN a.status = 'accepted' THEN q.reward ELSE 0 END) AS spent
    FROM quests q
    LEFT JOIN applications a ON a.quest_id = q.quest_id
    WHERE q.client_id = $user_id
    GROUP BY q.quest_id
    ORDER BY q.created_at DESC
");

/* ---------------------------
   RECENT PAYMENTS (accepted work)
---------------------------- */
$recent = $conn->query("
    SELECT 
        q.title,
        q.reward,
        u.full_name,
        a.applied_at
    FROM applications a
    INNER JOIN quests q ON q.quest_id = a.quest_id
    INNER JOIN users u ON u.user_id = a.student_id
    WHERE q.client_id = $user_id
    AND a.status = 'accepted'
    ORDER BY a.applied_at DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Analytics</title>
<link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

<h1>📊 Analytics Dashboard</h1>

<!-- TOP STATS -->
<div class="analytics-grid">

    <div class="dashboard-card">
        <h3>💰 Total Spent</h3>
        <h2>₱<?php echo number_format($total_spent, 2); ?></h2>
    </div>

    <div class="dashboard-card">
        <h3>📌 Total Quests</h3>
        <h2><?php echo $quest_stats['total_quests']; ?></h2>
    </div>

    <div class="dashboard-card">
        <h3>🔥 Open Quests</h3>
        <h2><?php echo $quest_stats['open_quests']; ?></h2>
    </div>

</div>

<!-- BREAKDOWN -->
<h2>📦 Spending Breakdown</h2>

<table class="table">

<tr>
    <th>Quest</th>
    <th>Status</th>
    <th>Reward</th>
    <th>Applicants</th>
</tr>

<?php while($row = $breakdown->fetch_assoc()): ?>

<tr>

    <td><?php echo htmlspecialchars($row['title']); ?></td>

    <td>
        <span class="badge <?php echo $row['status']; ?>">
            <?php echo strtoupper($row['status']); ?>
        </span>
    </td>

    <td>₱<?php echo number_format($row['reward'], 2); ?></td>

    <td><?php echo $row['applicants']; ?></td>

</tr>

<?php endwhile; ?>

</table>

<!-- RECENT PAYMENTS -->
<h2 style="margin-top:30px;">💸 Recent Payments</h2>

<table class="table">

<tr>
    <th>Student</th>
    <th>Quest</th>
    <th>Amount</th>
    <th>Date</th>
</tr>

<?php while($r = $recent->fetch_assoc()): ?>

<tr>
    <td><?php echo htmlspecialchars($r['full_name']); ?></td>
    <td><?php echo htmlspecialchars($r['title']); ?></td>
    <td>₱<?php echo number_format($r['reward'], 2); ?></td>
    <td><?php echo $r['applied_at']; ?></td>
</tr>

<?php endwhile; ?>

</table>

</div>

</body>
</html>