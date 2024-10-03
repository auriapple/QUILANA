<?php
include('db_connect.php');

$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = intval($_POST['assessment_id']);
    $passing_rate = floatval($_POST['passing_rate']);
    $max_points = intval($_POST['max_points']);
    $student_count = intval($_POST['student_count']);
    $remaining_points = intval($_POST['remaining_points']);

    $query = "UPDATE assessment SET 
              passing_rate = ?, 
              max_points = ?, 
              student_count = ?, 
              remaining_points = ? 
              WHERE assessment_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("diiii", $passing_rate, $max_points, $student_count, $remaining_points, $assessment_id);
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Speed mode details updated successfully';
        } else {
            $response['message'] = 'Error executing query: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Error preparing query: ' . $conn->error;
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>