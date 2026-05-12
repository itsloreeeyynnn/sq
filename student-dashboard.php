<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$user_result = $conn->query("SELECT full_name, level, xp FROM users WHERE user_id = $user_id");
$user = $user_result->fetch_assoc();
$current_xp = $user['xp'];
$current_level = $user['level'];

$xp_needed = $current_level * 100;
$xp_percent = $xp_needed > 0 ? min(100, round(($current_xp / $xp_needed) * 100)) : 0;

$rank_titles = [
    1 => '🌱 Novice Adventurer',
    2 => '⚔️ Apprentice',
    3 => '🏹 Skilled Hunter',
    4 => '🛡️ Elite Freelancer',
    5 => '👑 Guild Champion',
];
$rank_title = $rank_titles[$current_level] ?? '👑 Legendary';

$quest_result = $conn->query("SELECT COUNT(*) as total FROM quests WHERE client_id = $user_id OR quest_id IN (SELECT quest_id FROM applications WHERE student_id = $user_id AND status = 'accepted')");
$active_quests = $quest_result->fetch_assoc()['total'];

$traits_result = $conn->query("SELECT rt.trait_name FROM user_traits ut JOIN reputation_traits rt ON ut.trait_id = rt.trait_id WHERE ut.user_id = $user_id");
$traits = [];
while ($row = $traits_result->fetch_assoc()) {
    $traits[] = $row['trait_name'];
}
$traits_display = !empty($traits) ? implode(' • ', $traits) : 'No traits yet';

$badge_result = $conn->query("SELECT COUNT(*) as total FROM user_badges WHERE user_id = $user_id");
$badge_count = $badge_result->fetch_assoc()['total'];
$guild_level = $badge_count + 1;

$earnings_result = $conn->query("SELECT COALESCE(SUM(q.reward), 0) as total FROM quests q JOIN applications a ON a.quest_id = q.quest_id WHERE a.student_id = $user_id AND a.status = 'accepted'");
$total_earnings = $earnings_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">
    <h1>Welcome, <?php echo $_SESSION['full_name']; ?> ⚔️</h1>
    <p style="color:#aaa; margin-bottom:1.5rem;"><?php echo $rank_title; ?></p>

    <div class="xp-container">
        <div class="xp-header">
            <span>⭐ Level <?php echo $current_level; ?></span>
            <span><?php echo $current_xp; ?> / <?php echo $xp_needed; ?> XP</span>
            <span>Level <?php echo $current_level + 1; ?></span>
        </div>
        <div class="xp-bar-bg">
            <div class="xp-bar-fill" style="width: <?php echo $xp_percent; ?>%;">
                <span class="xp-bar-label"><?php echo $xp_percent; ?>%</span>
            </div>
        </div>
        <p style="color:#aaa; font-size:0.85rem; margin-top:0.5rem;">
            <?php echo ($xp_needed - $current_xp); ?> XP needed to reach Level <?php echo $current_level + 1; ?>
        </p>
    </div>

    <div class="dashboard-cards">

        <a href="quest-active.php" class="dashboard-card">
            <h2>🎯 Active Quests</h2>
            <p><?php echo $active_quests; ?></p>
        </a>

        <a href="reputation.php" class="dashboard-card">
            <h2>⭐ Reputation</h2>
            <p><?php echo $traits_display; ?></p>
        </a>

        <a href="guild-rank.php" class="dashboard-card">
            <h2>🏆 Guild Rank</h2>
            <p>Level <?php echo $guild_level; ?></p>
        </a>

        <a href="earnings.php" class="dashboard-card">
            <h2>💰 Total Earnings</h2>
            <p>₱<?php echo number_format($total_earnings, 0); ?></p>
        </a>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>