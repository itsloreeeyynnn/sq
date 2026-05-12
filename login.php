<?php

session_start();

include 'includes/db.php';

if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] === 'student') {
        header("Location: student-dashboard.php");
        exit();
    }

    if ($_SESSION['role'] === 'client') {
        header("Location: client-dashboard.php");
        exit();
    }
}

if (isset($_POST['login'])) {

  $email = $_POST['email'];
  $password = $_POST['password'];

  $query = "SELECT * FROM users
              WHERE email='$email'
              AND password='$password'";

  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {

    $user = mysqli_fetch_assoc($result);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];

    switch ($user['role']) {

      case 'student':
        header("Location: student-dashboard.php");
        break;

      case 'client':
        header("Location: client-dashboard.php");
        break;

      default:
        header("Location: login.php");
        break;
    }

    exit();

  } else {
    $error = "Invalid email or password.";
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Side Quest Login</title>

  <link rel="stylesheet" href="css/style.css">

</head>

<body>

  <div class="auth-container">

    <div class="auth-card">

      <h1>SIDE QUEST</h1>

      <p>
        Every legend starts with a mission.
      </p>

      <?php if (isset($error)) { ?>
        <p><?php echo $error; ?></p>
      <?php } ?>

      <form method="POST">

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login" class="btn">
          Login
        </button>

      </form>

      <div class="auth-link">

        No account?

        <a href="register.php">
          Begin Your Journey
        </a>

      </div>

    </div>

  </div>

  <script src="js/script.js"></script>

</body>

</html>