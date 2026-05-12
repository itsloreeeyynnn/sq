<?php
include 'includes/auth.php';
include 'includes/db.php';

$client_id = $_SESSION['user_id'];

/* =========================
   POST QUEST
========================= */
if (isset($_POST['post_quest'])) {

    $category_id = (int) $_POST['category_id'];

    $title = mysqli_real_escape_string(
        $conn,
        trim($_POST['title'])
    );

    $description = mysqli_real_escape_string(
        $conn,
        trim($_POST['description'])
    );

    $reward = (float) $_POST['reward'];

    $deadline = $_POST['deadline'];

    $difficulty = mysqli_real_escape_string(
        $conn,
        $_POST['difficulty']
    );

    // SOLO OR PARTY
    $is_party_quest = isset($_POST['is_party_quest'])
        ? (int) $_POST['is_party_quest']
        : 0;

    // PARTY SIZE
    $max_party_members = null;

    if ($is_party_quest == 1) {

        $max_party_members = (int) $_POST['max_party_members'];

        if ($max_party_members < 2) {
            $error = "Party quests require at least 2 adventurers.";
        }
    }

    if (!isset($error)) {

        if ($reward < 1) {

            $error = "Reward must be greater than 0.";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO quests
                (
                    client_id,
                    category_id,
                    title,
                    description,
                    reward,
                    deadline,
                    difficulty,
                    is_party_quest,
                    max_party_members
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "iissdssii",
                $client_id,
                $category_id,
                $title,
                $description,
                $reward,
                $deadline,
                $difficulty,
                $is_party_quest,
                $max_party_members
            );

            $stmt->execute();

            header("Location: post-quest.php?success=1");
            exit();
        }
    }
}

/* =========================
   SUCCESS MESSAGE
========================= */
if (isset($_GET['success'])) {
    $success = "Quest posted successfully!";
}

