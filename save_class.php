<?php
// Include database connection
include('db_connect.php');

// Check if the required fields are set
if (isset($_POST['class_name']) && isset($_POST['year']) && isset($_POST['section']) && isset($_POST['subject']) && isset($_POST['course_id'])) {
    // Sanitize input data
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $year = $conn->real_escape_string($_POST['year']);
    $section = $conn->real_escape_string($_POST['section']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $course_id = $conn->real_escape_string($_POST['course_id']);
    $faculty_id = $conn->real_escape_string($_POST['faculty_id']);

    // Insert new class into the database
    $sql = "INSERT INTO class (course_id, class_name, year, section, subject, faculty_id) VALUES ('$course_id', '$class_name', '$year', '$section', '$subject', '$faculty_id')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 1, 'msg' => 'Class added successfully.']);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Failed to add class: ' . $conn->error]);
    }

    // Close the connection
    $conn->close();
} else {
    echo json_encode(['status' => 0, 'msg' => 'Required fields are missing.']);
}
?>
