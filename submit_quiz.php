<?php
require 'db_connect.php';
require 'auth.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_SESSION['login_id'];
    
    // Fetch details from the form submitted
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $answers = $_POST['answers'];
    $time_elapsed = isset($_POST['time_elapsed']) ? json_decode($_POST['time_elapsed'], true) : [];
    
    // Set date taken to the current date and time
    $date_taken = date('Y-m-d H:i:s');

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
        $total_possible_score = 0;
        $index = 0;

        // Process answers from the form
        foreach ($answers as $question_id => $answer) {
            // Get the time spent on this specific question
            $time_spent = isset($time_elapsed[$index]) ? $time_elapsed[$index] : 0;

            // Fetch the question type and total points of the question
            $question_query = $conn->query("
                SELECT ques_type, total_points 
                FROM questions WHERE question_id = '" . $conn->real_escape_string($question_id) . "'
            ");
            $question_data = $question_query->fetch_assoc();
            $question_type = $question_data['ques_type'];
            $question_points = $question_data['total_points'];

            $total_possible_score += $question_points;

            // Determine the answer type based on the question type
            // Multiple Choice or True/False
            if ($question_type == 1 || $question_type == 3) {
                // Assign answer type value
                $answer_type = ($question_type == 1) ? 'multiple choices' : 'true or false';

                // Fetch answer details
                $option_query = $conn->query("
                    SELECT option_id, option_txt 
                    FROM question_options 
                    WHERE question_id = '" . $conn->real_escape_string($question_id) . "' 
                    AND LOWER(TRIM(option_txt)) = '" . $conn->real_escape_string(strtolower(trim($answer))) . "'
                ");

                if ($option_query && $option_query->num_rows>0){
                    $option_data = $option_query->fetch_assoc();
                    $option_id = $option_data['option_id'];
                    $option_value = strtolower(trim($option_data['option_txt']));

                    // Fetch correct option and compare with answer
                    $correct_answer_query = $conn->query("
                        SELECT option_txt 
                        FROM question_options 
                        WHERE question_id = '$question_id' 
                        AND is_right = 1
                    ");

                    if ($correct_answer_query && $correct_answer_query->num_rows > 0) {
                        $correct_option_txt = strtolower(trim($correct_answer_query->fetch_assoc()['option_txt']));
                        $is_right = ($option_value === $correct_option_txt) ? 1 : 0;
                    }

                } else {
                    $option_id = NULL;
                    $option_value = 'NO ANSWER';
                }
                
                // Calculate score
                $total_score += $is_right ? $question_points : 0;

                // Insert answer details into the database
                $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, answer_value, option_id, is_right, time_elapsed) 
                                        VALUES ('$submission_id', '$question_id', '$answer_type', '$option_value', " . ($option_id === NULL ? "NULL" : "'$option_id'") . ", '$is_right', " . ($time_spent === 0 ? "NULL" : $time_spent) . ")";
                $conn->query($insert_answer_query);

            // Multiple Selection
            } elseif ($question_type == 2) {
                // Assign answer type value
                $answer_type = 'multiple selection';

                // Fetch the array of selected answers
                $selected_answers = is_array($answer) ? array_map('strtolower', array_map('trim', $answer)) : [strtolower(trim($answer))];
                
                // Handle empty answers
                if (count($selected_answers) === 0) {
                    $option_id = NULL;
                    $choice = 'NO ANSWER';
                    $is_right = 0;

                    // Insert the answer into student_answer
                    $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, answer_value, option_id, is_right, time_elapsed) 
                                            VALUES ('$submission_id', '$question_id', '$answer_type', '$choice', " . ($option_id === NULL ? "NULL" : "'$option_id'") . ", '$is_right', " . ($time_spent === 0 ? "NULL" : $time_spent) . ")";
                    $conn->query($insert_answer_query);
                // Handle cases with answers
                } else {
                    $all_answers_correct = true; // Correctness tracker
            
                    // Fetch correct answers from the database
                    $correct_answers_query = $conn->query("
                        SELECT option_txt 
                        FROM question_options 
                        WHERE question_id = '" . $conn->real_escape_string($question_id) . "' 
                        AND is_right = 1
                    ");

                    // Store all correct answers into an array
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
                                $all_answers_correct = false; // There is a wrong answer selected
                                break;
                            }
                        }
                        // Ensure no correct answer is missed
                        foreach ($correct_answers as $correct_answer) {
                            if (!in_array($correct_answer, $selected_answers)) {
                                $all_answers_correct = false; // There is a correct answer not selected
                                break;
                            }
                        }
                    }
                
                    // Insert each selected answer into student_answer
                    foreach ($selected_answers as $choice) {
                        // Fetch the option_id for each selected answer
                        $option_query = $conn->query("
                            SELECT option_id 
                            FROM question_options 
                            WHERE question_id = '" . $conn->real_escape_string($question_id) . "' 
                            AND LOWER(TRIM(option_txt)) = '" . $conn->real_escape_string($choice) . "'
                        ");

                        // If the answer matches an option
                        if ($option_query && $option_query->num_rows > 0) {
                            $option_data = $option_query->fetch_assoc();
                            $option_id = $option_data['option_id'];
                        // If there is no matches
                        } else {
                            $option_id = NULL;
                            $choice = 'NO ANSWER';
                        }
                
                        // Determine if the choice is correct
                        $is_right = in_array($choice, $correct_answers) ? 1 : 0;
                
                        // Insert selected answer into student_answer
                        $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, answer_value, option_id, is_right, time_elapsed) 
                                                VALUES ('$submission_id', '$question_id', '$answer_type', '$choice', " . ($option_id === NULL ? "NULL" : "'$option_id'") . ", '$is_right', " . ($time_spent === 0 ? "NULL" : $time_spent) . ")";
                        $conn->query($insert_answer_query);
                    }
                
                    // Add the points if all answers are correct
                    if ($all_answers_correct) {
                        $total_score += $question_points;
                    }
                }
            
            } elseif ($question_type == 4 || $question_type == 5) {
                // Assign answer type value
                $answer_type = ($question_type == 4) ? 'fill in the blank' : 'identification';

                // Fetch answer details
                $text_query = $conn->query("
                    SELECT identification_id, identification_answer 
                    FROM question_identifications 
                    WHERE question_id = '" . $conn->real_escape_string($question_id) . "' 
                    AND LOWER(TRIM(identification_answer)) = '" . $conn->real_escape_string(strtolower(trim($answer))) . "'
                ");

                // If the student answer matches the correct answer
                if ($text_query && $text_query->num_rows>0){
                    $text_data = $text_query->fetch_assoc();
                    $text_id = $text_data['identification_id'];
                    $text_value = strtolower(trim($text_data['identification_answer']));
                    $is_right = 1;
                // If there is no match
                } else {
                    $text_id = NULL;
                    if ($answer == '') {
                        $text_value = 'NO ANSWER'; // Set the text value as no answer when the answer is empty
                    } else {
                        $text_value = strtolower(trim($answer)); // Set the text value as the student's answer if it is wrong
                    }
                    $is_right = 0;
                }

                // Calculate Score
                $total_score += $is_right ? $question_points : 0;

                // Insert answer details into the database
                $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, answer_value, identification_id, is_right, time_elapsed) 
                                        VALUES ('$submission_id', '$question_id', '$answer_type', '$text_value', " . ($text_id === NULL ? "NULL" : "'$text_id'") . ", '$is_right', " . ($time_spent === 0 ? "NULL" : $time_spent) . ")";
                $conn->query($insert_answer_query);
            }
            $index++;
        }

        // Calculate total possible score based on the total points of each question in the assessment
        /*$total_possible_score_query = $conn->query("SELECT SUM(total_points) as total_possible_score 
                                                    FROM questions
                                                    WHERE assessment_id = '$assessment_id'");
        $total_possible_score_data = $total_possible_score_query->fetch_assoc();
        $total_possible_score = $total_possible_score_data['total_possible_score'];*/

        

        // Get assessment mode
        $assessment_mode_query = $conn->query("SELECT assessment_mode FROM assessment WHERE assessment_id = '$assessment_id'");
        $assessment_mode_data = $assessment_mode_query->fetch_assoc();
        $assessment_mode = $assessment_mode_data['assessment_mode'];

        $rank = NULL;

        $score = ($assessment_mode != 3) ? $total_score : 0;

        $assessment_score = ($assessment_mode != 3) ? $total_possible_score : 15;

        // Calculate remarks
        $pass_mark = 0.5 * $total_possible_score;
        $remarks = ($assessment_mode != 3) ? (($score >= $pass_mark) ? 'Passed' : 'Failed') : NULL;

        // Insert results into student_results table
        $insert_results_query = "
            INSERT INTO student_results (submission_id, assessment_id, student_id, total_score, score, remarks, rank)
            VALUES ('$submission_id', '$assessment_id', '$student_id', '$assessment_score', '$score', " . ($remarks === NULL ? "NULL" : "'$remarks'") . ", '$rank')
            ";
        if ($conn->query($insert_results_query)) {
            echo "Results inserted successfully!";
        } else {
            echo "Error inserting results: " . $conn->error;
        }

        $conn->close();
        echo "Assessment submitted successfully. Your score is $total_score out of $total_possible_score.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error submitting assessment: " . $e->getMessage();
    }
} else {
    echo "No form submitted.";
}
?>