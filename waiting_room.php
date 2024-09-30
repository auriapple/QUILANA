<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id is set in URL
if (!isset($_GET['assessment_id']) || !isset($_GET['student_id'])) {
    header('location: load_assessments.php');
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$student_id = $conn->real_escape_string($_GET['student_id']);

// Fetch assessment details
$assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");

if ($assessment_query->num_rows>0) {
    $assessment = $assessment_query->fetch_assoc();

    // Setting the assessment mode text for display
    $assessment_mode = '';
    if ($assessment['assessment_mode'] == 1){
        $assessment_mode = 'Normal Mode';
    } elseif ($assessment['assessment_mode'] == 2){
        $assessment_mode = 'Quiz Bee Mode';
    } elseif ($assessment['assessment_mode'] == 3){
        $assessment_mode = 'Speed Mode';
    }

    // Fetch administer assessment details
    $administer_query = $conn->query("
        SELECT administer_id, status
        FROM administer_assessment
        WHERE assessment_id = '$assessment_id'
    ");
    $administer_row = $administer_query->fetch_assoc();
    $administer_id = $administer_row['administer_id'];
    $status = $administer_row['status'];

    // Check if the assessment is really administered
    if ($administer_query->num_rows>0) {

        // Check if there is a join assessment record
        $join_query = $conn->query("
            SELECT * 
            FROM join_assessment 
            WHERE administer_id = '$administer_id' 
            AND student_id = '$student_id'
        ");

        // If there is no record yet
        if ($join_query->num_rows==0){
            // Check if the assessment has not yet started
            if ($status == 0) {
                // Insert the join details with the status of 0 (joined)
                $insert_join_query = $conn->query("
                    INSERT INTO join_assessment (student_id, administer_id, status)
                    VALUES ('$student_id', '$administer_id', 0)
                ");
                if (!$insert_join_query) {
                    echo "Error inserting record: " . $conn->error;
                }
            }
        // Check if there is already a record
        } else {
            // Check if the assessment has started
            if ($status == 1) {
                // Update the status of the join details to 1 (answering)
                $update_status_query = $conn->query("
                    UPDATE join_assessment
                    SET status = 1
                    WHERE administer_id = '$administer_id' AND student_id = '$student_id'
                ");
                if (!$update_status_query) {
                    echo "Error updating record: " . $conn->error;
                }

                // Set the redirection based on the assessment mode
                $redirect_url = '';
                if ($assessment['assessment_mode'] == 1){
                    $redirect_url = 'quiz.php';
                } elseif ($assessment['assessment_mode'] == 2){
                    $redirect_url = 'assessment_mode_2.php';
                } elseif ($assessment['assessment_mode'] == 3){
                    $redirect_url = 'assessment_mode_3.php';
                }

                // Redirect to the corrrect assessment page
                header("Location: $redirect_url?assessment_id=" . urlencode($assessment_id));
            }
        }     
    }
} else {
    header('location: load_assessment.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessment['assessment_name']); ?> | Quilana</title>
    <?php include('header.php') ?>
    <style>
        .assessment-details {
            align-items: center;
            position: relative;
            justify-content: center;
        }
        .general-details {
            justify-content: center;
            margin-top: 50px;
        }
        .general-details h1 {
            font-size: 36px;
            font-weight: bold;  
            text-align: center;
            color: #4A4CA6;
            margin-bottom: 0;
        }
        .general-details h3 {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            color: #4A4CA6;
            margin-top: 0;
        }
        .general-details h4 {
            font-size: 24px;
            text-align: center;
            color: #1E1A43;
            margin-top: 25px;
        }
        .instructions {
            width: 80%;
            height: auto;
            border: 3px solid #6A7AC7;
            border-radius: 25px;
            padding: 50px;
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 25px auto;
        }
        .instructions h3 {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            color: #1E1A43;
        }
        .instruction-text {
            margin-top: 10px;
            align-items: center;
            display: flex;
            flex-direction: column;
        }
        .instruction-text p {
            text-align: center;
            color: #4A4A4A;
            font-size: 18px;
        }
        .instruction-text ul {
            max-width: 65%;
        }
        .instruction-text li {
            color: #4A4A4A;
            font-size: 18px;
        }
        .message h5 {
            font-size: 24px;
            font-weight: bold;
            justify-content: center;
            text-align: center;
            color: #6A7AC7;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="content-wrapper">
        <div class="assessment-details">
            <div class="general-details">
                <h1><?php echo htmlspecialchars(strtoupper($assessment['assessment_name'])); ?></h1>
                <h3>(<?php echo htmlspecialchars($assessment_mode); ?>)</h3>
                <h4><?php echo htmlspecialchars(strtoupper($assessment['topic'])); ?></h4>
            </div>
            <div class="instructions">
                <h3>Instructions</h3>
                <div class="instruction-text">
                    <?php
                    // For Normal Mode
                    if ($assessment_mode == 'Normal Mode') {
                        echo "<ul>";
                        // Total Duration
                        echo "<li>Total " . (($assessment['assessment_type'] == 1) ? 'Quiz' : 'Exam') . " Duration: " . htmlspecialchars($assessment['time_limit']) . " minutes</li>";
                        
                        // Pointing system
                        $points_query = $conn->query("
                            SELECT DISTINCT total_points
                            FROM questions
                            WHERE assessment_id = $assessment_id
                        ");
                        $points = [];
                        while ($row = $points_query->fetch_assoc()) {
                            $points[] = $row['total_points'];
                        }
                        if (count($points) === 1) {
                            echo "<li>" . htmlspecialchars($points[0]) . " points per correct answer</li>";
                        }

                        // No points for skipped questions
                        echo "<li>No points for skipped questions</li>";
                        echo "</ul>";

                    // For Quiz Bee Mode
                    } elseif ($assessment_mode == 'Quiz Bee Mode'){
                        echo "<ul>";
                        // Total Duration

                        // Total Quiz Duration (sum of all time limits)
                        $total_duration_query = $conn->query("
                            SELECT SUM(time_limit) AS total_duration
                            FROM questions
                            WHERE assessment_id = $assessment_id
                        ");
                        $total_duration_data = $total_duration_query->fetch_assoc();
                        $total_duration_seconds = $total_duration_data['total_duration'];

                        // Convert total duration from seconds to minutes and seconds
                        $total_duration_minutes = floor($total_duration_seconds / 60);
                        $remaining_seconds = $total_duration_seconds % 60;
                        
                        echo "<li>Total Quiz Duration: " . htmlspecialchars($total_duration_minutes) . " minutes and " . htmlspecialchars($remaining_seconds) . " seconds</li>";
                        
                        // Time limit
                        $time_limit_query = $conn->query("
                            SELECT DISTINCT time_limit
                            FROM questions
                            WHERE assessment_id = $assessment_id
                        ");
                        $time_limits = [];
                        while ($row = $time_limit_query->fetch_assoc()) {
                            $time_limits[] = $row['time_limit'];
                        }
                        if (count($time_limits) === 1) {
                            echo "<li>Time limit per question: " . htmlspecialchars($time_limits[0]) . "seconds</li>";
                        } else {
                            echo "<li>There are different time limits per question, so make sure to check the time limit in the upper right corner</li>";
                        }
                        
                        // Pointing system
                        $points_query = $conn->query("
                            SELECT DISTINCT total_points
                            FROM questions
                            WHERE assessment_id = $assessment_id
                        ");
                        $points = [];
                        while ($row = $points_query->fetch_assoc()) {
                            $points[] = $row['total_points'];
                        }
                        if (count($points) === 1) {
                            echo "<li>" . htmlspecialchars($points[0]) . "points per correct answer</li>";
                        }

                        // No points for skipped questions
                        echo "<li>No points for skipped questions</li>";
                        echo "</ul>";

                    // For Speed Mode
                    } elseif ($assessment_mode == 'Speed Mode'){
                        echo "<p>In this mode, your score is determined not just by answering correctly but also by how quickly you submit your answer.</p>";
                        echo "<ul>";
                        echo "<li>The fastest correct answers will earn 5 points, the second fastest will earn 4 points, and the third fastest will earn 3 points.</li>";
                        echo "<li>All remaining correct answers will receive 1 point.</li>";
                        echo "<li>Any incorrect answers will earn 0 points.</li>";
                        echo "</ul>";
                        echo "<p>Once you are sure of your answer, click the “Submit Answer” button on the top right corner of your screen to lock in your response. Remember, the faster you submit, the more points you can earn! Good luck and speed up to score high!</p>";
                    }
                ?>

                </div>
                
            </div>
            <div class="message">
                <h5>Get ready to show off those smarts! The <?php echo ($assessment['assessment_type'] == 1) ? 'quiz' : 'exam'; ?> will begin shortly!</h5>
            </div>
        </div>
    </div>
</body>
</html>