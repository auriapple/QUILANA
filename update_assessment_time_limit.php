<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = intval($_POST['assessment_id']);
    $time_limit = intval($_POST['time_limit']);

    $query = "UPDATE assessment SET time_limit = ? WHERE assessment_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $time_limit, $assessment_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Time limit updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update time limit']);
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