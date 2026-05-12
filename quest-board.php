<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];


$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$difficulty = isset($_GET['difficulty']) ? mysqli_real_escape_string($conn, $_GET['difficulty']) : '';
$min_reward = isset($_GET['min_reward']) && $_GET['min_reward'] !== '' ? (int) $_GET['min_reward'] : '';
$max_reward = isset($_GET['max_reward']) && $_GET['max_reward'] !== '' ? (int) $_GET['max_reward'] : '';
$quest_type = isset($_GET['quest_type']) ? $_GET['quest_type'] : '';


$limit = 9; 
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

$where = ["status = 'open'"];

if ($search)
    $where[] = "(title LIKE '%$search%' OR description LIKE '%$search%')";
if ($difficulty)
    $where[] = "difficulty = '$difficulty'";
if ($min_reward !== '')
    $where[] = "reward >= $min_reward";
if ($max_reward !== '')
    $where[] = "reward <= $max_reward";
if ($quest_type === 'solo')
    $where[] = "is_party_quest = 0";
if ($quest_type === 'party')
    $where[] = "is_party_quest = 1";

$where_sql = implode(' AND ', $where);

$total_result = $conn->query("SELECT COUNT(*) as total FROM quests WHERE $where_sql");
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);


$query = "
SELECT 
    q.*,
    COUNT(pm.party_member_id) AS member_count
FROM quests q
LEFT JOIN party_members pm ON q.quest_id = pm.quest_id
WHERE $where_sql
GROUP BY q.quest_id
ORDER BY q.created_at DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($query);
$count = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quest Board</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="page-title">
        <h1>Quest Board ⚔️</h1>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'already_applied'): ?>

        <div class="error-message" style="margin-bottom:1rem;">
            ⚠️ You already applied to this quest.
        </div>

    <?php endif; ?>

    <div class="filter-wrapper">
        <div class="filter-search">
        <form method="GET" action="quest-board.php" style="margin-bottom:1rem; width: 100%;">

            <input type="text" name="search" placeholder="🔍 Search quests..."
                value="<?php echo htmlspecialchars($search); ?>" class="filter-input search">

        </form>
        
        <button type="button" class="filter-btn filter-toggle" onclick="toggleFilters()">
                Filters
        </button>
        </div>

        <form method="GET" action="quest-board.php" class="filter-bar" id="filterBar">

            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

            <select name="difficulty" class="filter-select">
                <option value="">All Difficulties</option>
                <option value="easy" <?php echo $difficulty === 'easy' ? 'selected' : ''; ?>>⚡ Easy</option>
                <option value="medium" <?php echo $difficulty === 'medium' ? 'selected' : ''; ?>>🔥 Medium</option>
                <option value="hard" <?php echo $difficulty === 'hard' ? 'selected' : ''; ?>>💀 Hard</option>
                <option value="risky" <?php echo $difficulty === 'risky' ? 'selected' : ''; ?>>☠️ Risky</option>
            </select>

            <select name="quest_type" class="filter-select">
                <option value="">All Types</option>
                <option value="solo" <?php echo $quest_type === 'solo' ? 'selected' : ''; ?>>⚔️ Solo</option>
                <option value="party" <?php echo $quest_type === 'party' ? 'selected' : ''; ?>>👥 Party</option>
            </select>

            <input type="number" name="min_reward" placeholder="Min ₱" value="<?php echo $min_reward; ?>"
                class="filter-input filter-small">

            <input type="number" name="max_reward" placeholder="Max ₱" value="<?php echo $max_reward; ?>"
                class="filter-input filter-small">

            <button type="submit" class="btn">Apply Filters</button>

            <?php if ($difficulty || $min_reward !== '' || $max_reward !== '' || $quest_type): ?>
                <a href="quest-board.php" class="btn-clear">✕ Clear</a>
            <?php endif; ?>

        </form>

    </div>

    <div style="text-align:center; color:#aaa; margin-bottom:1rem;">
        <?php echo $count; ?> quest<?php echo $count !== 1 ? 's' : ''; ?> found
    </div>

    <div class="pagination" style="text-align:center; color:#aaa; margin-bottom:1rem;">

        <?php
        $params = $_GET;
        unset($params['page']);
        ?>

        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($params); ?>">
                ⬅ Prev
            </a>
        <?php endif; ?>

        <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($params); ?>">
                Next ➡
            </a>
        <?php endif; ?>

    </div>

    <div class="quest-grid">

        <?php if ($count > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <?php
                $deadline_ts = strtotime($row['deadline']);
                $now_ts = time();
                $diff = $deadline_ts - $now_ts;
                $days = floor($diff / 86400);
                $hours = floor(($diff % 86400) / 3600);
                $minutes = floor(($diff % 3600) / 60);

                if ($diff <= 0) {
                    $countdown_label = '❌ Expired';
                    $countdown_class = 'countdown-expired';
                } elseif ($days == 0 && $hours < 24) {
                    $countdown_label = "🔥 {$hours}h {$minutes}m left";
                    $countdown_class = 'countdown-urgent';
                } elseif ($days <= 3) {
                    $countdown_label = "⚠️ {$days}d {$hours}h left";
                    $countdown_class = 'countdown-warning';
                } else {
                    $countdown_label = "⏳ {$days}d {$hours}h left";
                    $countdown_class = 'countdown-safe';
                }
                ?>

                <div class="quest-card">

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <?php if ($row['is_party_quest'] == 1): ?>
                            <span class="badge-party">👥 Party Quest</span>
                        <?php else: ?>
                            <span class="badge-solo">⚔️ Solo Quest</span>
                        <?php endif; ?>

                        <span class="<?php echo $countdown_class; ?>"><?php echo $countdown_label; ?></span>
                    </div>

                    <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>

                    <div class="quest-info">💰 ₱<?php echo number_format($row['reward'], 2); ?></div>
                    <div class="quest-info">⚔️ <?php echo ucfirst($row['difficulty']); ?></div>
                    <div class="quest-info">📅 <?php echo date('M d, Y', strtotime($row['deadline'])); ?></div>

                    <?php if ($row['is_party_quest'] == 1): ?>
                        <?php
                        $quest_id = $row['quest_id'];
                        $members_result = $conn->query("SELECT COUNT(*) as count FROM party_members WHERE quest_id = $quest_id");
                        $member_count = $members_result->fetch_assoc()['count'];
                        ?>
                        <div class="quest-info">👥 <?php echo $member_count; ?> members joined</div>
                    <?php endif; ?>

                    <form method="POST" action="apply-quest.php">
                        <input type="hidden" name="quest_id" value="<?php echo $row['quest_id']; ?>">
                        <input type="hidden" name="is_party" value="<?php echo $row['is_party_quest']; ?>">
                        <button type="submit" class="btn">
                            <?php echo $row['is_party_quest'] == 1 ? '👥 Join Party' : '⚔️ Apply Quest'; ?>
                        </button>
                    </form>

                </div>

            <?php endwhile; ?>

        <?php else: ?>
            <div style="text-align:center; color:#aaa; padding:3rem; width:100%;">
                <h2>😔 No quests found</h2>
                <p>Try adjusting your filters</p>
                <a href="quest-board.php" class="btn" style="margin-top:1rem;">Clear Filters</a>
            </div>
        <?php endif; ?>

    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        function toggleFilters() {
            const filterBar = document.getElementById('filterBar');

            if (filterBar.style.display === 'none' || filterBar.style.display === '') {
                filterBar.style.display = 'flex';
            } else {
                filterBar.style.display = 'none';
            }
        }
    </script>
</body>

</html>