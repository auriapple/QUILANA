<?php
include('db_connect.php'); // Include your database connection script

session_start(); // Start the session to get the student ID

if (isset($_POST['get_code'])) {
    $code = $conn->real_escape_string($_POST['get_code']);
    $student_id = $_SESSION['login_id']; // Ensure the student ID is set in the session

    // Fetch the class and subject based on the code
    $class_query = $conn->query("SELECT c.class_id, c.section, c.subject, f.firstname, f.lastname 
                                 FROM class c 
                                 JOIN faculty f ON c.faculty_id = f.faculty_id 
                                 WHERE c.code = '$code'");

    if ($class_query && $class_query->num_rows > 0) {
        $class = $class_query->fetch_assoc();
        $class_id = $class['class_id'];

        // Check if the student is already enrolled in the class
        $check_student = $conn->query("SELECT * FROM student_enrollment WHERE class_id = '$class_id' AND student_id = '$student_id'");
        
        if ($check_student && $check_student->num_rows == 0) {
            // Add student to the class with pending status
            $conn->query("INSERT INTO student_enrollment (class_id, student_id, status) VALUES ('$class_id', '$student_id', '0')");

            if ($conn->affected_rows > 0) {
                // Return a success message to be displayed
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Enrollment request sent! Please wait for approval.'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send enrollment request']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'You are already enrolled in this class or pending approval']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Class not found']);
    }
    exit;
}
