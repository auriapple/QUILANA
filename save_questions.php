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

    // Handle time limit
    if ($assessment_mode == 2) { // Quiz Bee Mode
        $time_limit = isset($_POST['time_limit']) ? intval($_POST['time_limit']) : null;
    } else {
        $time_limit = null;
    }

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
    if (empty($question_text) || empty($assessment_id) || empty($ques_type)) {
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
        
                // Get the existing options from the database
                $existing_options_query = "SELECT option_id, option_txt FROM question_options WHERE question_id = ?";
                $existing_options_stmt = $conn->prepare($existing_options_query);
                $existing_options_stmt->bind_param("i", $question_id);
                $existing_options_stmt->execute();
                $existing_options_result = $existing_options_stmt->get_result();
        
                // Track existing options in the database
                $existing_options = [];
                while ($row = $existing_options_result->fetch_assoc()) {
                    $existing_options[$row['option_id']] = $row['option_txt'];
                }
        
                // Array to track submitted options
                $submitted_options = [];
        
                // Loop through the submitted options and update/insert them
                foreach ($options as $index => $option) {
                    $option_text = trim($option);
                    if (!empty($option_text)) {
                        // Add options to submitted list
                        $submitted_options[] = $option_text;
        
                        $is_correct = in_array((string)$index, $is_right) ? 1 : 0;
        
                        // Check if options already exists in the database
                        $option_exists = array_search($option_text, $existing_options);
        
                        if ($option_exists !== false) {
                            // Update existing option
                            $update_option_query = "UPDATE question_options SET is_right = ? WHERE option_id = ?";
                            $update_stmt = $conn->prepare($update_option_query);
                            $update_stmt->bind_param("ii", $is_correct, $option_exists);
                            $update_stmt->execute();
                            // Remove from the existing options array as it has been processed
                            unset($existing_options[$option_exists]);
                        } else {
                            // Insert new option
                            $options_query = "INSERT INTO question_options (option_txt, is_right, question_id) VALUES (?, ?, ?)";
                            $option_stmt = $conn->prepare($options_query);
                            $option_stmt->bind_param("sii", $option_text, $is_correct, $question_id);
                            $option_stmt->execute();
                        }
                    }
                }
        
                // Deletion of options
                foreach ($existing_options as $option_id => $option_text) {
                    $delete_option_query = "DELETE FROM question_options WHERE option_id = ?";
                    $delete_stmt = $conn->prepare($delete_option_query);
                    $delete_stmt->bind_param("i", $option_id);
                    $delete_stmt->execute();
                }
        
                break;

                case 'true_false':
                    $correct_option = $_POST['tf_answer'] ?? '';
                    $options = ['true', 'false'];
                    
                    // Check for existing options
                    $existing_options_query = "SELECT option_txt, option_id FROM question_options WHERE question_id = ?";
                    $existing_options_stmt = $conn->prepare($existing_options_query);
                    $existing_options_stmt->bind_param("i", $question_id);
                    $existing_options_stmt->execute();
                    $existing_options_result = $existing_options_stmt->get_result();
                
                    $existing_options = [];
                    while ($row = $existing_options_result->fetch_assoc()) {
                        $existing_options[$row['option_txt']] = $row['option_id'];
                    }
                
                    foreach ($options as $option) {
                        $is_correct = ($option === $correct_option) ? 1 : 0;
                
                        if (isset($existing_options[$option])) {
                            // Update existing option
                            $option_id = $existing_options[$option];
                            $update_option_query = "UPDATE question_options SET is_right = ? WHERE option_id = ?";
                            $update_stmt = $conn->prepare($update_option_query);
                            $update_stmt->bind_param("ii", $is_correct, $option_id);
                            $update_stmt->execute();
                        } else {
                            // Insert new option
                            $insert_option_query = "INSERT INTO question_options (option_txt, is_right, question_id) VALUES (?, ?, ?)";
                            $insert_stmt = $conn->prepare($insert_option_query);
                            $insert_stmt->bind_param("sii", $option, $is_correct, $question_id);
                            $insert_stmt->execute();
                        }
                    }
                    break;

            case 'identification':
            case 'fill_blank':
                $answer_text = $_POST[$question_type . '_answer'] ?? '';

                if (!empty($answer_text)) {
                    // Check if an answer already exists
                    $check_existing_query = "SELECT identification_id FROM question_identifications WHERE question_id = ?";
                    $check_existing_stmt = $conn->prepare($check_existing_query);
                    $check_existing_stmt->bind_param("i", $question_id);
                    $check_existing_stmt->execute();
                    $check_existing_result = $check_existing_stmt->get_result();

                    if ($check_existing_result->num_rows > 0) {
                        // Update existing answer
                        $existing_answer = $check_existing_result->fetch_assoc();
                        $identification_query = "UPDATE question_identifications SET identification_answer = ? WHERE identification_id = ?";
                        $identification_stmt = $conn->prepare($identification_query);
                        $identification_stmt->bind_param("si", $answer_text, $existing_answer['identification_id']);
                        $identification_stmt->execute();
                    } else {
                        // Insert new answer
                        $identification_query = "INSERT INTO question_identifications (identification_answer, question_id) VALUES (?, ?)";
                        $identification_stmt = $conn->prepare($identification_query);
                        $identification_stmt->bind_param("si", $answer_text, $question_id);
                        $identification_stmt->execute();
                    }
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