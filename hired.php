<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

/* ---------------------------
   GET HIRED ADVENTURERS
---------------------------- */
$hired = $conn->query("
    SELECT 
        a.application_id,
        a.quest_id,
        a.student_id,
        u.full_name,
        u.level,
        u.xp,
        q.title,
        q.reward,
        a.applied_at
    FROM applications a
    JOIN users u ON a.student_id = u.user_id
    JOIN quests q ON a.quest_id = q.quest_id
    WHERE q.client_id = $user_id
    AND a.status = 'accepted'
    ORDER BY a.applied_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hired Adventurers</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

    <h1>⚔️ Hired Adventurers</h1>
    <p style="color:#aaa; margin-bottom:1.5rem;">
        Your active and previously hired quest members
    </p>

    <?php if ($hired->num_rows > 0): ?>

        <div class="quest-grid">

            <?php while ($h = $hired->fetch_assoc()): ?>

                <div class="quest-card">

                    <h2><?php echo htmlspecialchars($h['full_name']); ?></h2>

                    <p style="color:#aaa;">
                        📌 Quest: <?php echo htmlspecialchars($h['title']); ?>
                    </p>

                    <div class="quest-info">
                        ⭐ Level <?php echo $h['level']; ?> • XP <?php echo $h['xp']; ?>
                    </div>

                    <div class="quest-info">
                        💰 Reward: ₱<?php echo number_format($h['reward'], 2); ?>
                    </div>

                    <div class="quest-info">
                        📅 Hired: <?php echo date('M d, Y', strtotime($h['applied_at'])); ?>
                    </div>

                    <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap;">

                        <a href="messages.php?user=<?php echo $h['student_id']; ?>" class="btn">
                            💬 Message
                        </a>

                        <a href="profile.php?id=<?php echo $h['student_id']; ?>" class="btn-clear">
                            👤 View Profile
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <p style="color:#aaa;">No hired adventurers yet.</p>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>