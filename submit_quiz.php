<?php
include('db_connect.php');
include('auth.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $student_id = $_SESSION['login_id'];
    $answers = $_POST['answers'];
    $score = 0;

    foreach ($answers as $question_id => $option_id) {
        // Check if the selected choice is correct
        $result = $conn->query("SELECT * FROM question_options WHERE option_id = '$option_id' AND is_right = 1");
        if ($result->num_rows > 0) {
            $score++;
        }
    }

    // Store the result in the database
    $conn->query("INSERT INTO student_results (student_id, assessment_id, score) VALUES ('$student_id', '$assessment_id', '$score')");

    // Redirect to a results page or show the score
    header('Location: results.php?assessment_id=' . $assessment_id);
}
?>
