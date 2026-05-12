<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

/* ---------------------------
   GET ACTIVE QUESTS
   (accepted applications)
---------------------------- */
$active_quests = $conn->query("
    SELECT q.*, a.status as application_status, a.applied_at
    FROM applications a
    JOIN quests q ON a.quest_id = q.quest_id
    WHERE a.student_id = $user_id
    AND a.status = 'accepted'
    ORDER BY q.deadline ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Active Quests</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

    <h1>⚔️ Your Active Quests</h1>
    <p style="color:#aaa; margin-bottom:1.5rem;">
        These are the quests you are currently working on.
    </p>

    <?php if ($active_quests->num_rows > 0): ?>

        <div class="quest-grid">

            <?php while ($quest = $active_quests->fetch_assoc()): ?>

                <?php
                $deadline_ts = strtotime($quest['deadline']);
                $now = time();
                $diff = $deadline_ts - $now;

                $days = floor($diff / 86400);
                $hours = floor(($diff % 86400) / 3600);

                if ($diff <= 0) {
                    $status_text = "❌ Expired";
                } elseif ($days == 0) {
                    $status_text = "🔥 {$hours}h left";
                } else {
                    $status_text = "⏳ {$days}d {$hours}h left";
                }
                ?>

                <div class="quest-card">

                    <h2><?php echo htmlspecialchars($quest['title']); ?></h2>

                    <p><?php echo htmlspecialchars($quest['description']); ?></p>

                    <div class="quest-info">
                        💰 ₱<?php echo number_format($quest['reward'], 2); ?>
                    </div>

                    <div class="quest-info">
                        📅 Deadline: <?php echo date('M d, Y', strtotime($quest['deadline'])); ?>
                    </div>

                    <div class="quest-info">
                        ⏱ <?php echo $status_text; ?>
                    </div>

                    <div class="quest-info">
                        📌 Status: <strong><?php echo strtoupper($quest['status']); ?></strong>
                    </div>

                    <a href="quest-view.php?id=<?php echo $quest['quest_id']; ?>" class="btn">
                        View Quest
                    </a>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div style="text-align:center; color:#aaa; padding:3rem;">
            <h2>No active quests yet ⚔️</h2>
            <p>Apply to quests to start your journey.</p>
            <a href="quest-board.php" class="btn">Browse Quests</a>
        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>