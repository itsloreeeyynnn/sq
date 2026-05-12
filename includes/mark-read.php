<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;