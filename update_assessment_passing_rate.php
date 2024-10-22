<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = intval($_POST['assessment_id']);
    $passing_rate = intval($_POST['passing_rate']);
    $max_warnings = intval($_POST['max_warnings']);

    $query = "UPDATE assessment SET passing_rate = ?, max_warnings = ? WHERE assessment_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iii", $passing_rate, $max_warnings, $assessment_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Passing rate updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update passing rate']);
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
