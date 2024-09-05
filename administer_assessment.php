<?php
header('Content-Type: application/json');
include('db_connect.php');

// Get form data
$assessment_id = $_POST['assessment_id'];
$class_id = $_POST['class_id'];
$course_id = $_POST['course_id'];

// Validate inputs
if (empty($assessment_id) || empty($class_id) || empty($course_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill out all required fields.']);
    exit;
}

// Check if the assessment has already been administered to this class
$check_sql = "
    SELECT * FROM administer_assessment 
    WHERE assessment_id = ? AND class_id = ? AND course_id = ?
";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('iii', $assessment_id, $class_id, $course_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Assessment already administered
    echo json_encode(['status' => 'info', 'message' => 'Assessment already administered to this class.']);
} else {
    // Insert new administration record without timelimit
    $insert_sql = "
        INSERT INTO administer_assessment (assessment_id, class_id, course_id, date_administered)
        VALUES (?, ?, ?, CURRENT_DATE())
    ";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param('iii', $assessment_id, $class_id, $course_id);

    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Assessment successfully administered!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to administer assessment.']);
    }

    $insert_stmt->close();
}

// Close connections
$check_stmt->close();
$conn->close();
?>
