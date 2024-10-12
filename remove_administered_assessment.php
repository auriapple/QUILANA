<?php
include('db_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id']) && isset($_POST['class_id']) && isset($_POST['student_id']) && isset($_POST['administer_id'])) {
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $administer_id = $conn->real_escape_string($_POST['administer_id']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // First, delete from join_assessment table
        $stmt = $conn->prepare("DELETE FROM join_assessment WHERE administer_id = ?");
        $stmt->bind_param("i", $administer_id);
        if (!$stmt->execute()) {
            throw new Exception("Error in join_assessment deletion: " . $stmt->error);
        }

        // Remove from administer_assessment table
        $stmt = $conn->prepare("DELETE FROM administer_assessment WHERE assessment_id = ? AND class_id = ?");
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




