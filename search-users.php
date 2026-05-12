<?php
include 'includes/auth.php';
include 'includes/db.php';

$current_user = $_SESSION['user_id'];

$search = isset($_GET['search'])
    ? trim(mysqli_real_escape_string($conn, $_GET['search']))
    : '';

if (strlen($search) < 2) {
    exit();
}

$query = "
    SELECT user_id, full_name, role
    FROM users
    WHERE user_id != $current_user
    AND full_name LIKE '%$search%'
    ORDER BY full_name ASC
    LIMIT 20
";

$users = $conn->query($query);

if ($users->num_rows > 0):

    while ($u = $users->fetch_assoc()):
?>

    <div id="user-card" class="quest-card">

        <div class="user-info">

            <h3>
                <?php echo htmlspecialchars($u['full_name']); ?>
            </h3>

            <p>
                <?php echo ucfirst($u['role']); ?>
            </p>

        </div>

        <a
            href="messages.php?user=<?php echo $u['user_id']; ?>"
            id="msg-btn"
            class="btn"
        >
            💬 Message
        </a>

    </div>

<?php
    endwhile;

else:
?>

<p style="color:#aaa;">
    No users found.
</p>

<?php endif; ?>