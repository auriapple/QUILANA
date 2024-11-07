<?php
include('db_connect.php');

if (isset($_GET['student_id']) && isset($_GET['class_id'])) {
    $student_id = $conn->real_escape_string($_GET['student_id']);
    $class_id = $conn->real_escape_string($_GET['class_id']);

    $query = "SELECT a.assessment_name, sr.score, sr.total_score, 
                     DATE_FORMAT(sr.date_updated, '%Y-%m-%d') AS date_updated
              FROM student_results sr
              JOIN assessment a ON sr.assessment_id = a.assessment_id
              JOIN administer_assessment aa ON sr.assessment_id = aa.assessment_id
              WHERE sr.student_id = ? AND aa.class_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $scores = array();
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($scores);
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('error' => 'Missing student_id or class_id'));
}

$conn->close();
?>