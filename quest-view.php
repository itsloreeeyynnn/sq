<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];
$quest_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

/* ---------------------------
   GET QUEST + VERIFY ACCESS
---------------------------- */
$quest_query = $conn->query("
    SELECT q.*, a.status AS application_status
    FROM quests q
    JOIN applications a ON a.quest_id = q.quest_id
    WHERE q.quest_id = $quest_id
    AND a.student_id = $user_id
");

$quest = $quest_query->fetch_assoc();

if (!$quest) {
  die("Quest not found or access denied.");
}

if (isset($_POST['submit_work'])) {

    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $link = mysqli_real_escape_string($conn, $_POST['submission_link']);

    $file_name = $_FILES['submission_file']['name'];
    $tmp_name = $_FILES['submission_file']['tmp_name'];

    $upload_dir = "uploads/submissions/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $unique_file = time() . "_" . basename($file_name);
    $target_path = $upload_dir . $unique_file;

    move_uploaded_file($tmp_name, $target_path);

    $conn->query("
        INSERT INTO submissions 
        (quest_id, student_id, file_path, submission_link, remarks)
        VALUES 
        ($quest_id, $user_id, '$target_path', '$link', '$remarks')
    ");

    $conn->query("
        UPDATE quests 
        SET status = 'completed'
        WHERE quest_id = $quest_id
    ");

    header("Location: quest-view.php?id=$quest_id&submitted=1");
    exit();
}

$submission = $conn->query("
    SELECT * FROM submissions 
    WHERE quest_id = $quest_id AND student_id = $user_id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Quest Details</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <?php include 'includes/navbar.php'; ?>

  <div class="dashboard">

    <h1><?php echo htmlspecialchars($quest['title']); ?></h1>

    <p style="color:#aaa;">
      <?php echo htmlspecialchars($quest['description']); ?>
    </p>

    <div class="quest-info">💰 ₱<?php echo number_format($quest['reward'], 2); ?></div>
    <div class="quest-info">⚔️ <?php echo ucfirst($quest['difficulty']); ?></div>
    <div class="quest-info">📅 Deadline: <?php echo date('M d, Y H:i', strtotime($quest['deadline'])); ?></div>
    <div class="quest-info">📌 Status: <strong><?php echo strtoupper($quest['status']); ?></strong></div>
    <div class="quest-info">🧭 Application Status: <?php echo strtoupper($quest['application_status']); ?></div>

    <hr style="margin:2rem 0; opacity:0.2;">

    <?php if ($quest['application_status'] === 'accepted'): ?>

      <h2>📦 Submit Your Work</h2>

      <?php if (!$submission): ?>

        <form method="POST" enctype="multipart/form-data">

          <div class="drop-zone" id="dropZone">
            <p>📁 Drag & drop your file here</p>
            <p>or click to browse</p>
            <input type="file" name="submission_file" id="fileInput" hidden required>
          </div>

          <input type="text" name="submission_link" placeholder="Optional: GitHub / Drive link">

          <textarea name="remarks" placeholder="Remarks..." rows="4"></textarea>

          <button type="submit" name="submit_work" class="btn">
            Submit Quest
          </button>

        </form>

      <?php else: ?>

        <div style="padding:1rem; background:#1e1e1e; border-radius:10px;">
          <h3>✅ Submitted</h3>
          <p><strong>Link:</strong> <?php echo htmlspecialchars($submission['submission_link']); ?></p>
          <p><strong>Remarks:</strong> <?php echo htmlspecialchars($submission['remarks']); ?></p>
          <p><small>Submitted on <?php echo $submission['submitted_at']; ?></small></p>
        </div>

      <?php endif; ?>

    <?php else: ?>

      <p style="color:#aaa;">
        This quest is not yet active for submission.
      </p>

    <?php endif; ?>

  </div>

  <?php include 'includes/footer.php'; ?>
  <script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropZone.style.border = "2px solid #00ff88";
    });

    dropZone.addEventListener('dragleave', () => {
      dropZone.style.border = "2px dashed #555";
    });

    dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      fileInput.files = e.dataTransfer.files;
      dropZone.innerHTML = "<p>✅ File selected: " + fileInput.files[0].name + "</p>";
    });
  </script>
</body>

</html>