/* =========================
   QUEST LIST
========================= */
$quests = $conn->query("
    SELECT q.*,

        (
            SELECT COUNT(*)
            FROM applications a
            WHERE a.quest_id = q.quest_id
        ) AS applicant_count,

        (
            SELECT COUNT(*)
            FROM submissions s
            WHERE s.quest_id = q.quest_id
        ) AS submission_count

    FROM quests q

    WHERE q.client_id = $client_id

    ORDER BY q.created_at DESC
");

/* =========================
   CATEGORIES
========================= */
$categories = $conn->query("
    SELECT *
    FROM categories
    ORDER BY category_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Post Quest</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="form-container">

<<<<<<< HEAD
    <!-- =========================
         POST FORM
    ========================== -->
=======
    
>>>>>>> d76399a865b491c266c2b555aaa38f94e96dcee7
    <div class="form-card profile-card">

        <h2>⚔️ Post a Side Quest</h2>

        <?php if (isset($success)) { ?>

            <div class="success-message">
                <?php echo $success; ?>
            </div>

        <?php } ?>

        <?php if (isset($error)) { ?>

            <div class="error-message">
                <?php echo $error; ?>
            </div>

        <?php } ?>

        <form method="POST">

            <!-- TITLE -->
            <input
                type="text"
                name="title"
                placeholder="Quest Title"
                required
            >

            <!-- DESCRIPTION -->
            <textarea
                name="description"
                placeholder="Quest Description"
                required
            ></textarea>

            <!-- REWARD -->
            <input
                type="number"
                step="0.01"
                min="1"
                name="reward"
                placeholder="Reward Amount"
                required
            >

            <!-- DEADLINE -->
            <input
                type="datetime-local"
                name="deadline"
                required
            >

           
            <select name="category_id" required>

                <option value="">
                    Select Category
                </option>

                <?php while ($cat = $categories->fetch_assoc()): ?>

                    <option value="<?php echo $cat['category_id']; ?>">

                        <?php echo htmlspecialchars($cat['category_name']); ?>

                    </option>

                <?php endwhile; ?>

            </select>

           
            <select name="difficulty" required>

                <option value="">
                    Select Difficulty
                </option>

                <option value="easy">
                    ⚡ Easy
                </option>

                <option value="medium">
                    🔥 Medium
                </option>

                <option value="hard">
                    💀 Hard
                </option>

                <option value="risky">
                    ☠️ Risky
                </option>

            </select>

            <!-- QUEST TYPE -->
            <select
                name="is_party_quest"
                id="questType"
                required
            >

                <option value="0">
                    ⚔️ Solo Quest
                </option>

                <option value="1">
                    👥 Party Quest
                </option>

            </select>

            <!-- PARTY OPTIONS -->
            <div
                id="partyOptions"
                style="display:none;"
            >

                <input
                    type="number"
                    name="max_party_members"
                    min="2"
                    placeholder="Maximum Party Members"
                >

            </div>

            <!-- SUBMIT -->
            <button
                type="submit"
                name="post_quest"
                class="btn"
            >
                Publish Quest
            </button>

        </form>

    </div>

<<<<<<< HEAD
    <!-- =========================
         QUEST LIST
    ========================== -->
=======
    
>>>>>>> d76399a865b491c266c2b555aaa38f94e96dcee7
    <div class="quest-list">

        <h2 style="margin-bottom:1rem;">
            📋 Your Quests
        </h2>

        <?php if ($quests->num_rows > 0): ?>

            <div class="quest-grid">

                <?php while ($q = $quests->fetch_assoc()): ?>

                    <div class="quest-card">

                        <h2>
                            <?php echo htmlspecialchars($q['title']); ?>
                        </h2>

                        <p>
                            <?php echo htmlspecialchars($q['description']); ?>
                        </p>

                        <div class="quest-info">
                            💰 ₱<?php echo number_format($q['reward'], 2); ?>
                        </div>

                        <div class="quest-info">

                            📌 Status:

                            <strong>
                                <?php echo strtoupper($q['status']); ?>
                            </strong>

                        </div>

                        <!-- QUEST TYPE -->
                        <div class="quest-info">

                            <?php if ($q['is_party_quest'] == 1): ?>

                                👥 Party Quest

                            <?php else: ?>

                                ⚔️ Solo Quest

                            <?php endif; ?>

                        </div>

                        <!-- PARTY SIZE -->
                        <?php if ($q['is_party_quest'] == 1): ?>

                            <div class="quest-info">

                                👥 Party Size:
                                <?php echo $q['max_party_members']; ?>

                            </div>

                        <?php endif; ?>

                        <div class="quest-info">

                            👥 Applicants:
                            <?php echo $q['applicant_count']; ?>

                        </div>

                        <div class="quest-info">

                            📦 Submissions:
                            <?php echo $q['submission_count']; ?>

                        </div>

                        <div class="quest-info">

                            📅 Deadline:

                            <?php echo date(
                                'M d, Y h:i A',
                                strtotime($q['deadline'])
                            ); ?>

                        </div>

                        <div
                            style="
                                margin-top:1rem;
                                display:flex;
                                gap:10px;
                                flex-wrap:wrap;
                            "
                        >

                            <a
                                href="submission-review.php?quest_id=<?php echo $q['quest_id']; ?>"
                                class="btn"
                            >
                                📦 Review Submissions
                            </a>

                            <a
                                href="hired.php?quest_id=<?php echo $q['quest_id']; ?>"
                                class="btn-clear"
                            >
                                👥 Hired Adventurers
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div
                class="quest-card"
                style="text-align:center;"
            >

                <h2>
                    No quests posted yet ⚔️
                </h2>

                <p style="color:#aaa;">

                    Your posted quests will appear here.

                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<!-- =========================
     PARTY QUEST TOGGLE
========================= -->
<script>

const questType = document.getElementById("questType");

const partyOptions = document.getElementById("partyOptions");

function togglePartyOptions() {

    if (questType.value == "1") {

        partyOptions.style.display = "block";

    } else {

        partyOptions.style.display = "none";

    }
}

togglePartyOptions();

questType.addEventListener(
    "change",
    togglePartyOptions
);

</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>