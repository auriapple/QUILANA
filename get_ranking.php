<?php
include('db_connect.php');

// Assuming $_GET['assessment_id'] is passed
if (isset($_GET['assessment_id'])) {
    $assessment_id = $conn->real_escape_string($_GET['assessment_id']);

    // Fetch assessment mode
    $assessment_query = $conn->query("
        SELECT assessment_mode, passing_rate, max_points, student_count, remaining_points
        FROM assessment
        WHERE assessment_id = '$assessment_id'
    ");
    $assessment_data = $assessment_query->fetch_assoc();
    $assessment_mode = $assessment_data['assessment_mode'];

    if ($assessment_mode == 3) {
        // Fetch scoring details
        $passing_rate = $assessment_data['passing_rate'];
        $max_points = $assessment_data['max_points'];
        $student_count = $assessment_data['student_count'];
        $remaining_points = $assessment_data['remaining_points'];

        // Fetch necessary data
        $administer_query = $conn->query("
            SELECT administer_id
            FROM administer_assessment
            WHERE assessment_id = '$assessment_id'
        ");
        $administer_data = $administer_query->fetch_assoc();
        $administer_id = $administer_data['administer_id'];

        // Fetch all questions in the assessment
        $question_query = $conn->query("
            SELECT question_id
            FROM questions
            WHERE assessment_id = '$assessment_id'
        ");
        $question_ids = [];
        while ($question_data = $question_query->fetch_assoc()) {
            $question_ids[] = $question_data['question_id'];
        }

        $question_ids_placeholder = implode(',', array_map('intval', $question_ids));

        // Fetch student answer details
        $details_query = $conn->query("
            SELECT ss.student_id, sa.time_elapsed, sa.question_id, sa.is_right
            FROM student_answer sa
            JOIN student_submission ss ON sa.submission_id = ss.submission_id
            JOIN administer_assessment aa ON ss.assessment_id = aa.assessment_id
            WHERE aa.administer_id = '$administer_id'
            AND sa.question_id IN ($question_ids_placeholder)
            ORDER BY sa.question_id, sa.time_elapsed ASC
        ");
        $answer_data = [];
        while ($details_row = $details_query->fetch_assoc()) {
            $answer_data[$details_row['question_id']][] = $details_row;
        }

        // Store student score
        $scores = [];

        // Process student answers per question id
        foreach ($answer_data as $question_id => $student_answers) {
            $student_data = [];

            // Process answers per student
            foreach ($student_answers as $student_answer) {
                $student_id = $student_answer['student_id'];
                $student_data[$student_id][] = [
                    'time_elapsed' => $student_answer['time_elapsed'],
                    'is_right' => $student_answer['is_right']
                ];
            }

            // Log the student data for debugging
            error_log('Fetched student data: ' . print_r($student_data, true));

            // Prepare to rank students
            $current_rank = 0;
            $previous_time = NULL;

            // Prepare to score students
            $score_rank = [];
            $student_ranking = 0;

            // Rank and score students
            foreach ($student_data as $student_id => $time) {
                usort($time, function($a, $b) {
                    return $a['time_elapsed'] <=> $b['time_elapsed'];
                });

                // Get the fastest time and whether that answer is correct
                $fastest_time = $time[0]['time_elapsed'];
                $is_right = $time[0]['is_right']; // Assuming the score is based on the fastest attempt

                // Rank assignment
                $answer_rank = ($previous_time === $fastest_time) ? $current_rank : ++$current_rank;
                $previous_time = $fastest_time;

                // Initialize score
                if (!isset($scores[$student_id])) {
                    $scores[$student_id] = 0;
                }

                // Initialize points
                $points = 0;

                // Fetch the question type
                $question_type_query = $conn->query("
                    SELECT ques_type
                    FROM questions
                    WHERE assessment_id = '$assessment_id' AND question_id = '$question_id'
                ");
                $question_type_data = $question_type_query->fetch_assoc();

                // Logic for scoring
                if ($question_type_data['ques_type'] != 2) { // Single answer
                    // Assign score
                    if ($is_right) {
                        $student_ranking++;
                        $score_rank[$student_id] = $student_ranking;

                        // Assign points based on the rank in score_rank
                        $points = $student_ranking <= $student_count ? $max_points : $remaining_points;
                    } else {
                        $points = 0;
                    }
                } else { // Multiple answers
                    $all_answers_correct = true;

                    // Fetch correct answers
                    $correct_answers_query = $conn->query("
                        SELECT option_txt 
                        FROM question_options 
                        WHERE question_id = '" . $conn->real_escape_string($question_id) . "' 
                        AND is_right = 1
                    ");
                    $correct_answers = [];
                    while ($row = $correct_answers_query->fetch_assoc()) {
                        $correct_answers[] = strtolower(trim($row['option_txt']));
                    }

                    // Fetch selected answers
                    $selected_answers_query = $conn->query("
                        SELECT answer_value 
                        FROM student_answer 
                        WHERE question_id = '" . $conn->real_escape_string($question_id) . "' 
                        AND submission_id = (
                            SELECT submission_id 
                            FROM student_submission 
                            WHERE student_id = '" . $conn->real_escape_string($student_id) . "' 
                            AND assessment_id = '$assessment_id'
                        )
                    ");
                    $selected_answers = [];
                    while ($row = $selected_answers_query->fetch_assoc()) {
                        $selected_answers[] = strtolower(trim($row['answer_value']));
                    }

                    // Check correctness
                    if (count($selected_answers) != count($correct_answers)) {
                        $all_answers_correct = false;
                    } else {
                        foreach ($selected_answers as $selected_answer) {
                            if (!in_array($selected_answer, $correct_answers)) {
                                $all_answers_correct = false;
                                break;
                            }
                        }
                        foreach ($correct_answers as $correct_answer) {
                            if (!in_array($correct_answer, $selected_answers)) {
                                $all_answers_correct = false;
                                break;
                            }
                        }
                    }

                    // Assign score
                    if ($all_answers_correct) {
                        $student_ranking++;
                        $score_rank[$student_id] = $student_ranking;

                        // Assign points based on the rank in score_rank
                        $points = $student_ranking <= $student_count ? $max_points : $remaining_points;
                    } else {
                        $points = 0;
                    }
                }

                // Add score
                $scores[$student_id] += $points;
                error_log("student id: $student_id, student score: $scores[$student_id]");

                // Update the rank in the database
                $answer_rank_query = "
                    UPDATE student_answer
                    SET answer_rank = '$answer_rank'
                    WHERE submission_id = (
                        SELECT submission_id
                        FROM student_submission
                        WHERE student_id = '$student_id'
                        AND assessment_id = '$assessment_id'
                    ) AND question_id = '$question_id'
                ";
                $conn->query($answer_rank_query);
            }
        }

        // Update scores in the student_results table
        $total_possible_score = count($question_ids) * $max_points;
        foreach ($scores as $student_id => $score) {
            $update_result_query = "
                UPDATE student_results
                SET 
                    score = score + $score,
                    total_score = $total_possible_score
                WHERE student_id = '$student_id' AND assessment_id = '$assessment_id'
            ";
            $conn->query($update_result_query);
        }
    }
    
    // Initialize score and rank variables
    $current_rank = 0;
    $previous_score = NULL;

    // Fetch all scores for this assessment
    $scores_query = $conn->query("
        SELECT student_id, score
        FROM student_results
        WHERE assessment_id = '$assessment_id'
        ORDER BY score DESC
    ");

    // Calculate rank
    if ($scores_query && $scores_query->num_rows > 0 ) {
        while ($scores_data = $scores_query->fetch_assoc()) {
            $student_score = $scores_data['score'];
            $student_id = $scores_data['student_id'];

            if ($student_score !== $previous_score) {
                $current_rank++;
                $previous_score = $student_score;
            }

            $update_rank_query = "
                UPDATE student_results
                SET rank = '$current_rank'
                WHERE assessment_id = '$assessment_id' AND student_id = '$student_id'
            ";
            $conn->query($update_rank_query);
        }

        // Update status rank to 1 (ranked)
        $update_status_query = "
            UPDATE administer_assessment
            SET ranks_status = 1
            WHERE assessment_id = '$assessment_id'
        ";
        if ($conn->query($update_status_query)) {
            echo "success";
        } else {
            echo "error: " . $conn->error;
        }
    } else {
        echo "error: no scores found for the assessment";
    }  
} else {
    echo "error: assessment id not provided";
}
?>