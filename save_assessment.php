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
    $subject = $_POST['subject'];
    $topic = $_POST['topic'];

    $time_limit = null;
    if ($assessment_mode == 1) { // Normal Mode
        $time_limit = isset($_POST['time_limit']) ? intval($_POST['time_limit']) : null;
    }

    // For Speed Mode
    $passing_rate = null;
    $max_points = null;
    $student_count = null;
    $remaining_points = null;
    
    if ($assessment_mode == 3) { // Speed Mode
        $passing_rate = isset($_POST['assessment_passing_rate']) ? intval($_POST['assessment_passing_rate']) : null;
        $max_points = isset($_POST['assessment_max_points']) ? intval($_POST['assessment_max_points']) : null;
        $student_count = isset($_POST['assessment_student_count']) ? intval($_POST['assessment_student_count']) : null;
        $remaining_points = isset($_POST['assessment_remaining_points']) ? intval($_POST['assessment_remaining_points']) : null;
    }

    if ($assessment_id) {
        // Update existing assessment
        if ($assessment_mode == 1) {
            $stmt = $conn->prepare("UPDATE assessment SET faculty_id = ?, assessment_name = ?, assessment_type = ?, assessment_mode = ?, course_id = ?, subject = ?, topic = ?, time_limit = ? WHERE assessment_id = ?");
            $stmt->bind_param('issiissii', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $subject, $topic, $time_limit, $assessment_id);
        } elseif ($assessment_mode == 3) {
            $stmt = $conn->prepare("UPDATE assessment SET faculty_id = ?, assessment_name = ?, assessment_type = ?, assessment_mode = ?, course_id = ?, subject = ?, topic = ?, passing_rate = ?, max_points = ?, student_count = ?, remaining_points = ? WHERE assessment_id = ?");
            $stmt->bind_param('issiissiiiii', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $subject, $topic, $passing_rate, $max_points, $student_count, $remaining_points, $assessment_id);
        } else {
            $stmt = $conn->prepare("UPDATE assessment SET faculty_id = ?, assessment_name = ?, assessment_type = ?, assessment_mode = ?, course_id = ?, subject = ?, topic = ?, time_limit = NULL WHERE assessment_id = ?");
            $stmt->bind_param('issiissi', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $subject, $topic, $assessment_id);
        }
    } else {
        // Add new assessment
        if ($assessment_mode == 1) {
            $stmt = $conn->prepare("INSERT INTO assessment (faculty_id, assessment_name, assessment_type, assessment_mode, course_id, subject, topic, time_limit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issiissi', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $subject, $topic, $time_limit);
        } elseif ($assessment_mode == 3) {
            $stmt = $conn->prepare("INSERT INTO assessment (faculty_id, assessment_name, assessment_type, assessment_mode, course_id, subject, topic, passing_rate, max_points, student_count, remaining_points) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issiissiiii', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $subject, $topic, $passing_rate, $max_points, $student_count, $remaining_points);
        } else {
            $stmt = $conn->prepare("INSERT INTO assessment (faculty_id, assessment_name, assessment_type, assessment_mode, course_id, subject, topic) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issiiss', $faculty_id, $assessment_name, $assessment_type, $assessment_mode, $course_id, $subject, $topic);
        }
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
