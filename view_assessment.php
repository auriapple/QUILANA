<?php
include('header.php');
include('auth.php');
include('db_connect.php');

if (isset($_GET['id'])) {
    $assessment_id = intval($_GET['id']);

    // Fetch the assessment details
    $assessment_query = "
        SELECT a.assessment_name, a.topic, aa.timelimit, q.question, q.ques_type, qo.option_txt, qo.is_right, qi.identification_answer 
        FROM assessment a
        JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
        LEFT JOIN questions q ON a.assessment_id = q.assessment_id
        LEFT JOIN question_options qo ON q.question_id = qo.question_id
        LEFT JOIN question_identifications qi ON q.question_id = qi.question_id
        WHERE a.assessment_id = $assessment_id
        ORDER BY q.order_by ASC";
    
    $result = $conn->query($assessment_query);
    
    if ($result->num_rows > 0) {
        $assessment_details = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "<p>No assessment found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid assessment ID.</p>";
    exit;
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

        .correct-answer {
            color: green;
            font-weight: bold;
        }

        .option {
            margin-left: 20px;
            margin-bottom: 5px;
        }

        .option input[type="radio"],
        .option input[type="checkbox"] {
            margin-right: 10px;
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
        <a href="class_list.php?class_id=<?php echo $_GET['class_id']; ?>&show_modal=true">
        <i class="fa fa-arrow-circle-left"></i>
            </a>
        </div>

        <div class="assessment-details">
            <h2><?php echo htmlspecialchars($assessment_details[0]['assessment_name']); ?></h2>
            <p><strong>Topic:</strong> <?php echo htmlspecialchars($assessment_details[0]['topic']); ?></p>
            <p><strong>Time Limit:</strong> <?php echo htmlspecialchars($assessment_details[0]['timelimit']); ?> minutes</p>

            <div class="questions-container">
                <?php
                $current_question = null;
                $question_number = 1; // Initialize question number
                foreach ($assessment_details as $detail) {
                    if ($current_question !== $detail['question']) {
                        if ($current_question !== null) {
                            echo '</div>';
                        }
                        $current_question = $detail['question'];
                        echo '<div class="question">';
                        echo '<div class="question-number">Question ' . $question_number . ':</div>';
                        echo '<p>' . htmlspecialchars($current_question) . '</p>';
                        $question_number++; // Increment question number
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
                            // Handle other question types if needed
                            break;
                    }
                }
                if ($current_question !== null) {
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
