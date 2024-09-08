<?php
include('db_connect.php');
include('auth.php');

if (!isset($_GET['assessment_id'])) {
    header('location: enroll.php');
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);

// Fetch assessment details
$assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
$assessment = $assessment_query->fetch_assoc();

// Fetch questions related to the assessment
$questions_query = $conn->query("SELECT * FROM questions WHERE assessment_id = '$assessment_id'");

// Get the time limit for the quiz
$time_limit = $assessment['time_limit'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessment['assessment_name']); ?> | Quilana</title>
    <?php include('header.php') ?>
    <style>
        .timer {
            font-size: 1.0em;
            color: red;
            font-weight: bold;
        }
        .question {
            margin-bottom: 10px; /* Space between questions */
            color: #1E1A43;
        }

        .question p {
            margin: 0px; /* Remove margin from question text to reduce space above options */
        }

        /* Style for options and answer boxes */
        .form-check {
            margin-top: 2px;
            margin-bottom: 2px; /* Space between options */
            padding-left: 35px; /* Indent options */
            color: #8F8F9D; /* Light grey color for options */
        }

        .form-group {
            margin-top: 2px;
            margin-bottom: 2px;
            padding-left: 15px;
        }

        /* Style for input fields */
        .form-group input[type="text"] {
            color: rgba(143, 143, 157, 0.75); /* 75% opacity for placeholder text */
            border-color: #8F8F9D; /* Light grey border for text input */
        }

        .form-group input[type="text"]:focus {
            color: rgba(143, 143, 157, 0.75); /* 75% opacity for placeholder text */
            border-color: #4A4CA6;
            box-shadow: 0 0 5px rgba(74, 76, 166, 0.5);
            outline: none;
        }

        .form-check input[type="radio"],
        .form-check input[type="checkbox"] {
            accent-color: #4A4CA6;
        }

        /* Style for selected options or typed answers */
        .form-check input:checked + label,
        .form-check input:focus + label,
        .form-group input[type="text"]:valid {
            color: #787878; /* Gray color when selected or typed */
        }
        
        /* ahhhhhhh */
        /* Button and timer container */
        .header-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 10px;
            position: relative;
        }

        .header-container p {
            margin: 0 20px 0 0;
        }

        .tabs-container {
            margin-bottom: 20px;
        }

        .tabs {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            border-bottom: 3px solid #ddd;
        }

        .tabs .tab-link {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: bold;
        }

        .tabs .tab-link.active {
            border-bottom: 3px solid #5a5ada;
            color: #5a5ada;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .questions-container {
            background-color: #FFFFFF;
            border: 1px solid #F8F9FA;
            border-radius: 8px;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.25);
            overflow: auto; /* Add scrollbars if content overflows */
            width: calc(100% - 20px); /* Adjust width based on padding and margin */
            height: calc(100vh - 250px); /* Adjust height based on header and other spacing */
            margin: 10px;
            padding: 20px;
            box-sizing: border-box;
        }
        .submit {
            background-image: linear-gradient(to right, #8794F2, #6E72C1);
            background-color: #4A4CA6;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 45px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: none;
            z-index: 2;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .submit:hover {
            background-color: #4A4CA6;
            background-image: none;
            cursor: pointer;
            box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="container-fluid admin">
        <!-- Tabs for assessment -->
        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="assessment-tab"><?php echo htmlspecialchars($assessment['assessment_name']); ?></li>
            </ul>
        </div>

        <!-- Questions and options -->
        <div class="questions-container">
            <form id="quiz-form" action="submit_quiz.php" method="POST">
                <!-- Header with submit button and timer -->
                <div class="header-container">
                    <p>Time Left: <span id="timer" class="timer"><?php echo htmlspecialchars($time_limit); ?>:00</span></p>
                    <button type="submit" class="btn btn-primary btn-sm submit">Submit</button>
                </div>
                <?php
                $question_number = 1;
                while ($question = $questions_query->fetch_assoc()) {
                    echo "<div class='question'>";
                    echo "<p><strong>$question_number. " . htmlspecialchars($question['question']) . "</strong></p>";

                    // Handle input types based on question type
                    $question_type = $question['ques_type'];

                    if ($question_type == 1) { // Single choice (radio buttons)
                        $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                        while ($choice = $choices_query->fetch_assoc()) {
                            echo "<div class='form-check'>";
                            echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='" . $choice['option_id'] . "' required>";
                            echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                            echo "</div>";
                        }
                    } elseif ($question_type == 2) { // Multiple choice (checkboxes)
                        $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                        while ($choice = $choices_query->fetch_assoc()) {
                            echo "<div class='form-check'>";
                            echo "<input class='form-check-input' type='checkbox' name='answers[" . $question['question_id'] . "][]' value='" . $choice['option_id'] . "'>";
                            echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                            echo "</div>";
                        }
                    } elseif ($question_type == 3) { // True/False (radio buttons)
                        echo "<div class='form-check'>";
                        echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='true' required>";
                        echo "<label class='form-check-label'>True</label>";
                        echo "</div>";
                        echo "<div class='form-check'>";
                        echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='false' required>";
                        echo "<label class='form-check-label'>False</label>";
                        echo "</div>";
                    } elseif ($question_type == 4 || $question_type == 5) { // Fill in the blank and identification (text input)
                        echo "<div class='form-group'>";
                        echo "<input type='text' class='form-control' name='answers[" . $question['question_id'] . "]' placeholder='Type your answer here' required>";
                        echo "</div>";
                    }

                    echo "</div>";
                    $question_number++;
                }
                ?>
                <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
                <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
                <input type="hidden" name="time_limit" value="<?php echo $time_limit; ?>">
            </form>
        </div>
    </div>

    <script>
        // Timer functionality
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    timer = 0;
                    document.getElementById('quiz-form').submit(); // Submit the form when the time is up
                }
            }, 1000);
        }

        window.onload = function () {
            var timeLimit = parseInt(document.querySelector('input[name="time_limit"]').value, 10) * 60,
                display = document.querySelector('#timer');
            startTimer(timeLimit, display);
        };
    </script>
</body>
</html>
