<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

/* ---------------------------
   GET & VALIDATE QUEST ID
---------------------------- */
$quest_id = isset($_GET['quest_id']) ? (int)$_GET['quest_id'] : 0;

if ($quest_id <= 0) {
    die("No quest selected.");
}

/* ---------------------------
   VERIFY OWNERSHIP
---------------------------- */
$quest = $conn->query("
    SELECT * FROM quests 
    WHERE quest_id = $quest_id 
    AND client_id = $user_id
")->fetch_assoc();

if (!$quest) {
    die("Unauthorized access or quest not found.");
}

/* ---------------------------
   HANDLE ACTIONS (APPROVE / REJECT)
---------------------------- */
if (isset($_GET['action']) && isset($_GET['submission_id'])) {

    $action = $_GET['action'];
    $submission_id = (int)$_GET['submission_id'];

    // get submission safely
    $submission = $conn->query("
        SELECT * FROM submissions 
        WHERE submission_id = $submission_id 
        AND quest_id = $quest_id
    ")->fetch_assoc();

    if ($submission) {

        $student_id = $submission['student_id'];

        /* ---------------------------
           APPROVE
        ---------------------------- */
        if ($action === 'approve') {

            // mark submission
            $conn->query("
                UPDATE submissions 
                SET remarks = CONCAT(IFNULL(remarks,''), ' | APPROVED')
                WHERE submission_id = $submission_id
            ");

            // complete quest
            $conn->query("
                UPDATE quests 
                SET status = 'completed'
                WHERE quest_id = $quest_id
            ");

            // get reward + user stats
            $data = $conn->query("
                SELECT q.reward, u.xp, u.level
                FROM quests q, users u
                WHERE q.quest_id = $quest_id
                AND u.user_id = $student_id
            ")->fetch_assoc();

            $reward = (float)$data['reward'];
            $xp = (int)$data['xp'];
            $level = (int)$data['level'];

            // XP system
            $xp_gain = 50;
            $new_xp = $xp + $xp_gain;
            $new_level = $level;

            while ($new_xp >= ($new_level * 100)) {
                $new_xp -= ($new_level * 100);
                $new_level++;
            }

            // update user
            $conn->query("
                UPDATE users 
                SET xp = $new_xp,
                    level = $new_level,
                    balance = COALESCE(balance,0) + $reward
                WHERE user_id = $student_id
            ");

            // notify student
            $conn->query("
                INSERT INTO notifications (user_id, message)
                VALUES (
                    $student_id,
                    'Your submission was approved! +50 XP and ₱$reward earned 🎉'
                )
            ");
        }

        /* ---------------------------
           REJECT
        ---------------------------- */
        if ($action === 'reject') {

            $conn->query("
                UPDATE submissions 
                SET remarks = CONCAT(IFNULL(remarks,''), ' | NEEDS REVISION')
                WHERE submission_id = $submission_id
            ");

            $conn->query("
                INSERT INTO notifications (user_id, message)
                VALUES (
                    $student_id,
                    'Your submission needs revision.'
                )
            ");
        }
    }

    header("Location: submission-review.php?quest_id=$quest_id");
    exit();
}

/* ---------------------------
   GET SUBMISSIONS (ONLY THIS QUEST)
---------------------------- */
$submissions = $conn->query("
    SELECT s.*, u.full_name
    FROM submissions s
    JOIN users u ON s.student_id = u.user_id
    WHERE s.quest_id = $quest_id
    ORDER BY s.submitted_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submission Review</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

    <h1>📦 Submission Review</h1>
    <p style="color:#aaa;">
        <?php echo htmlspecialchars($quest['title']); ?>
    </p>

    <?php if ($submissions->num_rows > 0): ?>

        <div class="quest-grid">

            <?php while ($s = $submissions->fetch_assoc()): ?>

                <div class="quest-card">

                    <h2><?php echo htmlspecialchars($s['full_name']); ?></h2>

                    <p>
                        📁 <a href="<?php echo $s['file_path']; ?>" target="_blank">
                            Download Submission
                        </a>
                    </p>

                    <?php if (!empty($s['submission_link'])): ?>
                        <p>
                            🔗 <a href="<?php echo $s['submission_link']; ?>" target="_blank">
                                External Link
                            </a>
                        </p>
                    <?php endif; ?>

                    <p style="color:#aaa;">
                        <?php echo htmlspecialchars($s['remarks']); ?>
                    </p>

                    <small>Submitted: <?php echo $s['submitted_at']; ?></small>

                    <div style="margin-top:1rem; display:flex; gap:10px;">

                        <a href="?quest_id=<?php echo $quest_id; ?>&action=approve&submission_id=<?php echo $s['submission_id']; ?>" class="btn">
                            ✅ Approve
                        </a>

                        <a href="?quest_id=<?php echo $quest_id; ?>&action=reject&submission_id=<?php echo $s['submission_id']; ?>" class="btn-clear">
                            ❌ Reject
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <p style="color:#aaa; text-align:center;">
            No submissions yet for this quest.
        </p>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>