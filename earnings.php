<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$earnings_result = $conn->query("
    SELECT q.title, q.reward, s.submitted_at 
    FROM submissions s
    JOIN quests q ON s.quest_id = q.quest_id
    WHERE s.student_id = $user_id
    ORDER BY s.submitted_at DESC
");

$total = $conn->query("
    SELECT COALESCE(SUM(q.reward), 0) as total 
    FROM submissions s 
    JOIN quests q ON s.quest_id = q.quest_id 
    WHERE s.student_id = $user_id
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="dashboard">
    <h1>💰 Earnings History</h1>
    <p style="color:#aaa; margin-bottom:1rem;">Total Earned: <strong style="color:#a855f7;">₱<?php echo number_format($total, 2); ?></strong></p>

    <table style="width:100%; border-collapse:collapse; color:#fff;">
        <thead>
            <tr style="border-bottom: 1px solid #444;">
                <th style="padding:12px; text-align:left;">Quest</th>
                <th style="padding:12px; text-align:left;">Amount</th>
                <th style="padding:12px; text-align:left;">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($earnings_result->num_rows > 0): ?>
                <?php while ($row = $earnings_result->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #2a2a2a;">
                    <td style="padding:12px;"><?php echo $row['title']; ?></td>
                    <td style="padding:12px; color:#22c55e;">₱<?php echo number_format($row['reward'], 2); ?></td>
                    <td style="padding:12px; color:#aaa;"><?php echo date('M d, Y', strtotime($row['submitted_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" style="padding:12px; color:#aaa;">No earnings yet. Complete quests to earn rewards!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>