<?php
include 'includes/auth.php';
include 'includes/db.php';

$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn, $_GET['search'])
    : '';

$difficulty = isset($_GET['difficulty'])
    ? mysqli_real_escape_string($conn, $_GET['difficulty'])
    : '';

$quest_type = isset($_GET['quest_type'])
    ? mysqli_real_escape_string($conn, $_GET['quest_type'])
    : '';

$min_reward = isset($_GET['min_reward']) && $_GET['min_reward'] !== ''
    ? (int) $_GET['min_reward']
    : '';

$max_reward = isset($_GET['max_reward']) && $_GET['max_reward'] !== ''
    ? (int) $_GET['max_reward']
    : '';

$where = ["status = 'open'"];

if ($search) {
    $where[] = "(title LIKE '%$search%' OR description LIKE '%$search%')";
}

if ($difficulty) {
    $where[] = "difficulty = '$difficulty'";
}

if ($min_reward !== '') {
    $where[] = "reward >= $min_reward";
}

if ($max_reward !== '') {
    $where[] = "reward <= $max_reward";
}

if ($quest_type === 'solo') {
    $where[] = "is_party_quest = 0";
}

if ($quest_type === 'party') {
    $where[] = "is_party_quest = 1";
}

$where_sql = implode(' AND ', $where);

$query = "
SELECT 
    q.*,
    COUNT(pm.party_member_id) AS member_count
FROM quests q
LEFT JOIN party_members pm 
ON q.quest_id = pm.quest_id
WHERE $where_sql
GROUP BY q.quest_id
ORDER BY q.created_at DESC
";

$result = $conn->query($query);

if ($result->num_rows > 0):

while ($row = $result->fetch_assoc()):

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

        <span class="<?php echo $countdown_class; ?>">
            <?php echo $countdown_label; ?>
        </span>

    </div>

    <h2><?php echo htmlspecialchars($row['title']); ?></h2>

    <p><?php echo htmlspecialchars($row['description']); ?></p>

    <div class="quest-info">
        💰 ₱<?php echo number_format($row['reward'], 2); ?>
    </div>

    <div class="quest-info">
        ⚔️ <?php echo ucfirst($row['difficulty']); ?>
    </div>

    <?php if ($row['is_party_quest'] == 1): ?>

        <div class="quest-info">
            👥 <?php echo $row['member_count']; ?>
            / <?php echo $row['max_party_members']; ?> members
        </div>

    <?php endif; ?>

    <form method="POST" action="apply-quest.php">

        <input
            type="hidden"
            name="quest_id"
            value="<?php echo $row['quest_id']; ?>"
        >

        <button type="submit" class="btn">

            <?php echo $row['is_party_quest'] == 1
                ? '👥 Join Party'
                : '⚔️ Apply Quest'; ?>

        </button>

    </form>

</div>

<?php
endwhile;

else:
?>

<div style="text-align:center; color:#aaa; padding:3rem; width:100%;">
    <h2>😔 No quests found</h2>
</div>

<?php endif; ?>