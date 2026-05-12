<?php
include 'includes/auth.php';
include 'includes/db.php';

$current_user = $_SESSION['user_id'];

if(isset($_POST['send'])){

    $receiver_id = $_POST['receiver_id'];
    $message_text = $_POST['message_text'];

    $query = "INSERT INTO messages
    (sender_id, receiver_id, message_text)
    VALUES
    ('$current_user', '$receiver_id', '$message_text')";

    mysqli_query($conn, $query);
}

$query = "SELECT
m.*,
u.full_name AS sender_name
FROM messages m
INNER JOIN users u
ON m.sender_id = u.user_id
WHERE receiver_id = '$current_user'
ORDER BY sent_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="messages-container">

    <div class="messages-box">

        <h1>Guild Messages 💬</h1>

        <form method="POST">

            <input type="number" name="receiver_id" placeholder="Receiver User ID" required>

            <textarea name="message_text" placeholder="Write your message..." required></textarea>

            <button type="submit" name="send" class="btn">
                Send Message
            </button>

        </form>

        <div class="message-list">

            <?php while($row = mysqli_fetch_assoc($result)){ ?>

                <div class="message-card">

                    <h3><?php echo $row['sender_name']; ?></h3>

                    <p>
                        <?php echo $row['message_text']; ?>
                    </p>

                    <small>
                        <?php echo $row['sent_at']; ?>
                    </small>

                </div>

            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>