<?php
include('db_connect.php');

if (isset($_POST['assessment_id'])) {
    $assessment_id = $_POST['assessment_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Fetch associated question IDs
        $questions_query = "SELECT question_id FROM questions WHERE assessment_id = ?";
        $questions_stmt = $conn->prepare($questions_query);
        $questions_stmt->bind_param("i", $assessment_id);
        $questions_stmt->execute();
        $questions_result = $questions_stmt->get_result();

        // Array to hold question IDs
        $question_ids = [];
        while ($row = $questions_result->fetch_assoc()) {
            $question_ids[] = $row['question_id'];
        }
        $questions_stmt->close();

        if (!empty($question_ids)) {
            // 2. Delete associated question options
            $delete_options_query = "DELETE FROM question_options WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")";
            $delete_options_stmt = $conn->prepare($delete_options_query);
            $types = str_repeat('i', count($question_ids));
            $delete_options_stmt->bind_param($types, ...$question_ids);
            $delete_options_stmt->execute();
            $delete_options_stmt->close();

            // 3. Delete associated question identifications
            $delete_identifications_query = "DELETE FROM question_identifications WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")";
            $delete_identifications_stmt = $conn->prepare($delete_identifications_query);
            $delete_identifications_stmt->bind_param($types, ...$question_ids);
            $delete_identifications_stmt->execute();
            $delete_identifications_stmt->close();

            // 4. Delete the questions themselves
            $delete_questions_query = "DELETE FROM questions WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")";
            $delete_questions_stmt = $conn->prepare($delete_questions_query);
            $delete_questions_stmt->bind_param($types, ...$question_ids);
            $delete_questions_stmt->execute();
            $delete_questions_stmt->close();
        }

        // 5. Delete the assessment itself
        $delete_assessment_query = "DELETE FROM assessment WHERE assessment_id = ?";
        $delete_assessment_stmt = $conn->prepare($delete_assessment_query);
        $delete_assessment_stmt->bind_param("i", $assessment_id);
        $delete_assessment_stmt->execute();
        $delete_assessment_stmt->close();

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Assessment deleted successfully, student answers retained.']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }

    $conn->close();
}
?>
