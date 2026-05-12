<?php
include 'includes/auth.php';
include 'includes/db.php';

$viewer_id  = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $viewer_id;

$is_owner = ($viewer_id === $profile_id);

if ($is_owner && isset($_POST['update'])) {

    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $role      = mysqli_real_escape_string($conn, $_POST['role']);
    $class_id  = (int)$_POST['class_id'];

    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $conn->query("
            UPDATE users 
            SET full_name='$full_name',
                email='$email',
                role='$role',
                class_id=$class_id,
                password='$password'
            WHERE user_id=$profile_id
        ");
    } else {
        $conn->query("
            UPDATE users 
            SET full_name='$full_name',
                email='$email',
                role='$role',
                class_id=$class_id
            WHERE user_id=$profile_id
        ");
    }

    $_SESSION['full_name'] = $full_name;
    $success = "Profile updated successfully!";
}

$user = $conn->query("
    SELECT * FROM users WHERE user_id = $profile_id
")->fetch_assoc();

$badges = $conn->query("
    SELECT b.badge_name, b.description, ub.earned_at
    FROM user_badges ub
    JOIN badges b ON ub.badge_id = b.badge_id
    WHERE ub.user_id = $profile_id
    ORDER BY ub.earned_at DESC
");

$traits = $conn->query("
    SELECT rt.trait_name, rt.emoji
    FROM user_traits ut
    JOIN reputation_traits rt ON ut.trait_id = rt.trait_id
    WHERE ut.user_id = $profile_id
");

$completed = $conn->query("
    SELECT COUNT(*) as total
    FROM submissions
    WHERE student_id = $profile_id
")->fetch_assoc()['total'];

$earnings = $conn->query("
    SELECT COALESCE(SUM(q.reward), 0) as total
    FROM submissions s
    JOIN quests q ON s.quest_id = q.quest_id
    WHERE s.student_id = $profile_id
")->fetch_assoc()['total'];

$classes = [
    1 => '🎨 Creator',
    2 => '✍️ Scribe',
    3 => '💻 Coder',
    4 => '📦 Runner'
];


$xp_needed = $user['level'] * 100;
$xp_percent = $xp_needed > 0
    ? min(100, round(($user['xp'] / $xp_needed) * 100))
    : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

<h1>
    👤 <?php echo htmlspecialchars($user['full_name']); ?>
</h1>

<?php if (!$is_owner): ?>
    <p style="color:#aaa;">Viewing public profile</p>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="profile-grid">


<div class="profile-left">

   
    <div class="profile-card">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
        </div>

        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>

        <p style="color:#a855f7;">
            <?php echo ucfirst($user['role']); ?>
            — <?php echo $classes[$user['class_id']] ?? 'No Class'; ?>
        </p>

        <p style="color:#aaa; font-size:0.85rem;">
            Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
        </p>
    </div>

  
    <div class="profile-card">
        <h3>📊 Stats</h3>

        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo $user['level']; ?></span>
                <span class="stat-label">Level</span>
            </div>

            <div class="stat-item">
                <span class="stat-number"><?php echo $user['xp']; ?></span>
                <span class="stat-label">XP</span>
            </div>

            <div class="stat-item">
                <span class="stat-number"><?php echo $completed; ?></span>
                <span class="stat-label">Quests Done</span>
            </div>

            <div class="stat-item">
                <span class="stat-number">₱<?php echo number_format($earnings, 0); ?></span>
                <span class="stat-label">Earned</span>
            </div>
        </div>

        <div style="margin-top:1rem;">
            <div class="xp-bar-bg">
                <div class="xp-bar-fill" style="width:<?php echo $xp_percent; ?>%;">
                    <span class="xp-bar-label"><?php echo $xp_percent; ?>%</span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="profile-card">
        <h3>⭐ Traits</h3>

        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:0.5rem;">
            <?php while ($t = $traits->fetch_assoc()): ?>
                <span class="badge-party">
                    <?php echo $t['emoji']; ?> <?php echo $t['trait_name']; ?>
                </span>
            <?php endwhile; ?>
        </div>
    </div>

    
    <div class="profile-card">
        <h3>🎖️ Badges</h3>

        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <?php while ($b = $badges->fetch_assoc()): ?>
                <div class="badge-item">
                    🏅 <?php echo $b['badge_name']; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</div>


<?php if ($is_owner): ?>
<div class="profile-right">

    <div class="profile-card">
        <h3>✏️ Edit Profile</h3>

        <form method="POST" style="display:flex; flex-direction:column; gap:10px;">

            <input type="text" name="full_name"
                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                required>

            <input type="email" name="email"
                value="<?php echo htmlspecialchars($user['email']); ?>"
                required>

            <select name="role">
                <option value="student" <?php echo $user['role']=='student'?'selected':''; ?>>Student</option>
                <option value="client" <?php echo $user['role']=='client'?'selected':''; ?>>Client</option>
            </select>

            <select name="class_id">
                <option value="1" <?php echo $user['class_id']==1?'selected':''; ?>>Creator</option>
                <option value="2" <?php echo $user['class_id']==2?'selected':''; ?>>Scribe</option>
                <option value="3" <?php echo $user['class_id']==3?'selected':''; ?>>Coder</option>
                <option value="4" <?php echo $user['class_id']==4?'selected':''; ?>>Runner</option>
            </select>

            <input type="password" name="password" placeholder="New password (optional)">

            <button type="submit" name="update" class="btn">
                Save Changes
            </button>

        </form>

    </div>

</div>
<?php endif; ?>

</div>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>