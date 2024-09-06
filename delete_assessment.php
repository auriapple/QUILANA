<?php
include('db_connect.php');

if (isset($_POST['assessment_id'])) {
    $assessment_id = $_POST['assessment_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Delete associated questions
        $questions_query = "SELECT question_id FROM questions WHERE assessment_id = ?";
        $questions_stmt = $conn->prepare($questions_query);
        $questions_stmt->bind_param("i", $assessment_id);
        $questions_stmt->execute();
        $questions_result = $questions_stmt->get_result();

        // Array to hold question IDs for further deletion
        $question_ids = [];
        while ($row = $questions_result->fetch_assoc()) {
            $question_ids[] = $row['question_id'];
        }
        $questions_stmt->close();

        // Delete questions
        $delete_questions_query = "DELETE FROM questions WHERE assessment_id = ?";
        $delete_questions_stmt = $conn->prepare($delete_questions_query);
        $delete_questions_stmt->bind_param("i", $assessment_id);
        $delete_questions_stmt->execute();
        $delete_questions_stmt->close();

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

        // 4. Delete the assessment itself
        $delete_assessment_query = "DELETE FROM assessment WHERE assessment_id = ?";
        $delete_assessment_stmt = $conn->prepare($delete_assessment_query);
        $delete_assessment_stmt->bind_param("i", $assessment_id);
        $delete_assessment_stmt->execute();
        $delete_assessment_stmt->close();

        // Commit transaction
        $conn->commit();
        echo 1;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo 0;
    }

    $conn->close();
}
?>