<?php
include 'includes/db.php';

if(isset($_POST['register'])){

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $class_id = $_POST['class_id'];

    $query = "INSERT INTO users
    (full_name, email, password, role, class_id)
    VALUES
    ('$full_name', '$email', '$password', '$role', '$class_id')";

    mysqli_query($conn, $query);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-container">

    <div class="auth-card">

        <h1>Create Account</h1>

        <form method="POST">

            <input type="text" name="full_name" placeholder="Full Name" required>

            <input type="email" name="email" placeholder="Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <select name="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="client">Client</option>
            </select>

            <select name="class_id">
                <option value="">Choose Class</option>
                <option value="1">🎨 Creator</option>
                <option value="2">✍️ Scribe</option>
                <option value="3">💻 Coder</option>
                <option value="4">📦 Runner</option>
            </select>

            <button type="submit" name="register" class="btn">
                Register
            </button>

        </form>

        <div class="auth-link">
            Already have an account?
            <a href="index.php">Login</a>
        </div>

    </div>

</div>

</body>
</html>