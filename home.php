<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <title>Dashboard | Quilana</title>
</head>
<body>
    <?php include 'nav_bar.php'; ?>
    
</body>
</html>
