<?php
session_start(); // Start the session to access session variables
include('db_connect.php');
header('Content-Type: application/json');

// Get JSON data from the request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate if necessary data is present
if (isset($data['administer_id']) && isset($data['suspicious_act'])) {
    $administer_id = $conn->real_escape_string($data['administer_id']);
    $suspicious_act = (int)$data['suspicious_act'];
    
    // Validate that 'student_id' exists in the session
    if (isset($_SESSION['login_id'])) {
        $student_id = $conn->real_escape_string($_SESSION['login_id']);

        // Update status in join_assessment table using a prepared statement
        $stmt = $conn->prepare("
            UPDATE join_assessment
            SET suspicious_act = ?, if_display = true
            WHERE administer_id = ? AND student_id = ?
        ");
        
        if ($stmt) {
            $stmt->bind_param("iii", $suspicious_act, $administer_id, $student_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error executing the update: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error preparing the query: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Student ID is missing from the session.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'administer_id or tab_switches is missing.']);
}

$conn->close();
?>