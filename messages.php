<?php
include 'includes/auth.php';
include 'includes/db.php';

$current_user = $_SESSION['user_id'];

$chat_user = isset($_GET['user']) ? (int)$_GET['user'] : 0;


if (isset($_POST['send']) && $chat_user) {

    $message = mysqli_real_escape_string($conn, $_POST['message_text']);

    $conn->query("
        INSERT INTO messages (sender_id, receiver_id, message_text)
        VALUES ($current_user, $chat_user, '$message')
    ");

    header("Location: messages.php?user=$chat_user");
    exit();
}


$conversations = $conn->query("
    SELECT 
        u.user_id,
        u.full_name,
        m.message_text,
        m.sent_at
    FROM users u
    INNER JOIN messages m ON m.message_id = (
        SELECT m2.message_id
        FROM messages m2
        WHERE 
            (m2.sender_id = u.user_id AND m2.receiver_id = $current_user)
            OR
            (m2.sender_id = $current_user AND m2.receiver_id = u.user_id)
        ORDER BY m2.sent_at DESC
        LIMIT 1
    )
    WHERE u.user_id != $current_user
    ORDER BY m.sent_at DESC
");


$chat_user_info = null;

if ($chat_user) {
    $chat_user_info = $conn->query("
        SELECT full_name FROM users WHERE user_id = $chat_user
    ")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages</title>
<link rel="stylesheet" href="css/style.css">


</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="messages-container">

<div class="messages-layout">

   
    <div class="convo-list">

        <h3>💬 Conversations</h3>

        <?php while($c = $conversations->fetch_assoc()): ?>

            <a href="messages.php?user=<?php echo $c['user_id']; ?>" class="convo-item">

                <strong><?php echo htmlspecialchars($c['full_name']); ?></strong>

                <small>
                    <?php echo htmlspecialchars(substr($c['message_text'], 0, 40)); ?>...
                </small>

            </a>

        <?php endwhile; ?>

    </div>

  
    <div class="chat-box">

        <?php if ($chat_user): ?>

            <h3>Chat with <?php echo htmlspecialchars($chat_user_info['full_name']); ?></h3>

            <div class="chat-thread" id="chatThread"></div>

            <form method="POST" class="chat-input">

                <input 
                    type="text" 
                    name="message_text" 
                    placeholder="Type message..." 
                    required
                    class="msg-field"
                    style="flex:1;"
                >

                <button class="msg-btn" name="send">Send</button>

            </form>

        <?php else: ?>

            <p style="color:#aaa;">Select a conversation to start chatting</p>

        <?php endif; ?>

    </div>

</div>

</div>

<?php if ($chat_user): ?>
<script>
const chatUser = <?php echo $chat_user; ?>;
const currentUser = <?php echo $current_user; ?>;

function loadMessages() {
    fetch("fetch-messages.php?user=" + chatUser)
        .then(res => res.json())
        .then(data => {

            const chat = document.getElementById("chatThread");
            chat.innerHTML = "";

            data.forEach(msg => {

                const div = document.createElement("div");
                div.classList.add("msg");

                if (msg.sender_id == currentUser) {
                    div.classList.add("me");
                }

                div.innerHTML = `
                    <p>${msg.message_text}</p>
                    <div style="font-size:10px; color:#aaa;">
                        ${msg.full_name} • ${msg.sent_at}
                    </div>
                `;

                chat.appendChild(div);
            });

            chat.scrollTop = chat.scrollHeight;
        });
}

loadMessages();

setInterval(loadMessages, 2000);
</script>
<?php endif; ?>

</body>
</html>