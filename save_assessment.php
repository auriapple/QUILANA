<?php
include('db_connect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assessment_id = isset($_POST['assessment_id']) ? $_POST['assessment_id'] : null;
    $faculty_id = $_POST['faculty_id'];
    $assessment_name = $_POST['assessment_name'];
    $assessment_type = $_POST['assessment_type'];
    $assessment_mode = $_POST['assessment_mode'];
    $course_id = $_POST['course_id'];
    $class_id = $_POST['class_id'];
    $topic = $_POST['topic'];

    if ($assessment_id) {
        // Update existing assessment
        $stmt = $conn->prepare("UPDATE assessment SET faculty_id = ?, assessment_name = ?, assessment_type = ?, assessment_mode = ?, course_id = ?, class_id = ?, topic = ? WHERE assessment_id = ?");
        $stmt->bind_param('issiisii', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $class_id, $topic, $assessment_id);
    } else {
        // Add new assessment
        $stmt = $conn->prepare("INSERT INTO assessment (faculty_id, assessment_name, assessment_type, assessment_mode, course_id, class_id, topic) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issiiss', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $class_id, $topic);
    }

    if ($stmt->execute()) {
        echo 1; // Success
    } else {
        echo 0; // Failure
    }

    $stmt->close();
}

$conn->close();
?>
