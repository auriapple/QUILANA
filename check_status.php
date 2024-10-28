<?php
include('db_connect.php');

if (!isset($_GET['assessment_id']) || !isset($_GET['class_id'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$class_id = $conn->real_escape_string($_GET['class_id']);

$status_query = $conn->query("
    SELECT status 
    FROM administer_assessment 
    WHERE assessment_id = '$assessment_id' AND class_id = '$class_id'
");

if ($status_query && $status_query->num_rows > 0) {
    $status_data = $status_query->fetch_assoc();
    echo json_encode(['status' => $status_data['status']]);
} else {
    echo json_encode(['error' => 'No records found']);
}
?>
