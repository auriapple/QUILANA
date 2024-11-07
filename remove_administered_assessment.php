<?php
include('db_connect.php');

//header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id']) && isset($_POST['class_id']) && isset($_POST['administer_id'])) {
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $administer_id = $conn->real_escape_string($_POST['administer_id']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        $status = 1; 
        // 1. Fetch students enrolled in that class
        $enrolled_query = "SELECT student_id FROM student_enrollment WHERE class_id = ? AND status = ?";
        $enrolled_stmt = $conn->prepare($enrolled_query);
        $enrolled_stmt->bind_param("ii", $class_id, $status);
        $enrolled_stmt->execute();
        $enrolled_result = $enrolled_stmt->get_result();

        // Array to hold student IDs
        $student_ids = [];
        while ($row = $enrolled_result->fetch_assoc()) {
            $student_ids[] = $row['student_id'];
        }
        $enrolled_stmt->close();

        // 2. Fetch submission IDs from student_submission
        if (!empty($student_ids)) {
            $submission_query = "SELECT submission_id FROM student_submission WHERE student_id IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ") AND assessment_id = ?";
            $submission_stmt = $conn->prepare($submission_query);
            
            // Create type string for bind_param
            $types = str_repeat('i', count($student_ids)) . 'i';
            $submission_stmt->bind_param($types, ...array_merge($student_ids, [$assessment_id]));
            
            $submission_stmt->execute();
            $submission_result = $submission_stmt->get_result();
            
            // Array to hold submission IDs
            $submission_ids = [];
            while ($row = $submission_result->fetch_assoc()) {
                $submission_ids[] = $row['submission_id'];
            }
            $submission_stmt->close();

            if (!empty($submission_ids)) {
                // 3. Delete student answers from that class
                $delete_answer_query = "DELETE FROM student_answer WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_answer_stmt = $conn->prepare($delete_answer_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_answer_stmt->bind_param($types, ...$submission_ids);
                $delete_answer_stmt->execute();
                $delete_answer_stmt->close();

                // 4. Delete student results
                $delete_result_query = "DELETE FROM student_results WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_result_stmt = $conn->prepare($delete_result_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_result_stmt->bind_param($types, ...$submission_ids);
                $delete_result_stmt->execute();
                $delete_result_stmt->close();

                // 5. Delete student submissions
                $delete_submission_query = "DELETE FROM student_submission WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_submission_stmt = $conn->prepare($delete_submission_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_submission_stmt->bind_param($types, ...$submission_ids);
                $delete_submission_stmt->execute();
                $delete_submission_stmt->close();
            }
        }

        // 6. Delete student join records
        $delete_join_query = "DELETE FROM join_assessment WHERE administer_id = ?";
        $delete_join_stmt = $conn->prepare($delete_join_query);
        $delete_join_stmt->bind_param('i', $administer_id);
        $delete_join_stmt->execute();
        $delete_join_stmt->close();

        // 7. Delete the assessment uploaded in review website
        $delete_uploads_query = "DELETE FROM assessment_uploads WHERE assessment_id = ? AND class_id = ?";
        $delete_uploads_stmt = $conn->prepare($delete_uploads_query);
        $delete_uploads_stmt->bind_param("ii", $assessment_id, $class_id);
        $delete_uploads_stmt->execute();
        $delete_uploads_stmt->close();

        // 8. Delete administer record
        $delete_administer_query = "DELETE FROM administer_assessment WHERE administer_id = ?";
        $delete_administer_stmt = $conn->prepare($delete_administer_query);
        $delete_administer_stmt->bind_param('i', $administer_id);
        $delete_administer_stmt->execute();
        $delete_administer_stmt->close();

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => true]);
    
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid request.']);
}
?>