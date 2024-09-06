<?php
include('db_connect.php');

if (isset($_POST['class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']); // Sanitize the input

    // Join assessments with administer_assessment to get the duration
    $assessments_query = $conn->query("
        SELECT a.assessment_id, a.assessment_name, aa.timelimit, a.topic
        FROM assessment a
        JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
        WHERE aa.class_id = '$class_id'
    ");

    if ($assessments_query->num_rows > 0) {
        echo '<div class="course-container">';
        while ($row = $assessments_query->fetch_assoc()) {
            echo '<div class="course-card">';
            echo '<div class="course-card-body">';
            echo '<div class="course-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
            echo '<div class="course-card-topic">Topic: ' . htmlspecialchars($row['topic']) . '</div>';
            echo '<div class="course-card-duration">Duration: ' . htmlspecialchars($row['timelimit']) . ' minutes</div>'; // Assuming duration is in minutes
            echo '<div class="course-actions">';
            echo '<button class="btn btn-primary btn-sm">Take Assessment</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No assessments available for this class.</p>';
    }
}
?>