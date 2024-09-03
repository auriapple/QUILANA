<?php
// Include database connection
include('db_connect.php');

// Check if the required fields are set
if (isset($_POST['class_name']) && isset($_POST['subject']) && isset($_POST['course_id'])) {
    // Sanitize input data
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $course_id = $conn->real_escape_string($_POST['course_id']);
    $faculty_id = $conn->real_escape_string($_POST['faculty_id']);

    // Initialize variables for unique code generation
    $unique_code = '';
    $code_exists = true;

    // Loop to generate a unique code
    while ($code_exists) {
        // Generate a random 8-character code
        $unique_code = substr(md5(uniqid(rand(), true)), 0, 8);

        // Check if the code already exists in the class table
        $check_code_query = "SELECT COUNT(*) as count FROM class WHERE code = '$unique_code'";
        $result = $conn->query($check_code_query);
        $row = $result->fetch_assoc();

        // If the count is 0, the code is unique
        if ($row['count'] == 0) {
            $code_exists = false;
        }
    }

    // Insert new class into the database
    $sql = "INSERT INTO class (course_id, code, class_name, subject, faculty_id) VALUES ('$course_id', '$unique_code', '$class_name', '$subject', '$faculty_id')";
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
