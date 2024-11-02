<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = $_POST['assessment_id'];

    if ($assessment_id) {
        $stmt = $conn ->prepare("
            SELECT course_id, subject
            FROM assessment
            WHERE assessment_id = ?
        ");
        $stmt->bind_param("i", $assessment_id);
        $stmt->execute();
        $assessment_result = $stmt->get_result();

        if ($row = $assessment_result->fetch_assoc()) {
            $course_id = $row['course_id'];
            $subject = $row['subject'];

            $stmt = $conn->prepare("
                SELECT c.class_id, c.class_name 
                FROM class c 
                WHERE c.course_id = ? 
                AND c.subject = ? 
                AND NOT EXISTS (
                    SELECT 1 
                    FROM administer_assessment 
                    WHERE class_id = c.class_id 
                    AND assessment_id = ? 
                )
            ");
            $stmt->bind_param("isi", $course_id, $subject, $assessment_id);
            $stmt->execute();
            $classes_result = $stmt->get_result();

            $options = '<option value="" disabled selected>Select Class</option>';

            if ($classes_result->num_rows > 0) {
                while ($class_row = $classes_result->fetch_assoc()) {
                    $options .= "<option value='".$class_row['class_id']."'>".$class_row['class_name']."</option>";
                }
            } else {
                $options .= '<option value="" disabled>No classes available</option>';
            }
            echo $options;
        }
        $stmt->close();
    }
    $conn->close();
}