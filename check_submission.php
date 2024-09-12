<?php
include('db_connect.php');
include('auth.php');

if (!isset($_GET['assessment_id']) || !isset($_SESSION['user_id'])) {
    echo 'error';
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$student_id = $_SESSION['login_id'];

$check_submission_query = $conn->query("
    SELECT * FROM student_results 
    WHERE assessment_id = '$assessment_id' 
    AND student_id = '$student_id'
");

if ($check_submission_query->num_rows > 0) {
    echo 'submitted';
} else {
    echo 'not_submitted';
}
?>
