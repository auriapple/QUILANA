<?php
// Database connection
require 'db_connect.php';
session_start();

function check_correctness($question_id, $answer_value, $question_type, $conn) {
    $points_query = $conn->query("SELECT total_points FROM questions WHERE question_id = '$question_id'");
    if ($points_query && $points_query->num_rows > 0) {
        $points_data = $points_query->fetch_assoc();
        $total_points = $points_data['total_points'];
    } else {
        return 0; // No points if question is not found
    }

    $answer_value = strtolower(trim($answer_value)); // Normalize input for comparison

    if ($question_type == 1 || $question_type == 3) {
        // Multiple Choice or True/False
        $correct_answer_query = $conn->query("SELECT option_txt FROM question_options WHERE question_id = '$question_id' AND is_right = 1");
        if ($correct_answer_query && $correct_answer_query->num_rows > 0) {
            $correct_answer_data = $correct_answer_query->fetch_assoc();
            $correct_option_txt = strtolower(trim($correct_answer_data['option_txt']));
            return ($answer_value == $correct_option_txt) ? 1 : 0;
        }
        return 0;

    } elseif ($question_type == 2) {
        // Multiple Selection
        $correct_answers_query = $conn->query("SELECT option_txt FROM question_options WHERE question_id = '$question_id' AND is_right = 1");
        $correct_answers = [];
        while ($row = $correct_answers_query->fetch_assoc()) {
            $correct_answers[] = strtolower(trim($row['option_txt']));
        }

        $selected_answers = is_array($answer_value) ? array_map('strtolower', array_map('trim', $answer_value)) : [strtolower(trim($answer_value))];
        return (in_array($answer_value, $correct_answers)) ? 1 : 0;

    } elseif ($question_type == 4 || $question_type == 5) {
        // Fill-in-the-blank or Identification (text input)
        $correct_text_query = $conn->query("SELECT identification_answer FROM question_identifications WHERE question_id = '$question_id'");
        if ($correct_text_query && $correct_text_query->num_rows > 0) {
            $correct_text_data = $correct_text_query->fetch_assoc();
            $correct_text = strtolower(trim($correct_text_data['identification_answer']));
            return ($answer_value == $correct_text) ? 1 : 0;
        }
        return 0;
    }

    return 0; // Default to incorrect if no condition matches
}


// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $student_id = $_SESSION['login_id'];
    $answers = $_POST['answers'];
    $date_taken = date('Y-m-d H:i:s');

    // Check if the student has already submitted this assessment
    $check_submission_query = $conn->query("SELECT submission_id FROM student_submission WHERE assessment_id = '$assessment_id' AND student_id = '$student_id'");
    if ($check_submission_query->num_rows > 0) {
        die("You have already submitted this assessment.");
    }

    // Get the class_id for the student
    $class_query = $conn->query("SELECT class_id FROM student_enrollment WHERE student_id = '$student_id' AND status = 1");
    if ($class_query->num_rows == 0) {
        die("Student is not enrolled in any active class.");
    }
    $class_data = $class_query->fetch_assoc();
    $class_id = $class_data['class_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert submission details
        $insert_submission_query = "INSERT INTO student_submission (assessment_id, student_id, student_score, status, date_taken) 
                                    VALUES ('$assessment_id', '$student_id', 0, 0, '$date_taken')";
        $conn->query($insert_submission_query);
        $submission_id = $conn->insert_id;

        $total_score = 0;
        $total_questions = 0;

        // Process answers
        foreach ($answers as $question_id => $answer) {
            $question_query = $conn->query("SELECT ques_type, total_points FROM questions WHERE question_id = '" . $conn->real_escape_string($question_id) . "'");
            $question_data = $question_query->fetch_assoc();
            $question_type = $question_data['ques_type'];
            $question_points = $question_data['total_points'];

            $total_questions += $question_points;

            if ($question_type == 1 || $question_type == 3) {
                // Multiple Choice or True/False
                $option_query = $conn->query("SELECT option_id FROM question_options WHERE question_id = '" . $conn->real_escape_string($question_id) . "' AND option_txt = '" . $conn->real_escape_string($answer) . "'");
                $option_data = $option_query->fetch_assoc();
                $option_id = $option_data['option_id'];
                $is_right = check_correctness($question_id, $answer, $question_type, $conn);
                $total_score += $is_right ? $question_points : 0;

                $insert_answer_query = "INSERT INTO student_answer (student_id, answer_text, submission_id, question_id, option_id, is_right) 
                                        VALUES ('$student_id', '" . $conn->real_escape_string($answer) . "', '$submission_id', '" . $conn->real_escape_string($question_id) . "', '$option_id', '$is_right')";
                $conn->query($insert_answer_query);

            } elseif ($question_type == 2) {
                // Multiple Selection
                $answer_score = 0;
                $selected_answers = is_array($answer) ? $answer : [$answer];
                
                // Calculate correct answers and scoring
                foreach ($selected_answers as $choice) {
                    $choice = strtolower(trim($choice));
                    $option_query = $conn->query("SELECT option_id, is_right FROM question_options WHERE question_id = '" . $conn->real_escape_string($question_id) . "' AND option_txt = '" . $conn->real_escape_string($choice) . "'");
                    if ($option_query && $option_query->num_rows > 0) {
                        $option_data = $option_query->fetch_assoc();
                        $option_id = $option_data['option_id'];
                        $is_right = $option_data['is_right'];

                        // Insert answer
                        $insert_answer_query = "INSERT INTO student_answer (student_id, answer_text, submission_id, question_id, option_id, is_right) 
                                                VALUES ('$student_id', '" . $conn->real_escape_string($choice) . "', '$submission_id', '" . $conn->real_escape_string($question_id) . "', '$option_id', '$is_right')";
                        $conn->query($insert_answer_query);

                        // Update score based on correctness
                        if ($is_right) {
                            $answer_score += 1; // Increment score for correct option
                        }
                    }
                }

                // Calculate total score for multiple selection (partial credit)
                $total_options_query = $conn->query("SELECT COUNT(*) as total FROM question_options WHERE question_id = '" . $conn->real_escape_string($question_id) . "'");
                $total_options_data = $total_options_query->fetch_assoc();
                $total_options = $total_options_data['total'];

                $total_correct_options_query = $conn->query("SELECT COUNT(*) as total FROM question_options WHERE question_id = '" . $conn->real_escape_string($question_id) . "' AND is_right = 1");
                $total_correct_options_data = $total_correct_options_query->fetch_assoc();
                $total_correct_options = $total_correct_options_data['total'];

                $total_score += ($answer_score / $total_correct_options) * $question_points;

            } elseif ($question_type == 4 || $question_type == 5) {
                // Fill-in-the-blank or identification
                $is_right = check_correctness($question_id, $answer, $question_type, $conn);
                $total_score += $is_right ? $question_points : 0;

                $insert_answer_query = "INSERT INTO student_answer (student_id, answer_text, submission_id, question_id, is_right) 
                                        VALUES ('$student_id', '" . $conn->real_escape_string($answer) . "', '$submission_id', '" . $conn->real_escape_string($question_id) . "', '$is_right')";
                $conn->query($insert_answer_query);
            }
        }

        // Update student_submission with the final score
        $update_submission_query = "UPDATE student_submission SET student_score = '$total_score', status = 1 WHERE submission_id = '$submission_id'";
        $conn->query($update_submission_query);

        // Calculate remarks
        $pass_mark = 0.5 * $total_questions;
        $remarks = ($total_score >= $pass_mark) ? 1 : 0;

        // Get assessment mode
        $assessment_mode_query = $conn->query("SELECT assessment_mode FROM assessment WHERE assessment_id = '$assessment_id'");
        $assessment_mode_data = $assessment_mode_query->fetch_assoc();
        $assessment_mode = $assessment_mode_data['assessment_mode'];

        $rank = ($assessment_mode == 1) ? NULL : 0;

        // Insert results into student_results table
        $insert_results_query = "
            INSERT INTO student_results (assessment_id, student_id, class_id, items, score, remarks, rank)
            VALUES ('$assessment_id', '$student_id', '$class_id', '$total_questions', '$total_score', '$remarks', " . ($rank === NULL ? "NULL" : "'$rank'") . ")
        ";
        $conn->query($insert_results_query);

        // Commit transaction
        $conn->commit();

        echo "Assessment submitted successfully. Your score is $total_score out of $total_questions.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error submitting assessment: " . $e->getMessage();
    }

    $conn->close();
} else {
    echo "No form submitted.";
}
?>