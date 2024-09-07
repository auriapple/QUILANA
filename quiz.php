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
    </style>
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="container-fluid admin">
        <h2><?php echo htmlspecialchars($assessment['assessment_name']); ?></h2>
        <p>Topic: <?php echo htmlspecialchars($assessment['topic']); ?></p>
        <p>Time Limit: <span id="timer" class="timer"><?php echo htmlspecialchars($time_limit); ?>:00</span></p>
        
        <form id="quiz-form" action="submit_quiz.php" method="POST">
            <?php
            $question_number = 1;
            while ($question = $questions_query->fetch_assoc()) {
                echo "<div class='question'>";
                echo "<p><strong>Question $question_number:</strong> " . htmlspecialchars($question['question']) . "</p>";

                // Fetch and display choices
                $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                while ($choice = $choices_query->fetch_assoc()) {
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='" . $choice['option_id'] . "' required>";
                    echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                    echo "</div>";
                }
                echo "</div>";
                $question_number++;
            }
            ?>
            <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
            <input type="hidden" name="time_limit" value="<?php echo $time_limit; ?>">
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
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
