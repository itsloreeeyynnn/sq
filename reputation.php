<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$traits_result = $conn->query("
    SELECT rt.trait_name, rt.emoji, rt.description 
    FROM user_traits ut 
    JOIN reputation_traits rt ON ut.trait_id = rt.trait_id 
    WHERE ut.user_id = $user_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reputation</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="dashboard">
    <h1>⭐ Your Reputation</h1>
    <p style="color:#aaa; margin-bottom:2rem;">Traits you've earned through your quests</p>

    <div class="dashboard-cards">
        <?php if ($traits_result->num_rows > 0): ?>
            <?php while ($trait = $traits_result->fetch_assoc()): ?>
            <div class="dashboard-card">
                <h2><?php echo $trait['emoji'] ?? '🏅'; ?> <?php echo $trait['trait_name']; ?></h2>
                <p><?php echo $trait['description'] ?? 'A reputation trait you have earned.'; ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="dashboard-card">
                <h2>No traits yet</h2>
                <p>Complete quests to earn reputation traits!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>