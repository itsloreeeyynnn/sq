<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];


$user = $conn->query("
    SELECT role
    FROM users
    WHERE user_id = $user_id
")->fetch_assoc();

if ($user['role'] !== 'student') {

    header("Location: quest-board.php?error=students_only");
    exit();
}


$quest_id = isset($_POST['quest_id'])
    ? (int)$_POST['quest_id']
    : 0;

if ($quest_id <= 0) {

    header("Location: quest-board.php?error=invalid_quest");
    exit();
}


$quest_result = $conn->query("
    SELECT *
    FROM quests
    WHERE quest_id = $quest_id
    AND status = 'open'
");

if ($quest_result->num_rows === 0) {

    header("Location: quest-board.php?error=quest_unavailable");
    exit();
}


$check = $conn->query("
    SELECT application_id
    FROM applications
    WHERE quest_id = $quest_id
    AND student_id = $user_id
");

if ($check->num_rows > 0) {

    header("Location: quest-board.php?error=already_applied");
    exit();
}


$conn->query("
    INSERT INTO applications
    (quest_id, student_id, status)
    VALUES
    ($quest_id, $user_id, 'pending')
");


$quest = $quest_result->fetch_assoc();

$client_id = $quest['client_id'];

$message = "A student applied to your quest: " . $quest['title'];

$stmt = $conn->prepare("
    INSERT INTO notifications
    (user_id, message, link)
    VALUES (?, ?, ?)
");

$link = "quest-applicants.php?id=$quest_id";

$stmt->bind_param("iss", $client_id, $message, $link);
$stmt->execute();


header("Location: quest-board.php?applied=success");
exit();
?>