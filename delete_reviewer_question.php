<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $question_id = intval($_POST['question_id']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete associated options
        $conn->query("DELETE FROM rw_question_opt WHERE rw_question_id = '$question_id'");

        // Delete associated identification answers
        $conn->query("DELETE FROM rw_question_identifications WHERE rw_question_id = '$question_id'");

        // Delete the question itself
        $conn->query("DELETE FROM rw_questions WHERE rw_question_id = '$question_id'");

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Question deleted successfully']);
    } catch (Exception $e) {
        // Rollback transaction if any query fails
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete the question']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
