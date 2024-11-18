<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $subject = $_POST['subject'];
    $course_id = $_POST['course_id'];

    if ($class_id && $subject && $course_id) {
        $get_assessments_query = $conn->prepare("
            SELECT assessment_id, assessment_name
            FROM assessment
            WHERE subject = ? AND course_id = ?
        ");
        $get_assessments_query->bind_param("si", $subject, $course_id);
        $get_assessments_query->execute();
        $assessments = $get_assessments_query->get_result();

        $options = '<option value="" disabled selected>Select Assessment</option>';

        if ($assessments->num_rows > 0) {
            while ($assessment = $assessments->fetch_assoc()) {
                $options .= "<option value='".$assessment['assessment_id']."'>".$assessment['assessment_name']."</option>";
            }
        } else {
            $options .= '<option value="" disabled>No assessments available</option>';
        }

        echo $options;
        $get_assessments_query->close();
    }
    $conn->close();
}