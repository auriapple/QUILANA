<?php
include('db_connect.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['administer_id'])) {
    $administer_id = $conn->real_escape_string($data['administer_id']);
    $status = (int)$data['status'];

    // Update status in administer_assessment table
    $update_query = "
        UPDATE administer_assessment
        SET status = $status
        WHERE administer_id = '$administer_id'
    ";

    if ($conn->query($update_query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'administer_id is missing.']);
}

$conn->close();
?>