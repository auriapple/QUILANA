<?php
include('db_connect.php');

header('Content-Type: application/json'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Retrieve form data
        $question_id = isset($_POST['id']) ? intval($_POST['id']) : null;
        $reviewer_id = intval($_POST['reviewer_id']);
        $question_text = $_POST['question'];
        $question_type = $_POST['question_type'];
        $order_by = isset($_POST['order_by']) ? intval($_POST['order_by']) : 1;
        $total_points = isset($_POST['total_points']) ? intval($_POST['total_points']) : 1;

        // Map question type to numeric value
        $ques_type_map = [
            'multiple_choice' => 1,
            'checkbox' => 2,
            'true_false' => 3,
            'identification' => 4,
            'fill_blank' => 5
        ];
        $ques_type = $ques_type_map[$question_type] ?? 0;

        // Validate inputs
        if (empty($question_text) || empty($reviewer_id) || empty($ques_type)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill out all required fields.']);
            exit;
        }

        // Start database transaction
        $conn->begin_transaction();

        // If question ID exists, update the existing question
        if ($question_id) {
            $query = "UPDATE rw_questions SET question = ?, question_type = ?, total_points = ?, order_by = ? WHERE rw_question_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siiii", $question_text, $ques_type, $total_points, $order_by, $question_id);
            $stmt->execute();

            // Delete existing options or answers for this question (so we can re-insert them)
            $conn->query("DELETE FROM rw_question_opt WHERE rw_question_id = $question_id");
            $conn->query("DELETE FROM rw_question_identifications WHERE rw_question_id = $question_id");
        } else {
            // Insert new question
            $query = "INSERT INTO rw_questions (question, reviewer_id, question_type, total_points, order_by) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siiii", $question_text, $reviewer_id, $ques_type, $total_points, $order_by);
            $stmt->execute();
            $question_id = $stmt->insert_id; // Get the ID of the newly inserted question
        }

        // Handle options or answers based on question type
        switch ($question_type) {
            case 'multiple_choice':
            case 'checkbox':
                $options = $_POST['question_opt'] ?? [];
                $is_right = isset($_POST['is_right']) ? (array)$_POST['is_right'] : [];

                foreach ($options as $index => $option) {
                    $option_text = trim($option);
                    if (!empty($option_text)) {
                        // Determine if the option is correct
                        $is_correct = in_array((string)$index, $is_right) ? 1 : 0;
                        $options_query = "INSERT INTO rw_question_opt (option_text, is_right, rw_question_id) VALUES (?, ?, ?)";
                        $option_stmt = $conn->prepare($options_query);
                        $option_stmt->bind_param("sii", $option_text, $is_correct, $question_id);
                        $option_stmt->execute();
                    }
                }
                break;

            case 'true_false':
                // For true/false questions, insert the correct answer
                $tf_answer = $_POST['tf_answer'] ?? '';
                $is_correct = ($tf_answer === 'true') ? 1 : 0;

                $options_query = "INSERT INTO rw_question_opt (option_text, is_right, rw_question_id) VALUES (?, ?, ?)";
                $option_stmt = $conn->prepare($options_query);
                $option_stmt->bind_param("sii", $tf_answer, $is_correct, $question_id);
                $option_stmt->execute();
                break;

            case 'identification':
            case 'fill_blank':
                // Handle identification or fill-in-the-blank answers
                $answer = $_POST[$question_type . '_answer'] ?? ''; 
                $answer_text = trim($answer);
                if (!empty($answer_text)) {
                    $identification_query = "INSERT INTO rw_question_identifications (identification_answer, rw_question_id) VALUES (?, ?)";
                    $identification_stmt = $conn->prepare($identification_query);
                    $identification_stmt->bind_param("si", $answer_text, $question_id);
                    $identification_stmt->execute();
                } else {
                    throw new Exception('Answer is required for identification or fill-in-the-blank questions.');
                }
                break;
        }

        // Commit the transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Question saved successfully.']);
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log($e->getMessage()); // Log the error
        echo json_encode(['status' => 'error', 'message' => 'Error occurred: ' . $e->getMessage()]);
        exit;
    }
}

$conn->close();
?>
