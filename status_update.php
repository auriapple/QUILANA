<?php
include('db_connect.php');

if (isset($_POST['class_id']) && isset($_POST['student_id']) && isset($_POST['status'])) {
    $class_id = $_POST['class_id'];
    $student_id = $_POST['student_id'];
    $status = $_POST['status'];

    $query = "UPDATE student_enrollment SET status = ? WHERE class_id = ? AND student_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('iii', $status, $class_id, $student_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }

        $stmt->close();
    } else {
        echo 'error';
    }

    $conn->close();
} else {
    echo 'error';
}
?>
