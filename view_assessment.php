<?php
include('db_connect.php');

if (isset($_GET['id'])) {
    $assessment_id = $conn->real_escape_string($_GET['id']);

    // Fetch the assessment details including time limit
    $qry_assessment = $conn->query("
        SELECT a.assessment_name, aa.timelimit, aa.date_administered, SUM(q.total_points) AS total_points, a.topic
        FROM administer_assessment aa
        JOIN assessment a ON aa.assessment_id = a.assessment_id
        JOIN questions q ON q.assessment_id = a.assessment_id
        WHERE a.assessment_id = '$assessment_id'
        GROUP BY a.assessment_id, a.assessment_name, aa.timelimit, aa.date_administered, a.topic
    ");

    if ($qry_assessment->num_rows > 0) {
        $assessment = $qry_assessment->fetch_assoc();
        echo "<h4><strong>{$assessment['assessment_name']}</strong></h4>";
        echo "<p><strong>Date Administered:</strong> " . htmlspecialchars($assessment['date_administered']) . "</p>";
        echo "<p><strong>Total Points:</strong> " . htmlspecialchars($assessment['total_points']) . "</p>";
        echo "<p><strong>Time Limit (minutes):</strong> " . htmlspecialchars($assessment['timelimit']) . "</p>";
        echo "<p><strong>Topic:</strong> " . htmlspecialchars($assessment['topic']) . "</p>";

        // Fetch questions and their correct options
        $qry_questions = $conn->query("
            SELECT q.question_id, q.question, q.total_points, q.order_by, qo.option_id, qo.option_txt, qo.is_right
            FROM questions q
            LEFT JOIN question_options qo ON q.question_id = qo.question_id
            WHERE q.assessment_id = '$assessment_id'
            ORDER BY q.order_by, qo.option_id
        ");

        if ($qry_questions->num_rows > 0) {
            $questions = [];
            while ($row = $qry_questions->fetch_assoc()) {
                $questions[$row['question_id']]['question'] = htmlspecialchars($row['question']);
                $questions[$row['question_id']]['total_points'] = htmlspecialchars($row['total_points']);
                $questions[$row['question_id']]['options'][] = [
                    'option_id' => htmlspecialchars($row['option_id']),
                    'option_txt' => htmlspecialchars($row['option_txt']),
                    'is_right' => (bool)$row['is_right']
                ];
            }

            echo "<h5>Questions:</h5>";
            echo "<ul>";
            foreach ($questions as $question_id => $details) {
                echo "<li><strong>Question:</strong> " . $details['question'] . " <strong>Points:</strong> " . $details['total_points'];
                echo "<ul>";
                foreach ($details['options'] as $option) {
                    $correct = $option['is_right'] ? "<span style='color: green;'> (Correct)</span>" : "";
                    echo "<li>" . $option['option_txt'] . $correct . "</li>";
                }
                echo "</ul></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No questions found for this assessment.</p>";
        }
    } else {
        echo "<p>Assessment details not found.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}

$conn->close();
?>
