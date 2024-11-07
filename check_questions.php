<?php
include('db_connect.php');

// Get the assessment ID
$assessment_id = $_POST['assessment_id'];

// Query to check if there are any questions
$result = $conn->query("SELECT COUNT(*) as count FROM questions WHERE assessment_id = '$assessment_id'");
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo 'questions_present';
} else {
    echo 'no_questions';
}
?>
