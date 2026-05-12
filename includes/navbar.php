<?php
include_once 'includes/db.php';
$user_id = $_SESSION['user_id'];

// Get user role
$user_role = $conn->query("SELECT role FROM users WHERE user_id = $user_id")->fetch_assoc()['role'];

// Get unread notifications count
$notif_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = $user_id AND is_read = 0");
$unread_count = $notif_result->fetch_assoc()['total'];

// Get latest 5 notifications
$notifs = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
?>

<nav class="navbar">

    <div class="logo">
        SIDE QUEST
    </div>

    <ul class="nav-links">

        
        <?php switch ($user_role) {
            case 'student':
                echo '<li><a href="student-dashboard.php">Dashboard</a></li>
                        <li><a href="quest-board.php">Quest Board</a></li>';
                break;

            case 'client':
                echo '<li><a href="client-dashboard.php">Dashboard</a></li>
                        <li><a href="post-quest.php">Post Quest</a></li>';
                break;
        }
        ?>
        <li><a href="messages.php">Messages</a></li>
        <li><a href="profile.php">👤 Profile</a></li>



        <!-- Notification Bell -->
        <li class="notif-wrapper">
            <button class="notif-bell" onclick="toggleNotif()">
                🔔
                <?php if ($unread_count > 0): ?>
                    <span class="notif-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <strong>Notifications</strong>
                    <?php if ($unread_count > 0): ?>
                        <a href="mark-read.php" class="notif-mark-read">Mark all read</a>
                    <?php endif; ?>
                </div>

                <?php if ($notifs->num_rows > 0): ?>
                    <?php while ($notif = $notifs->fetch_assoc()): ?>
                        <a href="<?php echo $notif['link'] ?? '#'; ?>"
                            class="notif-item <?php echo $notif['is_read'] == 0 ? 'notif-unread' : ''; ?>">
                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                            <span><?php echo date('M d, g:i A', strtotime($notif['created_at'])); ?></span>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="notif-empty">No notifications yet 🎯</div>
                <?php endif; ?>
            </div>
        </li>

        <li><a href="logout.php">Logout</a></li>

    </ul>

</nav>

<script>
    function toggleNotif() {
        const dropdown = document.getElementById('notifDropdown');
        dropdown.classList.toggle('notif-open');
    }

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        const wrapper = document.querySelector('.notif-wrapper');
        if (!wrapper.contains(e.target)) {
            document.getElementById('notifDropdown').classList.remove('notif-open');
        }
    });
</script>