<?php
// Start session to access session variables
session_start();

// Database connection
require_once 'db_connection.php';

// Get assessment ID from URL
$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

// Prepare SQL query to fetch assessment mode
$query = "SELECT assessment_mode FROM assessments WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Return JSON response with assessment mode
    header('Content-Type: application/json');
    echo json_encode(['assessment_mode' => strtolower($row['assessment_mode'])]);
} else {
    // Error handling if no assessment found
    http_response_code(404);
    echo json_encode(['error' => 'Assessment not found']);
}

$stmt->close();
$mysqli->close();
?>