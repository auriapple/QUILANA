<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $question_id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $assessment_id = intval($_POST['assessment_id']);
    $question_text = $_POST['question'];
    $question_type = $_POST['question_type'];
    $total_points = intval($_POST['points']);
    
    // Retrieve the assessment mode
    $mode_query = "SELECT assessment_mode FROM assessment WHERE assessment_id = ?";
    $mode_stmt = $conn->prepare($mode_query);
    $mode_stmt->bind_param("i", $assessment_id);
    $mode_stmt->execute();
    $mode_result = $mode_stmt->get_result();
    $assessment_mode = $mode_result->fetch_assoc()['assessment_mode'];
    $mode_stmt->close();

    // Handle time limit based on assessment mode
    $time_limit = ($assessment_mode == 2 || $assessment_mode == 3) ? intval($_POST['time_limit']) : null;

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
    if (empty($question_text) || empty($assessment_id) || empty($ques_type) || empty($total_points)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill out all required fields.']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($question_id) {
            // Update existing question
            $query = "UPDATE questions SET question = ?, ques_type = ?, total_points = ?, time_limit = ? WHERE question_id = ?";
            $time_limit_param = is_null($time_limit) ? null : $time_limit;
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siiii", $question_text, $ques_type, $total_points, $time_limit_param, $question_id);
            $stmt->execute();

            // Delete existing options
            $conn->query("DELETE FROM question_options WHERE question_id = $question_id");
        } else {
            // Insert new question
            $query = "INSERT INTO questions (question, assessment_id, ques_type, total_points, time_limit) VALUES (?, ?, ?, ?, ?)";
            $time_limit_param = is_null($time_limit) ? null : $time_limit;
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siiii", $question_text, $assessment_id, $ques_type, $total_points, $time_limit_param);
            $stmt->execute();
            $question_id = $stmt->insert_id;
        }

        // Handle options based on question type
        switch ($question_type) {
            case 'multiple_choice':
            case 'checkbox':
                $options = $_POST['question_opt'] ?? [];
                $is_right = isset($_POST['is_right']) ? (array)$_POST['is_right'] : [];

                foreach ($options as $index => $option) {
                    $option_text = trim($option);
                    if (!empty($option_text)) { 
                        $is_correct = in_array((string)$index, $is_right) ? 1 : 0;

                        $options_query = "INSERT INTO question_options (option_txt, is_right, question_id) VALUES (?, ?, ?)";
                        $option_stmt = $conn->prepare($options_query);
                        $option_stmt->bind_param("sii", $option_text, $is_correct, $question_id);
                        $option_stmt->execute();
                    }
                }
                break;

            case 'true_false':
                $tf_answer = $_POST['tf_answer'] ?? '';
                $is_correct = ($tf_answer === 'true') ? 1 : 0;

                $options_query = "INSERT INTO question_options (option_txt, is_right, question_id) VALUES (?, ?, ?)";
                $option_stmt = $conn->prepare($options_query);
                $option_stmt->bind_param("sii", $tf_answer, $is_correct, $question_id);
                $option_stmt->execute();
                break;

            case 'identification':
            case 'fill_blank':
                $answer_text = $_POST[$question_type . '_answer'] ?? '';

                if (!empty($answer_text)) {
                    $options_query = "INSERT INTO question_options (option_txt, is_right, question_id) VALUES (?, 1, ?)";
                    $option_stmt = $conn->prepare($options_query);
                    $option_stmt->bind_param("si", $answer_text, $question_id);
                    $option_stmt->execute();
                } else {
                    throw new Exception(ucfirst($question_type) . ' answer is required.');
                }
                break;
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Question saved successfully.']);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
