<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';

header("Location: $redirect");
exit();
?>