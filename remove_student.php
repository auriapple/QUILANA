<?php
include('db_connect.php');

// Check if the necessary data is provided
if (isset($_POST['student_id']) && isset($_POST['class_id'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $class_id = $conn->real_escape_string($_POST['class_id']);

    $qry_remove_student = $conn->query("
        UPDATE student_enrollment 
        SET status = 2 
        WHERE student_id = '$student_id' AND class_id = '$class_id'
    ");

    if ($qry_remove_student) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

// Close the database connection
$conn->close();
?>
