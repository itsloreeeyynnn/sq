<?php
include 'includes/auth.php';
include 'includes/db.php';

$current_user = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>New Message</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard">

    <h2>➕ New Message</h2>

    <input
        type="text"
        id="userSearch"
        placeholder="Search users..."
        class="filter-input"
        autocomplete="off"
    >

    <div
        id="searchResults"
        class="search-msg"
        style="margin-top:1.5rem;"
    >

        <p style="color:#aaa;">
            Start typing to search users.
        </p>

    </div>

</div>

<script>

const searchInput = document.getElementById("userSearch");

const resultsBox = document.getElementById("searchResults");

searchInput.addEventListener("keyup", function () {

    const query = this.value.trim();

    if (query.length < 2) {

        resultsBox.innerHTML = `
            <p style="color:#aaa;">
                Type at least 2 characters.
            </p>
        `;

        return;
    }

    fetch("search-users.php?search=" + encodeURIComponent(query))

        .then(res => res.text())

        .then(data => {

            resultsBox.innerHTML = data;

        });

});

</script>

</body>
</html>