<?php
header('Content-Type: application/json');
$response = array('status' => 'success', 'message' => 'Assessment successfully administered!');
echo json_encode($response);


include('db_connect.php');

// Get form data
$assessment_id = $_POST['assessment_id'];
$class_id = $_POST['class_id'];
$timelimit = $_POST['time_limit'];
$course_id = $_POST['course_id'];

// Validate inputs
if (empty($assessment_id) || empty($class_id) || empty($timelimit) || empty($course_id)) {
    echo 'Please fill out all required fields.';
    exit;
}

// Prepare SQL statement with ON DUPLICATE KEY UPDATE
$sql = "
    INSERT INTO administer_assessment (assessment_id, class_id, timelimit, course_id, date_administered)
    VALUES (?, ?, ?, ?, CURRENT_DATE())
    ON DUPLICATE KEY UPDATE 
        timelimit = VALUES(timelimit),
        date_administered = VALUES(date_administered)
";

$stmt = $conn->prepare($sql);

// Bind parameters and execute statement
$stmt->bind_param('iiii', $assessment_id, $class_id, $timelimit, $course_id);
if ($stmt->execute()) {
} else {
    echo 'Error: ' . $conn->error;
}

// Close statement and connection
$stmt->close();
$conn->close();

?>
