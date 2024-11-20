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
            // 2. Delete student answers associated with the question ids
            $delete_answer_query = "DELETE FROM student_answer WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")";
            $delete_answer_stmt = $conn->prepare($delete_answer_query);
            $types = str_repeat('i', count($question_ids));
            $delete_answer_stmt->bind_param($types, ...$question_ids);
            $delete_answer_stmt->execute();
            $delete_answer_stmt->close();

            // 3. Delete associated question options
            $delete_options_query = "DELETE FROM question_options WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")";
            $delete_options_stmt = $conn->prepare($delete_options_query);
            $types = str_repeat('i', count($question_ids));
            $delete_options_stmt->bind_param($types, ...$question_ids);
            $delete_options_stmt->execute();
            $delete_options_stmt->close();

            // 4. Delete associated question identifications
            $delete_identifications_query = "DELETE FROM question_identifications WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")";
            $delete_identifications_stmt = $conn->prepare($delete_identifications_query);
            $delete_identifications_stmt->bind_param($types, ...$question_ids);
            $delete_identifications_stmt->execute();
            $delete_identifications_stmt->close();

            // 5. Delete the questions themselves
            $delete_questions_query = "DELETE FROM questions WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")";
            $delete_questions_stmt = $conn->prepare($delete_questions_query);
            $delete_questions_stmt->bind_param($types, ...$question_ids);
            $delete_questions_stmt->execute();
            $delete_questions_stmt->close();
        }

        // 6. Fetch associated administer IDs
        $administer_query = "SELECT administer_id FROM administer_assessment WHERE assessment_id = ?";
        $administer_stmt = $conn->prepare($administer_query);
        $administer_stmt->bind_param("i", $assessment_id);
        $administer_stmt->execute();
        $administer_result = $administer_stmt->get_result();

        // Array to hold administer IDs
        $administer_ids = [];
        while ($row = $administer_result->fetch_assoc()) {
            $administer_ids[] = $row['administer_id'];
        }
        $administer_stmt->close();

        if (!empty($administer_ids)) {
           // 7. Delete student join records
           $delete_join_query = "DELETE FROM join_assessment WHERE administer_id IN (" . implode(',', array_fill(0, count($administer_ids), '?')) . ")";
           $delete_join_stmt = $conn->prepare($delete_join_query);
           $types = str_repeat('i', count($administer_ids));
           $delete_join_stmt->bind_param($types, ...$administer_ids);
           $delete_join_stmt->execute();
           $delete_join_stmt->close();

            // 8. Delete administer records
            $delete_administer_query = "DELETE FROM administer_assessment WHERE administer_id IN (" . implode(',', array_fill(0, count($administer_ids), '?')) . ")";
            $delete_administer_stmt = $conn->prepare($delete_administer_query);
            $types = str_repeat('i', count($administer_ids));
            $delete_administer_stmt->bind_param($types, ...$administer_ids);
            $delete_administer_stmt->execute();
            $delete_administer_stmt->close();
        }

        // 9. Delete student results associated with the assessment
        $delete_result_query = "DELETE FROM student_results WHERE assessment_id = ?";
        $delete_result_stmt = $conn->prepare($delete_result_query);
        $delete_result_stmt->bind_param("i", $assessment_id);
        $delete_result_stmt->execute();
        $delete_result_stmt->close();
        
        // 10. Delete student submissions
        $delete_submission_query = "DELETE FROM student_submission WHERE assessment_id = ?";
        $delete_submission_stmt = $conn->prepare($delete_submission_query);
        $delete_submission_stmt->bind_param("i", $assessment_id);
        $delete_submission_stmt->execute();
        $delete_submission_stmt->close();

        // 11. Delete the assessments uploaded in review website
        $delete_uploads_query = "DELETE FROM assessment_uploads WHERE assessment_id = ?";
        $delete_uploads_stmt = $conn->prepare($delete_uploads_query);
        $delete_uploads_stmt->bind_param("i", $assessment_id);
        $delete_uploads_stmt->execute();
        $delete_uploads_stmt->close();

        // 12. Delete the assessment itself
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