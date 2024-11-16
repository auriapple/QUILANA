<?php
include('db_connect.php');
header('Content-Type: application/json');

// Decode the JSON data sent from JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Check for required fields
if (isset($data['enrollment_id'])) {
    $enrollment_id = $conn->real_escape_string($data['enrollment_id']);

    $update_query = "
        UPDATE student_enrollment
        SET if_display = '0'
        WHERE studentEnrollment_id = '$enrollment_id'
    ";

    // Execute the update query
    if ($conn->query($update_query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

// Close the database connection
$conn->close();
?>