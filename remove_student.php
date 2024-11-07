<?php
include('db_connect.php');

// Check if the necessary data is provided
if (isset($_POST['student_id']) && isset($_POST['class_id'])) {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];

    try {
        $conn->begin_transaction();

        // 1. Fetch the assessments administered in that class
        $administer_query = "SELECT assessment_id, administer_id FROM administer_assessment WHERE class_id = ?";
        $administer_stmt = $conn->prepare($administer_query);
        $administer_stmt->bind_param("i", $class_id);
        $administer_stmt->execute();
        $administer_result = $administer_stmt->get_result();

        // Array to hold administer IDs
        $administer_data = [];
        while ($row = $administer_result->fetch_assoc()) {
            $administer_data[] = [
                'assessment_id' => $row['assessment_id'],
                'administer_id' => $row['administer_id']
            ];
        }
        $administer_stmt->close();

        // Extract assessment_ids for later use
        $assessment_ids = array_column($administer_data, 'assessment_id');
        $administer_ids = array_column($administer_data, 'administer_id');

        if (!empty($administer_ids)) {
            // 2. Delete student join records
            $delete_join_query = "DELETE FROM join_assessment WHERE administer_id IN (" . implode(',', array_fill(0, count($administer_ids), '?')) . ")";
            $delete_join_stmt = $conn->prepare($delete_join_query);
            $types = str_repeat('i', count($administer_ids));
            $delete_join_stmt->bind_param($types, ...$administer_ids);
            $delete_join_stmt->execute();
            $delete_join_stmt->close();
        }

        // 3. Fetch submission IDs from student_submission
        if (!empty($assessment_ids)) {
            $submission_query = "SELECT submission_id FROM student_submission WHERE assessment_id IN (" . implode(',', array_fill(0, count($assessment_ids), '?')) . ") AND student_id = ?";
            $submission_stmt = $conn->prepare($submission_query);
            
            // Create type string for bind_param
            $types = str_repeat('i', count($assessment_ids)) . 'i';
            $submission_stmt->bind_param($types, ...array_merge($assessment_ids, [$student_id]));
            
            $submission_stmt->execute();
            $submission_result = $submission_stmt->get_result();
            
            // Array to hold submission IDs
            $submission_ids = [];
            while ($row = $submission_result->fetch_assoc()) {
                $submission_ids[] = $row['submission_id'];
            }
            $submission_stmt->close();

            if (!empty($submission_ids)) {
                // 4. Delete student answers from that class
                $delete_answer_query = "DELETE FROM student_answer WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_answer_stmt = $conn->prepare($delete_answer_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_answer_stmt->bind_param($types, ...$submission_ids);
                $delete_answer_stmt->execute();
                $delete_answer_stmt->close();

                // 5. Delete student results
                $delete_result_query = "DELETE FROM student_results WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_result_stmt = $conn->prepare($delete_result_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_result_stmt->bind_param($types, ...$submission_ids);
                $delete_result_stmt->execute();
                $delete_result_stmt->close();

                // 6. Delete student submissions
                $delete_submission_query = "DELETE FROM student_submission WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_submission_stmt = $conn->prepare($delete_submission_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_submission_stmt->bind_param($types, ...$submission_ids);
                $delete_submission_stmt->execute();
                $delete_submission_stmt->close();
            }
        }

        //7. Update the student's enrollment itself ( 2 = unenrolled/removed)
        $update_enrollment_query = "UPDATE student_enrollment SET status = 2 WHERE class_id = ? AND student_id = ?";
        $update_enrollment_stmt = $conn->prepare($update_enrollment_query);
        $update_enrollment_stmt->bind_param('ii', $class_id, $student_id);
        $update_enrollment_stmt->execute();
        $update_enrollment_stmt->close();


        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => true]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
