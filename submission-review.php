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
   HANDLE ACTIONS
---------------------------- */
if (isset($_GET['action']) && isset($_GET['submission_id'])) {

    $action = $_GET['action'];
    $submission_id = (int)$_GET['submission_id'];

    // Get submission
    $submission = $conn->query("
        SELECT *
        FROM submissions
        WHERE submission_id = $submission_id
        AND quest_id = $quest_id
    ")->fetch_assoc();

    if ($submission) {

        $student_id = $submission['student_id'];

        // Prevent duplicate approval/rejection
        $remarks = strtolower($submission['remarks'] ?? '');

        $already_approved = strpos($remarks, 'approved') !== false;
        $already_rejected = strpos($remarks, 'rejected') !== false;

        /* ---------------------------
           APPROVE
        ---------------------------- */
        if ($action === 'approve' && !$already_approved && !$already_rejected) {

            // Update submission remarks
            $conn->query("
                UPDATE submissions
                SET remarks = 'APPROVED'
                WHERE submission_id = $submission_id
            ");

            // Complete quest
            $conn->query("
                UPDATE quests
                SET status = 'completed'
                WHERE quest_id = $quest_id
            ");

            // Get reward + student stats
            $data = $conn->query("
                SELECT q.reward, u.xp, u.level
                FROM quests q
                JOIN users u ON u.user_id = $student_id
                WHERE q.quest_id = $quest_id
            ")->fetch_assoc();

            $reward = (float)$data['reward'];
            $xp = (int)$data['xp'];
            $level = (int)$data['level'];

            // XP reward
            $xp_gain = 50;

            $new_xp = $xp + $xp_gain;
            $new_level = $level;

            // Level up system
            while ($new_xp >= ($new_level * 100)) {
                $new_xp -= ($new_level * 100);
                $new_level++;
            }

            // Update student
            $conn->query("
                UPDATE users
                SET xp = $new_xp,
                    level = $new_level
                WHERE user_id = $student_id
            ");

            // Notification
            $message = "Your submission for '{$quest['title']}' was approved! +{$xp_gain} XP and ₱" . number_format($reward, 2) . " earned 🎉";

            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, link)
                VALUES (?, ?, ?)
            ");

            $link = "quest-active.php";

            $stmt->bind_param("iss", $student_id, $message, $link);
            $stmt->execute();
        }

        /* ---------------------------
           REJECT
        ---------------------------- */
        if ($action === 'reject' && !$already_approved && !$already_rejected) {

            $conn->query("
                UPDATE submissions
                SET remarks = 'REJECTED'
                WHERE submission_id = $submission_id
            ");

            // Notify student
            $message = "Your submission for '{$quest['title']}' was rejected. Please revise and submit again.";

            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, link)
                VALUES (?, ?, ?)
            ");

            $link = "quest-active.php";

            $stmt->bind_param("iss", $student_id, $message, $link);
            $stmt->execute();
        }
    }

    header("Location: submission-review.php?quest_id=$quest_id");
    exit();
}

/* ---------------------------
   GET SUBMISSIONS
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

                <?php
                $remarks = strtolower($s['remarks'] ?? '');
                $is_approved = strpos($remarks, 'approved') !== false;
                $is_rejected = strpos($remarks, 'rejected') !== false;
                ?>

                <div class="quest-card">

                    <h2>
                        <?php echo htmlspecialchars($s['full_name']); ?>
                    </h2>

                    <p>
                        📁
                        <a href="<?php echo $s['file_path']; ?>" target="_blank">
                            Download Submission
                        </a>
                    </p>

                    <?php if (!empty($s['submission_link'])): ?>
                        <p>
                            🔗
                            <a href="<?php echo $s['submission_link']; ?>" target="_blank">
                                External Link
                            </a>
                        </p>
                    <?php endif; ?>

                    <p style="color:#aaa;">
                        <?php echo htmlspecialchars($s['remarks'] ?: 'No remarks'); ?>
                    </p>

                    <small>
                        Submitted:
                        <?php echo date('M d, Y h:i A', strtotime($s['submitted_at'])); ?>
                    </small>

                    <div style="margin-top:1rem;">

                        <?php if ($is_approved): ?>

                            <div class="badge-party">
                                ✅ APPROVED
                            </div>

                        <?php elseif ($is_rejected): ?>

                            <div class="badge-solo">
                                ❌ REJECTED
                            </div>

                        <?php else: ?>

                            <div style="display:flex; gap:10px; flex-wrap:wrap;">

                                <a
                                    href="?quest_id=<?php echo $quest_id; ?>&action=approve&submission_id=<?php echo $s['submission_id']; ?>"
                                    class="btn"
                                    onclick="return confirm('Approve this submission?');"
                                >
                                    ✅ Approve
                                </a>

                                <a
                                    href="?quest_id=<?php echo $quest_id; ?>&action=reject&submission_id=<?php echo $s['submission_id']; ?>"
                                    class="btn-clear"
                                    onclick="return confirm('Reject this submission?');"
                                >
                                    ❌ Reject
                                </a>

                            </div>

                        <?php endif; ?>

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