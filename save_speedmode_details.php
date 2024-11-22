<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = intval($_POST['assessment_id']);
    $passing_rate = intval($_POST['passing_rate']);
    $max_points = intval($_POST['max_points']);
    $student_count = intval($_POST['student_count']);
    $remaining_points = intval($_POST['remaining_points']);
    $max_warnings = intval($_POST['max_warnings']);

    $query = "UPDATE assessment SET 
    passing_rate = ?, 
    max_points = ?, 
    student_count = ?, 
    remaining_points = ?,
    max_warnings = ? 
    WHERE assessment_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iiiiii", $passing_rate, $max_points, $student_count, $remaining_points, $max_warnings, $assessment_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Speed mode details updated successfully']);
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
