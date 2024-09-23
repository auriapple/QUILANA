<?php
// Database connection
require 'db_connect.php';
require 'auth.php';
session_start();

function check_correctness($question_id, $answer_value, $question_type, $conn) {
    
    $answer_value = strtolower(trim($answer_value)); // Normalize input for comparison

    // Multiple Choice or True/False
    if ($question_type == 1 || $question_type == 3) {
        // Fetch the correct option from the database
        $correct_answer_query = $conn->query("SELECT option_txt FROM question_options WHERE question_id = '$question_id' AND is_right = 1");
        if ($correct_answer_query && $correct_answer_query->num_rows > 0) {
            $correct_answer_data = $correct_answer_query->fetch_assoc();
            $correct_option_txt = strtolower(trim($correct_answer_data['option_txt']));


            // Compare student's answer with the correct option
            return ($answer_value == $correct_option_txt) ? 1 : 0;
        }
        return 0;

    // Multiple Selection
    } elseif ($question_type == 2) {
        // Fetch correct options from the database
        $correct_answers_query = $conn->query("SELECT option_txt FROM question_options WHERE question_id = '$question_id' AND is_right = 1");
        $correct_answers = [];
        while ($row = $correct_answers_query->fetch_assoc()) {
            $correct_answers[] = strtolower(trim($row['option_txt']));
        }

        $selected_answers = is_array($answer_value) ? array_map('strtolower', array_map('trim', $answer_value)) : [strtolower(trim($answer_value))];

        // Ensure the number of selected answers matches the number of correct answers
        if (count($selected_answers) != count($correct_answers)) {
            return 0; // If the number of selected answers doesn't match, return incorrect
        }

        // Ensure all selected answers are correct
        foreach ($selected_answers as $answer) {
            if (!in_array($answer, $correct_answers)) {
                return 0; // If any selected answer is not correct, return incorrect
            }
        }

        // Ensure no correct answer is missed
        foreach ($correct_answers as $correct_answer) {
            if (!in_array($correct_answer, $selected_answers)) {
                return 0; // If any correct answer is missed, return incorrect
            }
        }

        return 1; // All selected answers are correct, and no incorrect answers were selected

    // Fill-in-the-blank or Identification (text input)
    } elseif ($question_type == 4 || $question_type == 5) {
        // Fetch the correct text answer from the question_identifications table
        $correct_text_query = $conn->query("SELECT identification_answer FROM question_identifications WHERE question_id = '$question_id'");
        if ($correct_text_query && $correct_text_query->num_rows > 0) {
            $correct_text_data = $correct_text_query->fetch_assoc();
            $correct_text = strtolower(trim($correct_text_data['identification_answer']));

            // Compare student's answer with correct text
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


    //$conn->begin_transaction();

    try {
        // Fetch administer assessment details
        $administer_query = $conn->query("
            SELECT aa.administer_id 
            FROM administer_assessment aa
            JOIN assessment a ON a.assessment_id = aa.assessment_id
            WHERE a.assessment_id = '$assessment_id'
        ");

        // Check if there is administer assessment details
        if ($administer_query->num_rows>0) {
            $administer_data = $administer_query->fetch_assoc();
            $administer_id = $administer_data['administer_id'];
            
            // Update the join_assessment status to 2 (finished)
            $update_join_query = $conn->query("
                UPDATE join_assessment 
                SET status = 2
                WHERE administer_id = '$administer_id' 
                AND student_id = '$student_id'
            ");
                
            if (!$update_join_query) {
                echo "Error updating record: " . $conn->error;
            }
        }

        // Insert submission details into the student_submission table
        $insert_submission_query = "INSERT INTO student_submission (student_id, assessment_id, date_taken) 
                                    VALUES ('$student_id', '$assessment_id', '$date_taken')";

        if ($conn->query($insert_submission_query)) {
            $submission_id = $conn->insert_id;
        } else {
            die("Error inserting submission details: " . $conn->error);
        }

        // Initialize score counter
        $total_score = 0;
        //$total_possible_score = 0;

        // Process answers from the form
        foreach ($answers as $question_id => $answer) {
            // Fetch the question type and total points of the question
            $question_query = $conn->query("SELECT ques_type, total_points FROM questions WHERE question_id = '" . $conn->real_escape_string($question_id) . "'");
            $question_data = $question_query->fetch_assoc();
            $question_type = $question_data['ques_type'];
            $question_points = $question_data['total_points'];

            //$total_possible_score += $question_points;

            // Determine the answer type based on the question type
            // Multiple Choice or True/False
            if ($question_type == 1 || $question_type == 3) {
                if ($question_type == 1) {
                    $answer_type = 'multiple choices';
                }
                else {
                    $answer_type = 'true or false';
                }

                $option_query = $conn->query("SELECT option_id, option_txt FROM question_options WHERE question_id = '" . $conn->real_escape_string($question_id) . "' AND LOWER(TRIM(option_txt)) = '" . $conn->real_escape_string(strtolower(trim($answer))) . "'");
                if ($option_query && $option_query->num_rows>0){
                    $option_data = $option_query->fetch_assoc();
                    $option_id = $option_data['option_id'];
                    $option_value = strtolower(trim($option_data['option_txt']));
                } /*else {
                    $option_id = NULL;
                    $option_value = 'NO ANSWER';
                }*/
                
                // Check if the answer is correct
                $is_right = check_correctness($question_id, $option_value, $question_type, $conn);
                $total_score += $is_right ? $question_points : 0;

                // Insert answer details into the database
                $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, answer_value, option_id, is_right) 
                                        VALUES ('$submission_id', '$question_id', '$answer_type', '$option_value', '$option_id', '$is_right')";
                $conn->query($insert_answer_query);

            // Multiple Selection
            } elseif ($question_type == 2) {
                $answer_type = 'multiple selection';

                $selected_answers = is_array($answer) ? array_map('strtolower', array_map('trim', $answer)) : [strtolower(trim($answer))];
                $all_answers_correct = true;
            
                // Fetch correct answers from the database
                $correct_answers_query = $conn->query("SELECT option_txt FROM question_options WHERE question_id = '" . $conn->real_escape_string($question_id) . "' AND is_right = 1");
                $correct_answers = [];
                while ($row = $correct_answers_query->fetch_assoc()) {
                    $correct_answers[] = strtolower(trim($row['option_txt']));
                }
            
                // Compare the number of selected answers with the correct answers
                if (count($selected_answers) != count($correct_answers)) {
                    $all_answers_correct = false; // Mismatch in the number of selected vs correct answers
                } else {
                    // Check each selected answer
                    foreach ($selected_answers as $selected_answer) {
                        if (!in_array($selected_answer, $correct_answers)) {
                            $all_answers_correct = false;
                            break; // Stop checking if any answer is wrong
                        }
                    }
            
                    // Ensure no correct answer is missed
                    foreach ($correct_answers as $correct_answer) {
                        if (!in_array($correct_answer, $selected_answers)) {
                            $all_answers_correct = false;
                            break;
                        }
                    }
                }
            
                // Insert each selected answer into student_answer
                foreach ($selected_answers as $choice) {
                    // Fetch the option_id for each selected answer
                    $option_query = $conn->query("SELECT option_id FROM question_options WHERE question_id = '" . $conn->real_escape_string($question_id) . "' AND LOWER(TRIM(option_txt)) = '" . $conn->real_escape_string($choice) . "'");
                    if ($option_query && $option_query->num_rows > 0) {
                        $option_data = $option_query->fetch_assoc();
                        $option_id = $option_data['option_id'];
                    } /*else {
                        $option_id = NULL;
                        $choice = 'NO ANSWER';
                    }*/
            
                    // Determine if the choice is correct
                    $is_right = in_array($choice, $correct_answers) ? 1 : 0;
            
                    // Insert selected answer into student_answer
                    $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, answer_value, option_id, is_right) 
                                            VALUES ('$submission_id', '$question_id', '$answer_type', '$choice', '$option_id', '$is_right')";
                    if (!$conn->query($insert_answer_query)) {
                        die("Error inserting into student_answer: " . $conn->error);
                    }
                }
            
                // Add the points if all answers are correct
                if ($all_answers_correct) {
                    $total_score += $question_points;
                }

            } elseif ($question_type == 4 || $question_type == 5) {
                // Fill-in-the-blank or identification
                if ($question_type == 4) {
                    $answer_type = 'fill in the blank';
                } else {
                    $answer_type = 'identification';
                }

                $text_query = $conn->query("SELECT identification_id, identification_answer FROM question_identifications WHERE question_id = '" . $conn->real_escape_string($question_id) . "' AND LOWER(TRIM(identification_answer)) = '" . $conn->real_escape_string(strtolower(trim($answer))) . "'");
                if ($text_query && $text_query->num_rows>0){
                    $text_data = $text_query->fetch_assoc();
                    $text_id = $text_data['identification_id'];
                    $text_value = strtolower(trim($text_data['identification_answer']));
                }

                // Check if the answer is correct
                $is_right = check_correctness($question_id, $text_value, $question_type, $conn);
                $total_score += $is_right ? $question_points : 0;

                // Insert answer details into the database
                $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, answer_value, identification_id, is_right) 
                                        VALUES ('$submission_id', '$question_id', '$answer_type', '$text_value', '$text_id', '$is_right')";
                $conn->query($insert_answer_query);

            }
        }

        // Calculate total possible score based on the total points of each question in the assessment
        $total_possible_score_query = $conn->query("SELECT SUM(total_points) as total_possible_score 
                                                    FROM questions
                                                    WHERE assessment_id = '$assessment_id'");
        $total_possible_score_data = $total_possible_score_query->fetch_assoc();
        $total_possible_score = $total_possible_score_data['total_possible_score'];

        // Calculate remarks
        $pass_mark = 0.5 * $total_possible_score;
        $remarks = ($total_score >= $pass_mark) ? 'Passed' : 'Failed';

        // Get assessment mode
        $assessment_mode_query = $conn->query("SELECT assessment_mode FROM assessment WHERE assessment_id = '$assessment_id'");
        $assessment_mode_data = $assessment_mode_query->fetch_assoc();
        $assessment_mode = $assessment_mode_data['assessment_mode'];

        //$rank = ($assessment_mode == 1) ? NULL : 0;

        // Insert results into student_results table
        $insert_results_query = "
            INSERT INTO student_results (submission_id, assessment_id, student_id, total_score, score, remarks, rank)
            VALUES ('$submission_id', '$assessment_id', '$student_id', '$total_possible_score', '$total_score', '$remarks', " . ($rank === NULL ? "NULL" : "$rank") . ")
        ";
        if ($conn->query($insert_results_query)) {
            echo "Results inserted successfully!";
        } else {
            echo "Error inserting results: " . $conn->error;
        }

        // Calculate Rank
        if ($assessment_mode == 1){
            $rank = NULL;
        }
        else {
            $scores_query = $conn->query("
                SELECT DISTINCT score
                FROM student_results
                WHERE assessment_id = '$assessment_id'
                ORDER BY score DESC
            ");

            $rank = 0;
            $current_rank = 1;

            while ($row = $scores_query->fetch_assoc()) {
                $score = $row['score'];
                if ($score == $total_score){
                    $rank = $current_rank;
                    break;
                }
                $current_rank++;
            }

            // Update the rank in student_results table
            $update_rank_query = "
                UPDATE student_results
                SET rank = '$rank'
                WHERE submission_id = '$submission_id'
            ";
            if (!$conn->query($update_rank_query)) {
                die("Error updating rank: " . $conn->error);
            }
        }

        $conn->close();

        echo "Assessment submitted successfully. Your score is $total_score out of $total_possible_score.";
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