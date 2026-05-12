<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

$quests = $conn->query("
    SELECT q.*,
        (SELECT COUNT(*) FROM applications a WHERE a.quest_id = q.quest_id) AS applicant_count,
        (SELECT COUNT(*) FROM submissions s WHERE s.quest_id = q.quest_id) AS submission_count
    FROM quests q
    WHERE q.client_id = $user_id
    ORDER BY q.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>My Active Quests</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <?php include 'includes/navbar.php'; ?>

  <div class="dashboard">

    <h1>📋 My Posted Quests</h1>
    <p style="color:#aaa; margin-bottom:1.5rem;">
      Manage your quests and review submissions.
    </p>

    <?php if ($quests->num_rows > 0): ?>

      <div class="quest-grid">

        <?php while ($q = $quests->fetch_assoc()): ?>

          <div class="quest-card">

            <h2><?php echo htmlspecialchars($q['title']); ?></h2>

            <p><?php echo htmlspecialchars($q['description']); ?></p>

            <div class="quest-info">
              💰 ₱<?php echo number_format($q['reward'], 2); ?>
            </div>

            <div class="quest-info">
              📌 Status: <strong><?php echo strtoupper($q['status']); ?></strong>
            </div>

            <div class="quest-info">
              👥 Applicants: <?php echo $q['applicant_count']; ?>
            </div>

            <div class="quest-info">
              📦 Submissions: <?php echo $q['submission_count']; ?>
            </div>

            <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap;">

              <a href="submission-review.php?quest_id=<?php echo $q['quest_id']; ?>" class="btn">
                📦 Review Submissions
              </a>

              <a href="quest-applicants.php?id=<?php echo $q['quest_id']; ?>" class="btn-clear">
                👥 View Applicants
              </a>

            </div>

          </div>

        <?php endwhile; ?>

      </div>

    <?php else: ?>

      <p style="color:#aaa;">You haven’t posted any quests yet.</p>

    <?php endif; ?>

  </div>

  <?php include 'includes/footer.php'; ?>

</body>

</html>