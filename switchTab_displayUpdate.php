<?php
include('db_connect.php');
header('Content-Type: application/json');

// Decode the JSON data sent from JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Check for required fields
if (isset($data['administer_id']) && isset($data['student_id'])) {
    $administer_id = $conn->real_escape_string($data['administer_id']);
    $class_id = $conn->real_escape_string($data['class_id']);
    $student_id = $conn->real_escape_string($data['student_id']);
    $if_display = $data['if_display'] ? 1 : 0; // Convert boolean to integer (1 for true, 0 for false)

    $update_query = "
        UPDATE join_assessment
        SET if_display = '$if_display'
        WHERE administer_id = '$administer_id'AND student_id = '$student_id'
    ";

    // Execute the update query
    if ($conn->query($update_query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing administer_id or student_id.']);
}

// Close the database connection
$conn->close();
?>