<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];
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

<!-- FILTER AREA -->
<div class="filter-wrapper">

    <div class="filter-search">

        <input
            type="text"
            id="searchInput"
            placeholder="🔍 Search quests..."
            class="filter-input search"
        >

        <button type="button" class="filter-btn filter-toggle" onclick="toggleFilters()">
            Filters
        </button>

    </div>

    <div class="filter-bar" id="filterBar">

        <select id="difficulty" class="filter-select">
            <option value="">All Difficulties</option>
            <option value="easy">⚡ Easy</option>
            <option value="medium">🔥 Medium</option>
            <option value="hard">💀 Hard</option>
            <option value="risky">☠️ Risky</option>
        </select>

        <select id="quest_type" class="filter-select">
            <option value="">All Types</option>
            <option value="solo">⚔️ Solo</option>
            <option value="party">👥 Party</option>
        </select>

        <input
            type="number"
            id="min_reward"
            placeholder="Min ₱"
            class="filter-input filter-small"
        >

        <input
            type="number"
            id="max_reward"
            placeholder="Max ₱"
            class="filter-input filter-small"
        >

    </div>

</div>

<!-- QUEST RESULTS -->
<div id="questGrid" class="quest-grid"></div>

<?php include 'includes/footer.php'; ?>

<script>

function toggleFilters() {
    const filterBar = document.getElementById('filterBar');

    filterBar.style.display =
        (filterBar.style.display === 'none' || filterBar.style.display === '')
        ? 'flex'
        : 'none';
}

/* ---------------------------
   AJAX LOAD QUESTS
---------------------------- */
function loadQuests() {

    const search = document.getElementById('searchInput').value;
    const difficulty = document.getElementById('difficulty').value;
    const questType = document.getElementById('quest_type').value;
    const minReward = document.getElementById('min_reward').value;
    const maxReward = document.getElementById('max_reward').value;

    fetch(`fetch-quests.php?search=${encodeURIComponent(search)}&difficulty=${difficulty}&quest_type=${questType}&min_reward=${minReward}&max_reward=${maxReward}`)
        .then(res => res.text())
        .then(data => {
            document.getElementById('questGrid').innerHTML = data;
        });
}

/* ---------------------------
   EVENTS (LIVE SEARCH)
---------------------------- */
document.getElementById('searchInput').addEventListener('input', loadQuests);
document.getElementById('difficulty').addEventListener('change', loadQuests);
document.getElementById('quest_type').addEventListener('change', loadQuests);
document.getElementById('min_reward').addEventListener('input', loadQuests);
document.getElementById('max_reward').addEventListener('input', loadQuests);

/* INITIAL LOAD */
loadQuests();

</script>

</body>
</html>