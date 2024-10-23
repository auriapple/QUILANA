<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

include('db_connect.php');

// Retrieve and sanitize the input parameters
$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

// Check if assessment_id and class_id are valid
if ($assessment_id <= 0 || $class_id <= 0) {
    die(json_encode(['error' => 'Invalid or missing assessment_id or class_id']));
}

// Prepare the SQL query
$scores_query = "
    SELECT s.student_id, s.firstname, s.lastname, 
           sr.score, sr.total_score, sr.remarks, sr.rank, se.status
    FROM student_enrollment se
    JOIN student s ON se.student_id = s.student_id
    LEFT JOIN student_results sr ON s.student_id = sr.student_id 
        AND sr.assessment_id = ?
    WHERE se.class_id = ? AND se.status = 1
    ORDER BY s.lastname ASC, s.firstname ASC";

$stmt = $conn->prepare($scores_query);
if ($stmt === false) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}

// Bind parameters and execute
$stmt->bind_param('ii', $assessment_id, $class_id);
if (!$stmt->execute()) {
    die(json_encode(['error' => 'Execute failed: ' . $stmt->error]));
}

// Fetch results
$scores_result = $stmt->get_result();
$scores = [];

while ($row = $scores_result->fetch_assoc()) {
    $scores[] = [
        'lastname' => $row['lastname'],
        'firstname' => $row['firstname'],
        'score' => isset($row['score']) ? $row['score'] : null,
        'total_score' => isset($row['total_score']) ? $row['total_score'] : null,
        'remarks' => isset($row['remarks']) ? $row['remarks'] : 'Not Taken',
        'rank' => isset($row['rank']) ? $row['rank'] : 'Not Taken'
    ];
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return scores as JSON
echo json_encode(['scores' => $scores]);
?>
