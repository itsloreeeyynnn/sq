<?php
include 'includes/auth.php';
include 'includes/db.php';

$current_user = $_SESSION['user_id'];
$chat_user = isset($_GET['user']) ? (int)$_GET['user'] : 0;

if (!$chat_user) {
    echo json_encode([]);
    exit();
}

$result = $conn->query("
    SELECT 
        m.message_id,
        m.sender_id,
        m.message_text,
        m.sent_at,
        u.full_name
    FROM messages m
    JOIN users u ON u.user_id = m.sender_id
    WHERE 
        (m.sender_id = $current_user AND m.receiver_id = $chat_user)
        OR
        (m.sender_id = $chat_user AND m.receiver_id = $current_user)
    ORDER BY m.sent_at ASC
");

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messages);