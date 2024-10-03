<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = intval($_POST['assessment_id']);
    $time_limit = intval($_POST['time_limit']);
    $passing_rate = intval($_POST['passing_rate']);

    $query = "UPDATE assessment SET time_limit = ?, passing_rate = ? WHERE assessment_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iii", $time_limit, $passing_rate, $assessment_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Assessment updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update assessment']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error preparing the SQL query']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
