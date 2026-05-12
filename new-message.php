<?php
include 'includes/auth.php';
include 'includes/db.php';

$current_user = $_SESSION['user_id'];

$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn, $_GET['search'])
    : '';

$query = "
    SELECT *
    FROM users
    WHERE user_id != $current_user
";

if (!empty($search)) {
    $query .= " AND full_name LIKE '%$search%'";
}

$query .= " ORDER BY full_name ASC";

$users = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>New Message</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

    <h1>➕ New Message</h1>

    <form method="GET" style="margin-bottom:1.5rem;">

        <input
            type="text"
            name="search"
            placeholder="Search users..."
            value="<?php echo htmlspecialchars($search); ?>"
            class="filter-input"
        >

    </form>

    <div class="quest-grid">

        <?php while($u = $users->fetch_assoc()): ?>

            <div class="quest-card">

                <h3><?php echo htmlspecialchars($u['full_name']); ?></h3>

                <p>
                    <?php echo ucfirst($u['role']); ?>
                </p>

                <a href="messages.php?user=<?php echo $u['user_id']; ?>" class="btn">
                    💬 Message
                </a>

            </div>

        <?php endwhile; ?>

    </div>

</div>

</body>
</html>