<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $role      = mysqli_real_escape_string($conn, $_POST['role']);
    $class_id  = (int)$_POST['class_id'];

    // Password change (optional)
    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $conn->query("UPDATE users SET full_name='$full_name', email='$email', role='$role', class_id=$class_id, password='$password' WHERE user_id=$user_id");
    } else {
        $conn->query("UPDATE users SET full_name='$full_name', email='$email', role='$role', class_id=$class_id WHERE user_id=$user_id");
    }

    $_SESSION['full_name'] = $full_name;
    $success = "Profile updated successfully!";
}

// Get user info
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

// Get badges
$badges = $conn->query("SELECT b.badge_name, b.description, ub.earned_at FROM user_badges ub JOIN badges b ON ub.badge_id = b.badge_id WHERE ub.user_id = $user_id ORDER BY ub.earned_at DESC");

// Get traits
$traits = $conn->query("SELECT rt.trait_name, rt.emoji FROM user_traits ut JOIN reputation_traits rt ON ut.trait_id = rt.trait_id WHERE ut.user_id = $user_id");

// Get completed quests count
$completed = $conn->query("SELECT COUNT(*) as total FROM submissions WHERE student_id = $user_id")->fetch_assoc()['total'];

// Get total earnings
$earnings = $conn->query("SELECT COALESCE(SUM(q.reward), 0) as total FROM submissions s JOIN quests q ON s.quest_id = q.quest_id WHERE s.student_id = $user_id")->fetch_assoc()['total'];

// Class names
$classes = [1 => '🎨 Creator', 2 => '✍️ Scribe', 3 => '💻 Coder', 4 => '📦 Runner'];

// XP info
$xp_needed = $user['level'] * 100;
$xp_percent = $xp_needed > 0 ? min(100, round(($user['xp'] / $xp_needed) * 100)) : 0;
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
    <h1>👤 My Profile</h1>

    <?php if (isset($success)): ?>
        <div class="alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="profile-grid">

        <!-- LEFT: Info + Stats -->
        <div class="profile-left">

            <!-- Avatar + Name -->
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p style="color:#a855f7;"><?php echo ucfirst($user['role']); ?> — <?php echo $classes[$user['class_id']] ?? 'No Class'; ?></p>
                <p style="color:#aaa; font-size:0.85rem;">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
            </div>

            <!-- Stats -->
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

                <!-- XP Bar -->
                <div style="margin-top:1rem;">
                    <div style="display:flex; justify-content:space-between; color:#aaa; font-size:0.8rem; margin-bottom:6px;">
                        <span>Level <?php echo $user['level']; ?></span>
                        <span><?php echo $user['xp']; ?> / <?php echo $xp_needed; ?> XP</span>
                        <span>Level <?php echo $user['level'] + 1; ?></span>
                    </div>
                    <div class="xp-bar-bg">
                        <div class="xp-bar-fill" style="width:<?php echo $xp_percent; ?>%;">
                            <span class="xp-bar-label"><?php echo $xp_percent; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Traits -->
            <div class="profile-card">
                <h3>⭐ Reputation Traits</h3>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:0.5rem;">
                    <?php if ($traits->num_rows > 0): ?>
                        <?php while ($trait = $traits->fetch_assoc()): ?>
                            <span class="badge-party"><?php echo $trait['emoji'] ?? '🏅'; ?> <?php echo $trait['trait_name']; ?></span>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#aaa;">No traits yet. Complete quests to earn some!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Badges -->
            <div class="profile-card">
                <h3>🎖️ Badges</h3>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:0.5rem;">
                    <?php if ($badges->num_rows > 0): ?>
                        <?php while ($badge = $badges->fetch_assoc()): ?>
                            <div class="badge-item" title="<?php echo $badge['description']; ?>">
                                🏅 <?php echo $badge['badge_name']; ?>
                                <span style="font-size:0.7rem; color:#aaa; display:block;"><?php echo date('M d, Y', strtotime($badge['earned_at'])); ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#aaa;">No badges yet!</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- RIGHT: Edit Form -->
        <div class="profile-right">
            <div class="profile-card">
                <h3>✏️ Edit Profile</h3>
                <form method="POST" style="display:flex; flex-direction:column; gap:12px; margin-top:1rem;">

                    <label style="color:#aaa; font-size:0.85rem;">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="filter-input" style="width:100%;" required>

                    <label style="color:#aaa; font-size:0.85rem;">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="filter-input" style="width:100%;" required>

                    <label style="color:#aaa; font-size:0.85rem;">Role</label>
                    <select name="role" class="filter-select" style="width:100%;">
                        <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="client"  <?php echo $user['role'] === 'client'  ? 'selected' : ''; ?>>Client</option>
                    </select>

                    <label style="color:#aaa; font-size:0.85rem;">Class</label>
                    <select name="class_id" class="filter-select" style="width:100%;">
                        <option value="1" <?php echo $user['class_id'] == 1 ? 'selected' : ''; ?>>🎨 Creator</option>
                        <option value="2" <?php echo $user['class_id'] == 2 ? 'selected' : ''; ?>>✍️ Scribe</option>
                        <option value="3" <?php echo $user['class_id'] == 3 ? 'selected' : ''; ?>>💻 Coder</option>
                        <option value="4" <?php echo $user['class_id'] == 4 ? 'selected' : ''; ?>>📦 Runner</option>
                    </select>

                    <label style="color:#aaa; font-size:0.85rem;">New Password <span style="color:#666;">(leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="New password..." class="filter-input" style="width:100%;">

                    <button type="submit" name="update" class="btn" style="margin-top:0.5rem;">💾 Save Changes</button>

                </form>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>