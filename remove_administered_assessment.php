<?php
include('db_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id']) && isset($_POST['class_id'])) {
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $class_id = $conn->real_escape_string($_POST['class_id']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Remove from administer_assessment table
        $stmt = $conn->prepare("DELETE FROM administer_assessment WHERE assessment_id = ? AND class_id = ?");
        $stmt->bind_param("ii", $assessment_id, $class_id);
        $stmt->execute();

        // Remove from student_results table
        $stmt = $conn->prepare("DELETE FROM student_results WHERE assessment_id = ? AND class_id = ?");
        $stmt->bind_param("ii", $assessment_id, $class_id);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>