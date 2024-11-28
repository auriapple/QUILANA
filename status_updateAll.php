<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_connect.php');

if (isset($_POST['class_id']) && isset($_POST['status'])) {
    $class_id = $_POST['class_id'];
    $status = $_POST['status'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;

    if ($status == 1) {
        $query = "UPDATE student_enrollment SET status = ?, if_display = 1 WHERE class_id = ? AND status = 0";
    } else {
        $query = "UPDATE student_enrollment SET status = ?, reason = ?, if_display = 1 WHERE class_id = ? AND status = 0
        ";
    }
    
    $stmt = $conn->prepare($query);

    if ($stmt) {
        if ($reason == null) {
            $stmt->bind_param('ii', $status, $class_id);
        } else {
            $stmt->bind_param('isi', $status, $reason, $class_id);
        }
        

        if ($stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            echo json_encode(['status' => 'success', 'affected' => $affectedRows]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute query']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute query']);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to execute query']);
}
?>
