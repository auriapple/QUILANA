<?php
include('db_connect.php');
session_start();

header('Content-Type: application/json'); 

function sendResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit; 
}

if (!isset($_POST['get_code'])) {
    sendResponse('error', 'No class code provided.');
}

$code = $conn->real_escape_string($_POST['get_code']);
$student_id = $_SESSION['login_id'];

// Query to find the class based on the provided code
$class_query = $conn->query("SELECT c.class_id, c.subject, f.firstname, f.lastname 
                             FROM class c 
                             JOIN faculty f ON c.faculty_id = f.faculty_id 
                             WHERE c.code = '$code'");

if (!$class_query) {
    sendResponse('error', 'Database error: ' . $conn->error);
}

if ($class_query->num_rows === 0) {
    sendResponse('error', 'Class not found. Please check the class code and try again.');
}

$class = $class_query->fetch_assoc();
$class_id = $class['class_id'];

// Check if the student is already enrolled in this class
$check_student = $conn->query("SELECT status FROM student_enrollment WHERE class_id = '$class_id' AND student_id = '$student_id'");

if (!$check_student) {
    sendResponse('error', 'Database error: ' . $conn->error);
}

if ($check_student->num_rows > 0) {
    $row = $check_student->fetch_assoc();
    switch ($row['status']) {
        case 0:
            sendResponse('error', 'Your enrollment is still pending approval.');
        case 1:
            sendResponse('error', 'You are already enrolled in this class.');
        case 2:
            // Update the status from "unenrolled" (2) to "pending" (0)
            $update_query = "UPDATE student_enrollment SET status = 0 WHERE class_id = '$class_id' AND student_id = '$student_id'";
            if ($conn->query($update_query)) {
                sendResponse('success', 'Enrollment request sent! Please wait for approval.');
            } else {
                sendResponse('error', 'Failed to update enrollment request: ' . $conn->error);
            }
            break; 
        default:
            sendResponse('error', 'Invalid enrollment status. Please contact support.');
    }
} else {
    // New enrollment case
    $insert_query = "INSERT INTO student_enrollment (class_id, student_id, status) VALUES ('$class_id', '$student_id', '0')";
    if ($conn->query($insert_query)) {
        sendResponse('success', 'Enrollment request sent! Please wait for approval.');
    } else {
        sendResponse('error', 'Failed to send enrollment request: ' . $conn->error);
    }
}
?>
