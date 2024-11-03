<?php
include('db_connect.php');
include('auth.php');

if(isset($_POST['class_id'])){
    $class_id = $_POST['class_id'];
    $faculty_id = $_POST['faculty_id'];
    $course_id = $_POST['course_id'];

    // Start transaction
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

        // 2. Delete all students associated with the class
        $delete_enrollment_query = "DELETE FROM student_enrollment WHERE class_id = ?";
        $delete_enrollment_stmt = $conn->prepare($delete_enrollment_query);
        $delete_enrollment_stmt->bind_param('i', $class_id);
        $delete_enrollment_stmt->execute();
        $delete_enrollment_stmt->close();

        // 3. Fetch the assessments administered in that class
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
            // 4. Delete student join records
            $delete_join_query = "DELETE FROM join_assessment WHERE administer_id IN (" . implode(',', array_fill(0, count($administer_ids), '?')) . ")";
            $delete_join_stmt = $conn->prepare($delete_join_query);
            $types = str_repeat('i', count($administer_ids));
            $delete_join_stmt->bind_param($types, ...$administer_ids);
            $delete_join_stmt->execute();
            $delete_join_stmt->close();
 
            // 5. Delete administer records
            $delete_administer_query = "DELETE FROM administer_assessment WHERE administer_id IN (" . implode(',', array_fill(0, count($administer_ids), '?')) . ")";
            $delete_administer_stmt = $conn->prepare($delete_administer_query);
            $types = str_repeat('i', count($administer_ids));
            $delete_administer_stmt->bind_param($types, ...$administer_ids);
            $delete_administer_stmt->execute();
            $delete_administer_stmt->close();
        }

        // 6. Fetch submission IDs from student_submission
        if (!empty($assessment_ids) && !empty($student_ids)) {
            $submission_query = "SELECT submission_id FROM student_submission WHERE assessment_id IN (" . implode(',', array_fill(0, count($assessment_ids), '?')) . ") AND student_id IN (" . implode(',', array_fill(0, count($student_ids), '?')) . ")";
            $submission_stmt = $conn->prepare($submission_query);
            
            // Create type string for bind_param
            $types = str_repeat('i', count($assessment_ids)) . str_repeat('i', count($student_ids));
            $submission_stmt->bind_param($types, ...array_merge($assessment_ids, $student_ids));
            
            $submission_stmt->execute();
            $submission_result = $submission_stmt->get_result();
            
            // Array to hold submission IDs
            $submission_ids = [];
            while ($row = $submission_result->fetch_assoc()) {
                $submission_ids[] = $row['submission_id'];
            }
            $submission_stmt->close();

            if (!empty($submission_ids)) {
                // 7. Delete student answers from that class
                $delete_answer_query = "DELETE FROM student_answer WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_answer_stmt = $conn->prepare($delete_answer_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_answer_stmt->bind_param($types, ...$submission_ids);
                $delete_answer_stmt->execute();
                $delete_answer_stmt->close();

                // 8. Delete student results
                $delete_result_query = "DELETE FROM student_results WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_result_stmt = $conn->prepare($delete_result_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_result_stmt->bind_param($types, ...$submission_ids);
                $delete_result_stmt->execute();
                $delete_result_stmt->close();

                // 9. Delete student submissions
                $delete_submission_query = "DELETE FROM student_submission WHERE submission_id IN (" . implode(',', array_fill(0, count($submission_ids), '?')) . ")";
                $delete_submission_stmt = $conn->prepare($delete_submission_query);
                $types = str_repeat('i', count($submission_ids));
                $delete_submission_stmt->bind_param($types, ...$submission_ids);
                $delete_submission_stmt->execute();
                $delete_submission_stmt->close();
            }
        }

        // 10. Delete the assessments uploaded in review website
        $delete_uploads_query = "DELETE FROM assessment_uploads WHERE class_id = ?";
        $delete_uploads_stmt = $conn->prepare($delete_uploads_query);
        $delete_uploads_stmt->bind_param("i", $class_id);
        $delete_uploads_stmt->execute();
        $delete_uploads_stmt->close();

        // 11. Delete the class itself
        $delete_class_query = "DELETE FROM class WHERE class_id = ?";
        $delete_class_stmt = $conn->prepare($delete_class_query);
        $delete_class_stmt->bind_param("i", $class_id);
        $delete_class_stmt->execute();
        $delete_class_stmt->close();

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 1, 'msg' => 'The class has been deleted successfully!']);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 0, 'msg' => 'Failed to delete class: ' . $e->getMessage()]);
    }

    $conn->close();
}

?>
