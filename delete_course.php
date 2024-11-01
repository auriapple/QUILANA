<?php
include('db_connect.php');
include('auth.php');

if(isset($_POST['course_id'])){
    $course_id = $_POST['course_id'];
    $faculty_id = $_POST['faculty_id'];
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Fetch all the classes under that course
        $class_query = "SELECT class_id FROM class WHERE course_id = ? AND faculty_id = ?";
        $class_stmt = $conn->prepare($class_query);
        $class_stmt->bind_param("ii", $course_id, $faculty_id);
        $class_stmt->execute();
        $class_result = $class_stmt->get_result();

        // Array to hold student IDs
        $class_ids = [];
        while ($row = $class_result->fetch_assoc()) {
            $class_ids[] = $row['class_id'];
        }
        $class_stmt->close();

        // 2. Fetch the assessments administered in that class
        $assessment_query = "SELECT assessment_id FROM assessment WHERE course_id = ? AND faculty_id = ?";
        $assessment_stmt = $conn->prepare($assessment_query);
        $assessment_stmt->bind_param("ii", $course_id, $faculty_id);
        $assessment_stmt->execute();
        $assessment_result = $assessment_stmt->get_result();

        // Array to hold administer IDs
        $assessment_ids = [];
        while ($row = $assessment_result->fetch_assoc()) {
            $assessment_ids[] = $row['assessment_id'];
        }
        $assessment_stmt->close();

        if (!empty($class_ids)) {
            // 3. Delete all students associated with the class
            $delete_enrollment_query = "DELETE FROM student_enrollment WHERE class_id IN (" . implode(',', array_fill(0, count($class_ids), '?')) . ")";
            $delete_enrollment_stmt = $conn->prepare($delete_enrollment_query);
            $types = str_repeat('i', count($class_ids));
            $delete_enrollment_stmt->bind_param($types, ...$class_ids);
            $delete_enrollment_stmt->execute();
            $delete_enrollment_stmt->close();

            // 4. Call delete_assessment.php to delete all associated records with the assessments in a class
            if (!empty($assessment_ids)) {
                foreach ($assessment_ids as $assessment_id) {
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
                }
            }
                
            // 5. Delete all the classes
            $delete_class_query = "DELETE FROM class WHERE class_id IN (" . implode(',', array_fill(0, count($class_ids), '?')) . ")";
            $delete_class_stmt = $conn->prepare($delete_class_query);
            $types = str_repeat('i', count($class_ids));
            $delete_class_stmt->bind_param($types, ...$class_ids);
            $delete_class_stmt->execute();
            $delete_class_stmt->close();
        }

        // 6. Delete the course itself
        $delete_course_query = "DELETE FROM course WHERE course_id = ? AND faculty_id = ?";
        $delete_course_stmt = $conn->prepare($delete_course_query);
        $delete_course_stmt->bind_param('ii', $course_id, $faculty_id);
        $delete_course_stmt->execute();
        $delete_course_stmt->close();

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 1, 'msg' => 'The program has been deleted successfully!']);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 0, 'msg' => 'Failed to delete program: ' . $e->getMessage()]);
    }

    $conn->close();
    }
?>