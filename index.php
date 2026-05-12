<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') {
        header("Location: student-dashboard.php");
        exit();
    }

    if ($_SESSION['role'] === 'client') {
        header("Location: client-dashboard.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>