<?php
include('db_connect.php');
include('auth.php');

if (!isset($_GET['administer_id']) || !isset($_SESSION['login_id'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$administer_id = $conn->real_escape_string($_GET['administer_id']);
$student_id = $_SESSION['login_id'];

$check_submission_query = $conn->query("
    SELECT * FROM student_submission 
    WHERE administer_id = '$administer_id' 
    AND student_id = '$student_id'
");

if ($check_submission_query && $check_submission_query->num_rows > 0) {
    echo json_encode(['status' => 'submitted']);
} else {
    echo json_encode(['status' => 'not submitted']);
}
?>