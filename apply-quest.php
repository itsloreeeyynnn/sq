<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$user = $conn->query("SELECT role FROM users WHERE user_id = $user_id")->fetch_assoc();

if ($user['role'] !== 'student') {
    die("Only students can apply for quests.");
}


$quest_id = isset($_POST['quest_id']) ? (int)$_POST['quest_id'] : 0;

$quest_result = $conn->query("
    SELECT * FROM quests
    WHERE quest_id = $quest_id AND status = 'open'
");

if ($quest_result->num_rows === 0) {
    die("Quest not available.");
}

$check = $conn->query("
    SELECT * FROM applications
    WHERE quest_id = $quest_id AND student_id = $user_id
");

if ($check->num_rows > 0) {
    die("You already applied to this quest.");
}

$conn->query("
    INSERT INTO applications (quest_id, student_id, status)
    VALUES ($quest_id, $user_id, 'pending')
");

$quest = $quest_result->fetch_assoc();
$client_id = $quest['client_id'];

$message = "A student applied to your quest: " . $quest['title'];

$conn->query("
    INSERT INTO notifications (user_id, message, link)
    VALUES ($client_id, '$message', 'quest-applicants.php?id=$quest_id')
");

header("Location: quest-board.php?applied=success");
exit();
?>