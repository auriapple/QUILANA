<?php
include('db_connect.php');
if (isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $qry_studentNumPending = $conn->query("
        SELECT COUNT(*) AS numPending
        FROM student_enrollment se
        JOIN student s ON se.student_id = s.student_id
        WHERE se.class_id = '$class_id' AND se.status = 0
    ");
    $row = $qry_studentNumPending->fetch_assoc();
    echo json_encode(['numPending' => $row['numPending']]);
} else {
    echo json_encode(['error' => 'Missing class_id']);
}
?>
