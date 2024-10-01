<?php
include('db_connect.php');

// Assuming $_GET['assessment_id'] is passed
if (isset($_GET['assessment_id'])) {
    $assessment_id = $conn->real_escape_string($_GET['assessment_id']);

    // Fetch assessment mode
    $assessment_mode_query = $conn->query("
        SELECT assessment_mode
        FROM assessment
        WHERE assessment_id = '$assessment_id'
    ");
    $assessment_data = $assessment_mode_query->fetch_assoc();
    $assessment_mode = $assessment_data['assessment_mode'];

    if ($assessment_mode == 3) {
        // Fetch necessary data
        $administer_query = $conn->query("
            SELECT administer_id
            FROM administer_assessment
            WHERE assessment_id = '$assessment_id'
        ");
        $administer_data = $administer_query->fetch_assoc();
        $administer_id = $administer_data['administer_id'];

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

        $scores = [];
        $question_scores = [];

        // Process each question and calculate rankings
        foreach ($answer_data as $question_id => $student_answers) {
            if (!isset($question_scores[$question_id])) {
                $question_scores[$question_id] = [];
            }

            foreach ($student_answers as $student_answer) {
                $student_id = $student_answer['student_id'];

                // Only score for the first correct answer for this question
                if ($student_answer['is_right'] == 1 && !isset($question_scores[$question_id][$student_id])) {
                    $question_scores[$question_id][$student_id] = $student_answer['time_elapsed']; // Store time for ranking
                }
            }
        }

        foreach ($question_scores as $question_id => $students) {
            // Rank students by time
            $current_rank = 0;
            $previous_time = null;

            // Sort students by time for ranking
            asort($students); // Sort by time_elapsed
            foreach ($students as $student_id => $time_elapsed) {
                // Rank assignment
                $rank = ($previous_time === $time_elapsed) ? $current_rank : ++$current_rank;
                $previous_time = $time_elapsed;

                // Determine score based on rank
                $points = ($rank <= 3) ? 5 : 2; // Modify points as per your requirement
                if (!isset($scores[$student_id])) {
                    $scores[$student_id] = 0;
                }
                $scores[$student_id] += $points;

                // Update the rank in the database
                $answer_rank_query = "
                    UPDATE student_answer
                    SET answer_rank = '$rank'
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

        $total_possible_score = count($question_ids) * 5; // Assuming 5 points for each question
        // Update scores in the student_results table
        foreach ($scores as $student_id => $score) {
            $update_score_query = "
                UPDATE student_results
                SET score = score + $score
                WHERE student_id = '$student_id' AND assessment_id = '$assessment_id'
            ";
            $conn->query($update_score_query);

            // Calculate remarks
            $pass_mark = 0.5 * $total_possible_score;
            $remarks = ($score >= $pass_mark) ? 'Passed' : 'Failed';

            // Update remarks in the student_results table
            $update_remarks_query = "
                UPDATE student_results
                SET remarks = '$remarks'
                WHERE student_id = '$student_id' AND assessment_id = '$assessment_id'
            ";
            $conn->query($update_remarks_query);
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