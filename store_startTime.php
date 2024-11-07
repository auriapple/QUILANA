<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $administer_id = $data['administer_id'];
    
    date_default_timezone_set('Asia/Manila');
    $startTime = date('Y-m-d H:i:s');

    $update_sql = "UPDATE administer_assessment SET start_time = ? WHERE administer_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $startTime, $administer_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'start_time' => $startTime]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update start time.']);
    }

    $stmt->close();
    $conn->close();
}
?>