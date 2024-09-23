<?php
include('db_connect.php');
session_start();

if (isset($_POST['get_code'])) {
    $code = $conn->real_escape_string($_POST['get_code']);
    $student_id = $_SESSION['login_id'];

    $class_query = $conn->query("SELECT c.class_id, c.subject, f.firstname, f.lastname 
                                 FROM class c 
                                 JOIN faculty f ON c.faculty_id = f.faculty_id 
                                 WHERE c.code = '$code'");

    if ($class_query && $class_query->num_rows > 0) {
        $class = $class_query->fetch_assoc();
        $class_id = $class['class_id'];

        $check_student = $conn->query("SELECT status FROM student_enrollment WHERE class_id = '$class_id' AND student_id = '$student_id'");
        
        if ($check_student && $check_student->num_rows > 0) {
            $row = $check_student->fetch_assoc();
            if ($row['status'] == 0) {
                echo json_encode(['status' => 'error', 'message' => 'Your enrollment is still pending approval.']);
            } elseif ($row['status'] == 1) {
                echo json_encode(['status' => 'error', 'message' => 'You are already enrolled in this class.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Your enrollment request has been rejected.']);
            }
        } else {
            $conn->query("INSERT INTO student_enrollment (class_id, student_id, status) VALUES ('$class_id', '$student_id', '0')");

            if ($conn->affected_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Enrollment request sent! Please wait for approval.'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send enrollment request']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Class not found']);
    }
    exit;
}