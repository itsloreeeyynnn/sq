<?php

include 'includes/auth.php';
include 'includes/db.php';

$submission_id = (int)$_GET['id'];


$query = "
SELECT 
    s.student_id,
    s.quest_id,
    q.reward,
    q.difficulty
FROM submissions s
JOIN quests q ON s.quest_id = q.quest_id
WHERE s.submission_id = $submission_id
";

$data = $conn->query($query)->fetch_assoc();

$student_id = $data['student_id'];
$quest_id = $data['quest_id'];
$difficulty = $data['difficulty'];


$xp_rewards = [
    'easy' => 25,
    'medium' => 50,
    'hard' => 100,
    'risky' => 150
];

$xp_gain = $xp_rewards[$difficulty] ?? 25;


$user = $conn->query("
    SELECT xp, level
    FROM users
    WHERE user_id = $student_id
")->fetch_assoc();

$new_xp = $user['xp'] + $xp_gain;
$new_level = $user['level'];

while ($new_xp >= ($new_level * 100)) {
    $new_xp -= ($new_level * 100);
    $new_level++;
}

$conn->query("
    UPDATE users
    SET xp = $new_xp,
        level = $new_level
    WHERE user_id = $student_id
");

$conn->query("
    UPDATE quests
    SET status = 'completed'
    WHERE quest_id = $quest_id
");

$message = "🎉 Quest completed! You earned $xp_gain XP.";

$conn->query("
    INSERT INTO notifications
    (user_id, message)
    VALUES
    ($student_id, '$message')
");

header("Location: submission-review.php?quest_id=$quest_id");
exit();
?>