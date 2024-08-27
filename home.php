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
    <div class="container-flud admin">
        <?php
        // Output for the total courses and classes (No Design)
        $result = $conn->query("SELECT COUNT(*) as totalCourses FROM course");
        $resTotalCourses = $result->fetch_assoc();
        $totalCourses = $resTotalCourses['totalCourses'];
        echo "Total Number of Courses: ". $totalCourses;
        $result = $conn->query("SELECT COUNT(*) as totalClasses FROM class");
        $resTotalClasses = $result->fetch_assoc();
        $totalClasses = $resTotalClasses['totalClasses'];
        echo "<br>" ."Total Number of Classes: ". $totalClasses;
        ?>
    </div>
    

</body>
</html>
