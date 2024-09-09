<?php
// Database connection
require 'db_connect.php';
session_start();

// Function to check if the answer is correct
function check_correctness($question_id, $answer_value, $question_type, $conn) {
    if ($question_type == 1 || $question_type == 3) {
        // Multiple Choices or True/False
        // Fetch the correct option from the question_options table
        $correct_answer_query = $conn->query("SELECT is_right, option_txt FROM question_options WHERE question_id = '$question_id'");
        $correct_answer_data = $correct_answer_query->fetch_assoc();
        $correct_option_txt = $correct_answer_data['option_txt'];
        
        // Compare student's answer with correct option_txt
        return ($answer_value == $correct_option_txt) ? 1 : 0;

    } elseif ($question_type == 2) {
        // Multiple Selection
        // Fetch correct options from the question_options table
        $correct_answers_query = $conn->query("SELECT option_txt FROM question_options WHERE question_id = '$question_id' AND is_right = 1");
        $correct_answers = [];
        while ($row = $correct_answers_query->fetch_assoc()) {
            $correct_answers[] = $row['option_txt'];
        }
        
        // Compare the array of student's answers with the correct answers
        return in_array($answer_value, $correct_answers) ? 1 : 0;

    } elseif ($question_type == 4 || $question_type == 5) {
        // Fill-in-the-blank or identification (text input)
        // Fetch the correct text answer from the question_identifications table
        $correct_text_query = $conn->query("SELECT identification_answer FROM question_identifications WHERE question_id = '$question_id'");
        $correct_text_data = $correct_text_query->fetch_assoc();
        $correct_text = strtolower(trim($correct_text_data['identification_answer'])); // Normalize case and trim whitespace for comparison
        
        // Compare student's answer with correct text (case-insensitive)
        return (strtolower($answer_value) == $correct_text) ? 1 : 0;
    }
    return 0; // Default to incorrect if no condition matches
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the necessary data from the form
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $student_id = $_SESSION['login_id'];
    $answers = $_POST['answers'];
    $date_taken = date('Y-m-d H:i:s');

    // Insert submission details into the student_submission table
    $insert_submission_query = "INSERT INTO student_submission (student_id, assessment_id, date_taken) 
                                VALUES ('$student_id', '$assessment_id', '$date_taken')";

    if ($conn->query($insert_submission_query)) {
        $submission_id = $conn->insert_id;
    }

    // Insert answer details into the student_answer, answer_text, and answer_options table
    // Loop the answers submitted throught the form
    if (!empty($answers)) {
        foreach ($answers as $question_id => $answer) {
            // Fetch the question type from the database
            $question_query = $conn->query("SELECT ques_type FROM questions WHERE question_id = '" . $conn->real_escape_string($question_id) . "'");
            $question_data = $question_query->fetch_assoc();
            $question_type = $question_data['ques_type'];

            // Determine the answer type based on the question type
            if ($question_type == 1) {
                // Multiple Choices (radio button)
                // Fetch option_txt from question_options for the selected answer
                $answer_query = $conn->query("SELECT option_txt FROM question_options WHERE option_id = '" . $conn->real_escape_string($answer) . "'");
                $answer_data = $answer_query->fetch_assoc();
                $answer_value = $answer_data['option_txt'];
                $answer_type = 'option'; // Single choice options go to the answer_options table
            
                // Check if the answer is correct
                $is_right = check_correctness($question_id, $answer_value, $question_type, $conn);
               
                // Insert into the student_answer table
                $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, is_right) 
                                        VALUES ('$submission_id', '$question_id', '$answer_type', '$is_right')";
                $conn->query($insert_answer_query);
                $answer_id = $conn->insert_id;

                // Insert into answer_options table for option answers
                $insert_option_query = "INSERT INTO answer_options (answer_id, option_value) VALUES ('$answer_id', '$answer_value')";
                $conn->query($insert_option_query);
            } elseif ($question_type == 2) {
                // Multiple Selection (checkbox)
                $answer_type = 'option';
                if (is_array($answer)) {
                    foreach ($answer as $choice_id) {
                        // Fetch option_txt from question_options for each selected answer
                        $choice_query = $conn->query("SELECT option_txt FROM question_options WHERE option_id = '" . $conn->real_escape_string($choice_id) . "'");
                        $choice_data = $choice_query->fetch_assoc();
                        $choice_value = $choice_data['option_txt'];

                        // Check if the answer is correct
                        $is_right = check_correctness($question_id, $choice_value, $question_type, $conn);
                       
                        // Insert into student_answer table
                        $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, is_right) 
                                                VALUES ('$submission_id', '$question_id', '$answer_type', '$is_right')";
                        $conn->query($insert_answer_query);
                        $answer_id = $conn->insert_id;

                        // Insert each choice into the answer_options table
                        $insert_option_query = "INSERT INTO answer_options (answer_id, option_value) VALUES ('$answer_id', '$choice_value')";
                        $conn->query($insert_option_query);
                    }
                }
            } elseif ($question_type == 3) {
                // True/False (radio button)
                // Fetch option_txt from question_options for the selected answer
                $answer_query = $conn->query("SELECT option_txt FROM question_options WHERE option_txt = '" . $conn->real_escape_string($answer) . "'");
                $answer_data = $answer_query->fetch_assoc();
                $answer_value = $answer_data['option_txt'];
                $answer_type = 'option';

                // Check if the answer is correct
                $is_right = check_correctness($question_id, $answer_value, $question_type, $conn);
            
                // Insert into the student_answer table
                $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, is_right) 
                                        VALUES ('$submission_id', '$question_id', '$answer_type', '$is_right')";
                $conn->query($insert_answer_query);
                $answer_id = $conn->insert_id;

                // Insert into answer_options table for option answers
                $insert_option_query = "INSERT INTO answer_options (answer_id, option_value) VALUES ('$answer_id', '$answer_value')";
                $conn->query($insert_option_query);

            } elseif ($question_type == 4 || $question_type == 5) {
                // Fill-in-the-blank or identification (text input)
                $text_value = trim($conn->real_escape_string($answer)); // Clean up the student's answer (trim whitespace)
                $answer_type = 'text'; // Text answers go into the answer_text table

                // Check if the answer is correct
                $is_right = check_correctness($question_id, $text_value, $question_type, $conn);
                
                // Insert into the student_answer table
                $insert_answer_query = "INSERT INTO student_answer (submission_id, question_id, answer_type, is_right) 
                                        VALUES ('$submission_id', '$question_id', '$answer_type', '$is_right')";
                $conn->query($insert_answer_query);
                $answer_id = $conn->insert_id;

                // Insert into answer_text table
                $insert_text_query = "INSERT INTO answer_text (answer_id, text_value) VALUES ('$answer_id', '$text_value')";
                $conn->query($insert_text_query);
            }
        }
    }

    // Retrieve the total number of correct answers
    $score_query = $conn->query("SELECT COUNT(*) as correct_answers FROM student_answer 
                                 WHERE submission_id = '$submission_id' AND is_right = 1");
    $score_data = $score_query->fetch_assoc();
    $correct_answers = $score_data['correct_answers'];

    // Calculate total possible score based on the total points of each question in the assessment
    $total_possible_score_query = $conn->query("SELECT SUM(total_points) as total_possible_score 
                                                FROM questions
                                                WHERE assessment_id = '$assessment_id'");
    $total_possible_score_data = $total_possible_score_query->fetch_assoc();
    $total_possible_score = $total_possible_score_data['total_possible_score'];

    // Calculate the remarks (pass/fail) based on 50% score threshold
    $pass_mark = 0.5 * $total_possible_score;
    $remarks = ($correct_answers >= $pass_mark) ? 'Passed' : 'Failed';

    // Rank will be NULL if question_mode = 1, otherwise calculate the rank
    $assessment_mode_query = $conn->query("SELECT assessment_mode FROM assessment WHERE assessment_id = '$assessment_id'");
    $assessment_mode_data = $assessment_mode_query->fetch_assoc();
    $assessment_mode = $assessment_mode_data['assessment_mode'];

    $rank = ($assessment_mode == 1) ? 'NULL' : '0';

    // Insert results into student_results table
    $insert_results_query = "
        INSERT INTO student_results (submission_id, assessment_id, student_id, total_score, score, remarks, rank)
        VALUES ('$submission_id', '$assessment_id', '$student_id', '$total_possible_score', '$correct_answers', '$remarks', $rank)
    ";

    if ($conn->query($insert_results_query)) {
        echo "Results inserted successfully!";
    } else {
        echo "Error inserting results: " . $conn->error;
    }
    
    $conn->close();
} echo "Answers submitted successfully.";
?>