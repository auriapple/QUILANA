<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];

    if ($course_id) {
        $stmt = $conn->prepare("SELECT DISTINCT subject FROM class WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Initialize an array to store unique subjects
        $subjects = [];

        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='".$row['subject']."'>".$row['subject']."</option>";
        }

        // Generate dropdown options
        $options = '<option value="">Select Subject</option>';
        foreach ($subjects as $subject => $class_id) {
            $options .= "<option value='".$class_id."'>".$subject."</option>";
        }

        echo $options;
        $stmt->close();
    }
    $conn->close();
}
