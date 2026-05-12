<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];


$user = $conn->query("SELECT full_name, level, xp FROM users WHERE user_id = $user_id")->fetch_assoc();

$badges_result = $conn->query("
    SELECT b.badge_name, b.description, ub.earned_at 
    FROM user_badges ub 
    JOIN badges b ON ub.badge_id = b.badge_id 
    WHERE ub.user_id = $user_id 
    ORDER BY ub.earned_at DESC
");


$ranks = [
    ['level' => 1, 'title' => 'Novice Adventurer', 'emoji' => '🌱', 'xp_needed' => 0],
    ['level' => 2, 'title' => 'Apprentice', 'emoji' => '⚔️', 'xp_needed' => 100],
    ['level' => 3, 'title' => 'Skilled Hunter', 'emoji' => '🏹', 'xp_needed' => 300],
    ['level' => 4, 'title' => 'Elite Freelancer', 'emoji' => '🛡️', 'xp_needed' => 600],
    ['level' => 5, 'title' => 'Guild Champion', 'emoji' => '👑', 'xp_needed' => 1000],
];

$current_level = $user['level'];
$current_xp = $user['xp'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guild Rank</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="dashboard">
    <h1>🏆 Guild Rank</h1>
    <p style="color:#aaa; margin-bottom:2rem;">Your current standing in the guild — Level <?php echo $current_level; ?> | <?php echo $current_xp; ?> XP</p>

    <div class="dashboard-cards">
        <?php foreach ($ranks as $rank): ?>
        <div class="dashboard-card" style="<?php echo $rank['level'] == $current_level ? 'border: 2px solid #a855f7;' : ''; ?>">
            <h2><?php echo $rank['emoji']; ?> <?php echo $rank['title']; ?></h2>
            <p>Level <?php echo $rank['level']; ?> — <?php echo $rank['xp_needed']; ?> XP required</p>
            <?php if ($rank['level'] == $current_level): ?>
                <p style="color:#a855f7; font-weight:bold;">← You are here</p>
            <?php elseif ($rank['level'] < $current_level): ?>
                <p style="color:#22c55e;">✅ Completed</p>
            <?php else: ?>
                <p style="color:#aaa;">🔒 Locked</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <h2 style="margin-top:3rem;">🎖️ Your Badges</h2>
    <div class="dashboard-cards" style="margin-top:1rem;">
        <?php if ($badges_result->num_rows > 0): ?>
            <?php while ($badge = $badges_result->fetch_assoc()): ?>
            <div class="dashboard-card">
                <h2>🏅 <?php echo $badge['badge_name']; ?></h2>
                <p><?php echo $badge['description']; ?></p>
                <p style="color:#aaa; font-size:0.4rem;">Earned: <?php echo date('M d, Y', strtotime($badge['earned_at'])); ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="dashboard-card">
                <h2>No badges yet</h2>
                <p>Complete quests to earn badges!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>