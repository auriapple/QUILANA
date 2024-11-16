<?php
include('db_connect.php');

if (isset($_POST['class_id']) && isset($_POST['student_id']) && isset($_POST['status'])) {
    $class_id = $_POST['class_id'];
    $student_id = $_POST['student_id'];
    $status = $_POST['status'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;

    if ($status == 1) {
        $query = "UPDATE student_enrollment SET status = ?, if_display = 1 WHERE class_id = ? AND student_id = ?";
    } else {
        $query = "UPDATE student_enrollment SET status = ?, reason = ?, if_display = 1 WHERE class_id = ? AND student_id = ?";
    }
    
    $stmt = $conn->prepare($query);

    if ($stmt) {
        if ($reason == null) {
            $stmt->bind_param('iii', $status, $class_id, $student_id);
        } else {
            $stmt->bind_param('isii', $status, $reason, $class_id, $student_id);
        }
        

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error 1';
        }

        $stmt->close();
    } else {
        echo 'error 2';
    }

    $conn->close();
} else {
    echo 'error 3';
}
?>
