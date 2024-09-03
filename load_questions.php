<?php
include('db_connect.php');

if (isset($_GET['assessment_id'])) {
    $assessment_id = $_GET['assessment_id'];
    
    $questions_query = $conn->query("SELECT * FROM questions WHERE assessment_id = '$assessment_id' ORDER BY question_order");
    $questions = $questions_query->fetch_all(MYSQLI_ASSOC);

    foreach ($questions as $question) {
        echo "<div class='question'>
                <p>Question: " . htmlspecialchars($question['question_text']) . "</p>
                <p>Type: " . htmlspecialchars($question['question_type']) . "</p>
                <p>Points: " . htmlspecialchars($question['points']) . "</p>
                <p>Correct Answer: " . htmlspecialchars($question['correct_answer']) . "</p>
              </div>";
    }
}
?>
