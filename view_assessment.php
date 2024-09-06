<?php
include('header.php');
include('auth.php');
include('db_connect.php');

// Retrieve assessment ID from query parameters
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($assessment_id > 0) {
    // Fetch the assessment details and time limits based on mode
    $assessment_query = "
        SELECT a.assessment_name, a.topic, a.time_limit AS assessment_time_limit, a.assessment_mode, 
               q.question, q.ques_type, qo.option_txt, qo.is_right, qi.identification_answer, q.time_limit AS question_time_limit
        FROM assessment a
        LEFT JOIN questions q ON a.assessment_id = q.assessment_id
        LEFT JOIN question_options qo ON q.question_id = qo.question_id
        LEFT JOIN question_identifications qi ON q.question_id = qi.question_id
        WHERE a.assessment_id = ?
        ORDER BY q.order_by ASC";
    
    $stmt = $conn->prepare($assessment_query);
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $assessment_id);
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $assessment_details = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "<p>No assessment found for the given ID.</p>";
        exit;
    }
} else {
    echo "<p>Invalid assessment ID.</p>";
    exit;
}

// Calculate the overall time limit
$assessment_mode = $assessment_details[0]['assessment_mode'];
$overall_time_limit_minutes = 0;

if ($assessment_mode == 1) { // Normal Mode
    $overall_time_limit_minutes = intval($assessment_details[0]['assessment_time_limit']);
} elseif ($assessment_mode == 2 || $assessment_mode == 3) { // Quiz Bee or Speed Mode
    $total_question_time_limit = 0;
    $counted_questions = array();
    foreach ($assessment_details as $detail) {
        if (isset($detail['question_time_limit']) && !in_array($detail['question'], $counted_questions)) {
            $total_question_time_limit += intval($detail['question_time_limit']);
            $counted_questions[] = $detail['question'];
        }
    }
    $overall_time_limit_minutes = ceil($total_question_time_limit / 60);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Assessment | Quilana</title>
    <style>
        .back-arrow {
            position: absolute;
            font-size: 30px;
            top: 90px;
            font-weight: bold;
        }

        .back-arrow a {
            color: #4A4CA6;
        }

        .back-arrow a:hover {
            color: #0056b3;
        }

        .assessment-details {
            margin-top: -15px;
            margin-left: 50px;
        }

        .assessment-details h2 {
            font-weight: bold;
        }

        .assessment-details p {
            margin-bottom: 0.5em;
            font-size: 1em;
            color: #666;
        }

        .questions-container {
            max-height: 470px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 20px;
            background-color: #f9f9f9;
        }

        .question {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }

        .question-number {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .option {
            margin-left: 20px;
            margin-bottom: 5px;
        }

        .option input[type="radio"],
        .option input[type="checkbox"] {
            margin-right: 10px;
        }

        .time-limit {
            margin-top: 10px;
            font-style: italic;
            color: #333;
        }

        body {
            overflow: hidden;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="container-fluid admin">
        <div class="back-arrow">
            <a href="class_list.php?class_id=<?php echo htmlspecialchars($_GET['class_id']); ?>&show_modal=true">
                <i class="fa fa-arrow-circle-left"></i>
            </a>
        </div>

        <div class="assessment-details">
            <h2><?php echo htmlspecialchars($assessment_details[0]['assessment_name']); ?></h2>
            <p><strong>Topic:</strong> <?php echo htmlspecialchars($assessment_details[0]['topic']); ?></p>

            <?php
            // Display time limit based on assessment mode
            switch ($assessment_mode) {
                case 1:
                    echo '<p><strong>Overall Time Limit:</strong> ' . htmlspecialchars($overall_time_limit_minutes) . ' minutes (Normal Mode)</p>';
                    break;
                case 2:
                    echo '<p><strong>Overall Time Limit:</strong> ' . htmlspecialchars($overall_time_limit_minutes) . ' minutes (Quiz Bee Mode)</p>';
                    break;
                case 3:
                    echo '<p><strong>Overall Time Limit:</strong> ' . htmlspecialchars($overall_time_limit_minutes) . ' minutes (Speed Mode)</p>';
                    break;
                default:
                    echo '<p><strong>Overall Time Limit:</strong> Not specified</p>';
                    break;
            }
            ?>

            <div class="questions-container">
                <?php
                $current_question = null;
                $question_number = 1;
                foreach ($assessment_details as $detail) {
                    if ($current_question !== $detail['question']) {
                        if ($current_question !== null) {
                            echo '</div>'; // Close previous question
                        }
                        $current_question = $detail['question'];
                        echo '<div class="question">';
                        echo '<div class="question-number">Question ' . $question_number . ':</div>';
                        echo '<p>' . htmlspecialchars($current_question) . '</p>';
                        
                        // Display time limit for each question only for Quiz Bee and Speed Mode
                        if ($assessment_mode == 2 || $assessment_mode == 3) {
                            $question_time_limit = isset($detail['question_time_limit']) ? htmlspecialchars($detail['question_time_limit']) : 'Not specified';
                            echo '<div class="time-limit">Time Limit: ' . $question_time_limit . ' seconds</div>';
                        }
                        
                        $question_number++;
                    }

                    switch ($detail['ques_type']) {
                        case 1:  // Multiple Choice
                        case 2:  // Multiple Select (Checkbox)
                        case 3:  // True/False
                            $input_type = $detail['ques_type'] == 2 ? 'checkbox' : 'radio';
                            $checked_attr = $detail['is_right'] ? 'checked' : '';
                            $shaded_class = $detail['is_right'] ? 'checked' : '';
                            echo '<div class="option">';
                            echo '<label>';
                            echo '<span class="' . $shaded_class . '"><input type="' . $input_type . '" disabled ' . $checked_attr . '></span>';
                            echo htmlspecialchars($detail['option_txt']);
                            echo '</label>';
                            echo '</div>';
                            break;

                        case 4:  // Identification
                        case 5:  // Fill in the Blank
                            echo '<p><strong>Answer:</strong> ' . htmlspecialchars($detail['identification_answer']) . '</p>';
                            break;

                        default:
                            break;
                    }
                }
                if ($current_question !== null) {
                    echo '</div>'; // Close last question
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>