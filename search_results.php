<?php
include('db_connect.php');
include('auth.php');

if (!isset($_SESSION['login_id'])) {
    exit;
}

$student_id = $_SESSION['login_id'];
$query = isset($_GET['query']) ? $_GET['query'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'quizzes';

// Determine assessment type (1 for quizzes, 2 for exams)
$assessment_type = ($type === 'quizzes') ? 1 : 2;

// Get student's enrolled classes
$classes_query = $conn->query("SELECT c.class_id, c.subject 
                              FROM class c 
                              JOIN student_enrollment s ON c.class_id = s.class_id 
                              WHERE s.student_id = '$student_id' AND s.status='1'");

if ($classes_query->num_rows > 0) {
    while ($class = $classes_query->fetch_assoc()) {
        $search_condition = "";
        if (!empty($query)) {
            $search_query = $conn->real_escape_string($query);
            $search_condition = "AND (
                a.assessment_name LIKE '%$search_query%' 
                OR a.topic LIKE '%$search_query%'
                OR c.subject LIKE '%$search_query%'
            )";
        }

        $assessments_query = $conn->query("
            SELECT DISTINCT a.assessment_id, a.assessment_name, a.topic
            FROM assessment a
            JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
            JOIN class c ON aa.class_id = c.class_id
            WHERE aa.class_id = '" . $class['class_id'] . "' 
            AND a.assessment_type = $assessment_type
            $search_condition
        ");

        $assessments = [];
        while ($row = $assessments_query->fetch_assoc()) {
            $assessments[] = $row;
        }

        if (count($assessments) > 0) {
            $has_results = false;
            
            echo '<div class="content-separator">';
            echo '<span class="content-name">' . htmlspecialchars($class['subject']) . '</span>';
            echo '<hr class="separator-line">';
            echo '</div>';
            
            echo '<div class="' . ($type === 'quizzes' ? 'quizzes' : 'exams') . '-container">';
            
            foreach ($assessments as $assessment) {
                
                // Check if student has results for this assessment
                $results_query = $conn->query("
                    SELECT 1 
                    FROM student_results 
                    WHERE student_id = '$student_id' 
                    AND assessment_id = '" . $assessment['assessment_id'] . "'
                ");

                if ($results_query->num_rows > 0) {
                    $has_results = true;
                    echo '<div class="assessment-card">';
                    echo '<div class="assessment-card-title">' . htmlspecialchars($assessment['assessment_name']) . '</div>';
                    echo '<div class="assessment-card-topic">Topic: ' . htmlspecialchars($assessment['topic']) . '</div>';
                    echo '<button id="viewResult_' . $assessment['assessment_id'] . '" class="main-button" data-id="' . $assessment['assessment_id'] . '" type="button">View Result</button>';
                    echo '</div>';
                }
            }
            
            echo '</div>';
            
            if (!$has_results) {
                if (empty($query)) {
                    echo '<div class="no-records">No ' . $type . ' yet for ' . htmlspecialchars($class['subject']) . '</div>';
                }
            }
        } else if (empty($query)) {
            echo '<div class="no-records">No ' . $type . ' yet for ' . htmlspecialchars($class['subject']) . '</div>';
        }
    }
} else {
    echo '<div class="no-records">No ' . $type . ' yet</div>';
}
?>