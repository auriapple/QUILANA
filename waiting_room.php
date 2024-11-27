<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id, student_id, and administer_id are set in URL
if (!isset($_GET['assessment_id']) && !isset($_GET['student_id']) && !isset($_GET['administer_id'])) {
    header('location: load_assessments.php');
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$student_id = $conn->real_escape_string($_GET['student_id']);
$administer_id = $conn->real_escape_string($_GET['administer_id']);

// Fetch assessment details
$assessment_query = $conn->query("
    SELECT * 
    FROM assessment 
    WHERE assessment_id = '$assessment_id'
");

if ($assessment_query->num_rows > 0) {
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
        SELECT status
        FROM administer_assessment
        WHERE administer_id = '$administer_id'
    ");

    // Check if the assessment have an administer record
    if ($administer_query && $administer_query->num_rows > 0) {
        $administer_data = $administer_query->fetch_assoc();
        $status = $administer_data['status'];

        // Check if there is a join assessment record
        $join_query = $conn->query("
            SELECT * 
            FROM join_assessment 
            WHERE administer_id = '$administer_id' 
            AND student_id = '$student_id'
        ");

        // If there is no record yet
        if ($join_query->num_rows == 0) {
            // Check if the assessment has not yet started
            if ($status == 0) {
                // Insert the join details with the status of 0 (joined)
                $insert_join_query = $conn->query("
                    INSERT INTO join_assessment (student_id, administer_id, status)
                    VALUES ('$student_id', '$administer_id', 0)
                ");
                if (!$insert_join_query) {
                    echo "Error inserting record: " . $conn->error;
                    exit();
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
                    WHERE administer_id = '$administer_id' 
                    AND student_id = '$student_id'
                ");
                if (!$update_status_query) {
                    echo "Error updating record: " . $conn->error;
                    exit();
                }

                // Set the redirection based on the assessment mode
                $redirect_url = '';
                if ($assessment['assessment_mode'] == 1){
                    $redirect_url = 'assessment_mode_1.php';
                } elseif ($assessment['assessment_mode'] == 2){
                    $redirect_url = 'assessment_mode_2.php';
                } elseif ($assessment['assessment_mode'] == 3){
                    $redirect_url = 'assessment_mode_3.php';
                }

                // Redirect to the correct assessment page
                header("Location: $redirect_url?assessment_id=" . urlencode($assessment_id) . "&administer_id=" . urlencode($administer_id));
                exit();
            }
        }
    } else {
        echo "Error: Assessment hasn't been administered yet.";
        exit();
    }
} else {
    header('location: load_assessments.php');
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
        /* Overall Assessment Details */
        .assessment-details {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            justify-content: center;
            height: 85vh;
        }

        /* General Assessment Details */
        .general-details {
            justify-content: center;
            margin-top: 50px;
            height: 120px;
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
            margin: 0;
        }
        .general-details h4 {
            font-size: 24px;
            text-align: center;
            color: #1E1A43;
            margin-top: 15px;
            margin-bottom: 0;
        }

        /* Fade Style */
        .fade-top {
            height: 50px;
            background: linear-gradient(to bottom, rgba(249, 249, 249, 0.9), rgba(249, 249, 249, 0));
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 2;
        }

        /* Assessment Instructions */
        .instructions {
            display: flex;
            flex-direction: column;
            position: relative;
            width: 75%;
            height: auto;
            border: 3px solid #6A7AC7;
            border-radius: 25px;
            margin: 20px auto;
            flex: 1;
            overflow: hidden;
        }
        .content {
            padding: 50px;
            overflow-y: auto;
            height: 100%;
        }
        .content::-webkit-scrollbar {
            display: none;
        }

        /* Instructions */
        .instructions h3 {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            color: #1E1A43;
            margin-bottom: 0;
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
            margin-bottom: 0;
        }
        .instruction-text ul {
            max-width: 65%;
            margin-bottom: 0;
        }
        .instruction-text li {
            color: #4A4A4A;
            font-size: 18px;
        }

        /* Reminders */
        .reminders {
            margin-top: 20px;
        }
        .reminder-text {
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }
        .numbered-list {
            margin-bottom: 0;
            list-style-type: none;
            counter-reset: list-counter;
            text-align: justify;
            padding-left: 0;
            max-width: 75%;
            align-items: center;
        }
        .numbered-list li {
            counter-increment: list-counter;
            font-size: 18px;
            color: #4A4A4A;
        }
        .numbered-list li::before {
            content: counter(list-counter) ". ";
            font-weight: bold;
        }

        /* Fade Style */
        .fade-bottom {
            height: 50px;
            background: linear-gradient(to top, rgba(249, 249, 249, 0.9), rgba(249, 249, 249, 0));
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 2;
        }

        /* Message */
        .message {
            height: 65px;
        }
        .message h5 {
            font-size: 24px;
            font-weight: bold;
            justify-content: center;
            text-align: center;
            color: #6A7AC7;
        }

        /* Text Styles */
        .reminder-text em {
            color: #A9A9A9;
        }
        .reminder-text strong {
            color: #e00f0f;
        }

        /* Media Responsiveness */
        @media screen and (max-width: 768px) {
            .general-details {
                margin-top: 25px;
            }
            .general-details h1 {
                font-size: 30px;
            }
            .general-details h3,
            .instructions h3,
            .reminders h3 {
                font-size: 26px;
            }
            .message h5 {
                font-size: 20px;
            }
            .general-details h4 {
                margin-top: 5px;
                font-size: 18px;
            }
            .instructions {
                margin: 15px;
                width: 95%;
            }
            .content {
                padding: 20px;
            }
            .instruction-text ul {
                max-width: 85%;
            }
            .instruction-text li,
            .instruction-text p,
            .numbered-list li {
                font-size: 16px;
            }
            .fade-top, .fade-bottom {
                height: 50px;
            }
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
                <div class="fade-top"></div>
                <div class="content">
                    <h3>Instructions</h3>
                    <div class="instruction-text">
                        <?php
                        // For Normal Mode
                        if ($assessment_mode == 'Normal Mode') {
                            echo "<ul>";
                            
                            // Total Duration
                            echo "<li><strong>Total " . (($assessment['assessment_type'] == 1) ? 'Quiz' : 'Exam') . " Duration: </strong>" . htmlspecialchars($assessment['time_limit']) . " minutes</li>";
                            
                            // Number of questions
                            $question_query = $conn->query("
                                SELECT COUNT(DISTINCT question_id) AS question_count
                                FROM questions
                                WHERE assessment_id = '$assessment_id'
                            ");
                            $question_data = $question_query->fetch_assoc();

                            // Total Questions
                            echo "<li><strong>Total Questions: </strong>" . htmlspecialchars($question_data['question_count']) . "</li>";

                            // Pointing system
                            $points_query = $conn->query("
                                SELECT DISTINCT total_points
                                FROM questions
                                WHERE assessment_id = $assessment_id
                            ");

                            // Check the points per question
                            $points = [];
                            while ($row = $points_query->fetch_assoc()) {
                                $points[] = $row['total_points'];
                            }

                            // Display points if the assessment have the same points per question
                            if (count($points) === 1) {
                                echo "<li><strong>" . htmlspecialchars($points[0]) . " point/s</strong> per correct answer</li>";
                            }

                            // Passing rate
                            echo "<li> <strong>Passing Rate:</strong> ". htmlspecialchars($assessment['passing_rate']) ."%</li>";
                            echo "</ul>";

                        // For Quiz Bee Mode
                        } elseif ($assessment_mode == 'Quiz Bee Mode'){
                            echo "<ul>";

                            // Total Quiz Duration (sum of all question time limits)
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
                            
                            echo "<li><strong>Total Quiz Duration:</strong> " . htmlspecialchars($total_duration_minutes) . " minutes and " . htmlspecialchars($remaining_seconds) . " seconds</li>";
                            
                            // Time limit
                            $time_limit_query = $conn->query("
                                SELECT DISTINCT time_limit
                                FROM questions
                                WHERE assessment_id = $assessment_id
                            ");

                            // Check the time limit per question
                            $time_limits = [];
                            while ($row = $time_limit_query->fetch_assoc()) {
                                $time_limits[] = $row['time_limit'];
                            }

                            if (count($time_limits) === 1) {
                                echo "<li><strong>Time limit per question:</strong> " . htmlspecialchars($time_limits[0]) . " seconds</li>";
                            } else {
                                echo "<li>There are different time limits per question, so make sure to check the time limit in the upper right corner</li>";
                            }

                            // Number of questions
                            $question_query = $conn->query("
                                SELECT COUNT(DISTINCT question_id) AS question_count
                                FROM questions
                                WHERE assessment_id = '$assessment_id'
                            ");
                            $question_data = $question_query->fetch_assoc();

                            // Total Duration
                            echo "<li><strong>Total Questions:</strong> " . htmlspecialchars($question_data['question_count']) . "</li>";
                            
                            // Pointing system
                            $points_query = $conn->query("
                                SELECT DISTINCT total_points
                                FROM questions
                                WHERE assessment_id = $assessment_id
                            ");

                            // Check the points per question
                            $points = [];
                            while ($row = $points_query->fetch_assoc()) {
                                $points[] = $row['total_points'];
                            }

                            // Display points if the assessment have the same points for all question
                            if (count($points) === 1) {
                                echo "<li><strong>" . htmlspecialchars($points[0]) . " point/s</strong> per correct answer</li>";
                            }

                            // Passing rate
                            echo "<li><strong>Passing Rate:</strong> ". htmlspecialchars($assessment['passing_rate']) ."%</li>";
                            echo "</ul>";

                        // For Speed Mode
                        } elseif ($assessment_mode == 'Speed Mode'){
                            // Opening instructions
                            echo "<p>In this mode, your score is determined not just by answering correctly but also by how quickly you submit your answer.</p>";
                            
                            echo "<ul>";

                            // Number of questions
                            $question_query = $conn->query("
                                SELECT COUNT(DISTINCT question_id) AS question_count
                                FROM questions
                                WHERE assessment_id = '$assessment_id'
                            ");
                            $question_data = $question_query->fetch_assoc();

                            // Total Duration
                            echo "<li><strong>Total Questions:</strong> " . htmlspecialchars($question_data['question_count']) . "</li>";

                            // Student count and pointing system
                            if ($assessment['student_count'] == 1) {
                                echo "<li>The <strong>first student</strong> to submit correct answers will earn <strong>" . htmlspecialchars($assessment['max_points']) ." point/s</strong>.</li>"; 
                            } else {
                            echo "<li>The <strong>first " . htmlspecialchars($assessment['student_count']) ." students</strong> to submit correct answers will earn <strong>" . htmlspecialchars($assessment['max_points']) ." point/s</strong>.</li>"; 
                            }

                            echo "<li>All remaining correct answers will receive <strong>" . htmlspecialchars($assessment['remaining_points']) . " point/s</strong>.</li>";
                            
                            echo "</ul>";

                            // Closing instructions
                            echo "<p>Once you are sure of your answer, click the “<strong>Submit</strong>” button on the top right corner of your screen to lock in your response. Remember, the faster you submit, the more points you can earn! <br><strong><em>Good luck and speed up to score high!</em></strong></p>";
                        }
                        ?>
                    </div>
                    <h3 class="reminders" >Reminders</h3>
                    <div class="reminder-text">
                        <ul class="numbered-list">
                            <li>Please do not <em>leave the assessment page, switch tabs, or attempt to record any assessment details</em>, as this may result in <strong>disqualification</strong>.</li>
                            <li>Avoid <em>refreshing the page</em>, as this may cause you to <strong>lose your attempted answers</strong>.</li>
                            <?php
                            if ($assessment_mode == 'Normal Mode') {
                                echo "<li>If you do not finish the test within the given time limit, your attempted answers will be <strong>automatically submitted</strong>.</li>";
                            } elseif ($assessment_mode == 'Quiz Bee Mode' || $assessment_mode == 'Speed Mode') {
                                echo "<li><strong>You cannot skip any questions</strong>; you must answer the current question to proceed to the next one.</li>";
                                if ($assessment_mode == 'Quiz Bee Mode') {
                                    echo "<li>If you fail to submit your answer before the question time limit ends, your attempted answer will be <strong>automatically submitted</strong>, regardless of whether you have provided an answer or not.</li>";
                                }
                            }
                            ?>
                        </ul>
                    </div>

                </div>
                <div class="fade-bottom"></div>
            </div>
            <div class="message">
                <h5>Get ready to show off those smarts! The <?php echo ($assessment['assessment_type'] == 1) ? 'quiz' : 'exam'; ?> will begin shortly!</h5>
            </div>
        </div>
    </div>
    <script>
        function check_status() {
            const administerId = "<?php echo $administer_id; ?>"; 

            fetch(`check_status.php?administer_id=${administerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    
                    const currentStatus = data.status;
                    console.log('Current Status:', currentStatus);
                    
                    // Reload page if the status is 1
                    if (currentStatus == 1) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error fetching status:', error));
        }

        // Call check_status every 3 seconds
        setInterval(check_status, 3000);
    </script>
</body>
</html>