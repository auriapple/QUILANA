<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $assessment_id = $_POST['assessment_id'];
    $class_id = $_POST['class_id'];
    $faculty_id = $_POST['faculty_id'];

    $checkStmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM schedule_assessments 
        WHERE assessment_id = ? AND class_id = ?
    ");
    $checkStmt->bind_param("si", $assessment_id, $class_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo 'exists';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO schedule_assessments (date_scheduled, assessment_id, class_id, faculty_id) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssii", $date, $assessment_id, $class_id, $faculty_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }

        $stmt->close();
    }

    $conn->close();
}
?>