<?php
include('db_connect.php');
include('auth.php');

if (isset($_POST['class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $student_id = $_SESSION['login_id'];

    // Query to get all assessments for the class
    $total_assessments_query = $conn->query("
        SELECT a.assessment_id, a.assessment_name, a.time_limit, a.topic, a.assessment_mode, a.assessment_type, aa.status
        FROM assessment a
        JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
        WHERE aa.class_id = '$class_id'
    ");

    // Count total assessments
    $total_assessments = $total_assessments_query->num_rows;

    // Query to get assessments taken by the student
    $taken_assessments_query = $conn->query("
        SELECT DISTINCT assessment_id
        FROM student_submission
        WHERE student_id = '$student_id'
        AND assessment_id IN (
            SELECT a.assessment_id
            FROM assessment a
            JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
            WHERE aa.class_id = '$class_id'
        )
    ");

    // Count taken assessments
    $taken_assessments = $taken_assessments_query->num_rows;

    // Check if all assessments have been taken
    if ($total_assessments == $taken_assessments) {
        echo '<p class="no-records">No assessments available for this class.</p>';
    } else {
        echo '<div class="assessment-container">';
        // Display assessment details
        while ($row = $total_assessments_query->fetch_assoc()) {
            // Check if the student has already taken the assessment
            $assessment_query = $conn->query("
                SELECT 1
                FROM student_submission
                WHERE student_id = '$student_id' AND assessment_id = '" . $row['assessment_id'] . "'
            ");

            // Show assessments that aren't taken yet
            if ($assessment_query->num_rows == 0) {
                // Determine button text based on assessment type
                $button_text = $row['assessment_type'] == 1 ? 'Take Quiz' : 'Take Exam';

                // Set the assessment mode text
                $assessment_mode = '';
                $assessment_mode = $row['assessment_mode'] == 1 ? 'Normal Mode' : ($row['assessment_mode'] == 2 ? 'Quiz Bee Mode' : 'Speed Mode');
                
                // Determine redirection URL based on assessment mode
                $redirect_url = '';
                if ($row['status'] == 0) {
                    // Redirect to waiting room if status is 0
                    $redirect_url = 'waiting_room.php';
                } elseif ($row['status'] == 1) {
                    // Redirect to respective assessment page based on mode
                    if ($row['assessment_mode'] == 1) {
                        $redirect_url = 'assessment_mode_1.php';
                    } elseif ($row['assessment_mode'] == 2) {
                        $redirect_url = 'assessment_mode_2.php';
                    } elseif ($row['assessment_mode'] == 3) {
                        $redirect_url = 'assessment_mode_3.php';
                    }
                }

                // Display assessment card information
                echo '<div class="assessment-card">';
                echo '<div class="assessment-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
                echo '<div class="assessment-card-topic">Topic: ' . htmlspecialchars($row['topic']) . '</div>';
                echo '<div class="assessment-card-mode">Mode: ' . htmlspecialchars($assessment_mode) . '</div>';
                echo '<div class="assessments-actions">';
                echo '<a href="' . htmlspecialchars($redirect_url) . '?assessment_id=' . htmlspecialchars($row['assessment_id']) . '&student_id=' . htmlspecialchars($student_id) . '&class_id=' . htmlspecialchars($class_id) . '" class="take-assessment-link">';
                echo '<button id="takeAssessment_' . $row['assessment_id'] . '" class="main-button">' . htmlspecialchars($button_text) . '</button>';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>';
    }
}
?>