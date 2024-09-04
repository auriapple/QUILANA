<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $question_id = intval($_POST['question_id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete associated options
        $delete_options = "DELETE FROM question_options WHERE question_id = ?";
        $stmt = $conn->prepare($delete_options);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();

        // Delete associated identifications (for identification and fill-in-the-blank questions)
        $delete_identifications = "DELETE FROM question_identifications WHERE question_id = ?";
        $stmt = $conn->prepare($delete_identifications);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();

        // Delete the question
        $delete_question = "DELETE FROM questions WHERE question_id = ?";
        $stmt = $conn->prepare($delete_question);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Question deleted successfully.']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while deleting the question: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>