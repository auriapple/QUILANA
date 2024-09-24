
<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id is set in URL
if (!isset($_GET['assessment_id'])) {
    header('location: quiz.php');
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);

// Fetch assessment details
$assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
$assessment = $assessment_query->fetch_assoc();

// Fetch questions related to the assessment
$questions_query = $conn->query("SELECT * FROM questions WHERE assessment_id = '$assessment_id'");

// Get the time limit for the assessment
$time_limit = $assessment['time_limit'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessment['assessment_name']); ?> | Quilana</title>
    <?php include('header.php') ?>
</head>
<body>
    <?php include('nav_bar.php') ?>
    <!-- Confirmation Popup -->
    <div id="confirmation-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <button class="popup-close" onclick="closePopup('confirmation-popup')">&times;</button>
            <h2 class="popup-title">Are you sure you want to submit your answers?</h2>
            <p class="popup-message">THIS ACTION CANNOT BE UNDONE</p>
            <div class="popup-buttons">
                <button id="cancel" class="secondary-button" onclick="closePopup('confirmation-popup')">Cancel</button>
                <button id="confirm" class="secondary-button" onclick="handleSubmit()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="success-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <button class="popup-close" onclick="closeSuccessPopup('success-popup')">&times;</button>
            <h2 class="popup-title">Your answers have been submitted and recorded successfully!</h2>
            <div class="popup-buttons">
                <button id="result" class="secondary-button" onclick="viewResult()">View Result</button>
            </div>
        </div>
    </div>

    <!-- Timer Run Out Popup -->
    <div id="timer-runout-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">The timer ran out! You must submit your answers now!</p>
            <button id="submit-answers" class="secondary-button" onclick="handleSubmit()">Submit</button>
        </div>
    </div>

    <!-- Error Popup -->
    <div id="error-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">An error occurred while submitting the form. Please try again.</h2>
            <div class="popup-buttons">
                <button id="error" class="secondary-button" onclick="closeErrorPopup('error-popup')">Try Again</button>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <form id="quiz-form" action="submit_quiz.php" method="POST">
            <!-- Header with submit button and timer -->
                <div class="header-container">
                    <p>Time Left: <span id="timer" class="timer"><?php echo htmlspecialchars($time_limit); ?>:00</span></p>
                    <button type="button" onclick="showPopup('confirmation-popup')" id="submit" class="secondary-button">Submit</button>
                </div>

            <!-- Quiz form will appear here if the student hasn't already taken the assessment -->
            <div class="tabs-container">
                <ul class="tabs">
                    <li class="tab-link active" data-tab="assessment-tab"><?php echo htmlspecialchars($assessment['assessment_name']); ?></li>
                </ul>
            </div>

            <!-- Questions Container -->
            <div class="questions-container">
                <?php
                // Initialize question counter to 1
                $question_number = 1;
                while ($question = $questions_query->fetch_assoc()) {
                    echo "<div class='question'>";
                    echo "<p><strong>$question_number. " . htmlspecialchars($question['question']) . "</strong></p>";

                    // Handle input types based on question type
                    $question_type = $question['ques_type'];

                    // Single choice (radio buttons)
                    if ($question_type == 1) {
                        $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                        while ($choice = $choices_query->fetch_assoc()) {
                            echo "<div class='form-check'>";
                            echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='" . htmlspecialchars($choice['option_txt']) . "' required>";
                            echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                            echo "</div>";
                        }
                    // Multiple choice (checkboxes)
                    } elseif ($question_type == 2) {
                        $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                        while ($choice = $choices_query->fetch_assoc()) {
                            echo "<div class='form-check'>";
                            echo "<input class='form-check-input' type='checkbox' name='answers[" . $question['question_id'] . "][]' value='" . htmlspecialchars($choice['option_txt']) . "'>";
                            echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                            echo "</div>";
                        }
                    // True/False (radio buttons)
                    } elseif ($question_type == 3) {
                        echo "<div class='form-check'>";
                        echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='true' required>";
                        echo "<label class='form-check-label'>True</label>";
                        echo "</div>";
                        echo "<div class='form-check'>";
                        echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='false' required>";
                        echo "<label class='form-check-label'>False</label>";
                        echo "</div>";
                    // Fill in the blank and identification (text input)
                    } elseif ($question_type == 4 || $question_type == 5) {
                        echo "<div class='form-check-group'>";
                        echo "<input type='text' class='form-control' name='answers[" . $question['question_id'] . "]' placeholder='Type your answer here' required>";
                        echo "</div>";
                    }
                    echo "</div>";
                    $question_number++;
                }
                ?>
                <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
                <input type="hidden" name="time_limit" value="<?php echo $time_limit; ?>">
            </div>
        </form>
    </div>

    <script>
        var timerInterval;
        var timerExpired = false; // Flag to track if timer has expired

        // Timer functionality
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;

            // Get stored end time
            var storedEndTime = localStorage.getItem('endTime');
            if (storedEndTime) {
                var now = Date.now();
                timer = Math.max(0, Math.floor((storedEndTime - now) / 1000));
            } else {
                var endTime = Date.now() + (timer * 1000);
                localStorage.setItem('endTime', endTime);
            }

            updateDisplay(timer, display); // Initialize display immediately

            timerInterval = setInterval(function () {
                var now = Date.now();
                var remainingTime = Math.max(0, Math.floor((localStorage.getItem('endTime') - now) / 1000));

                if (remainingTime <= 0) {
                    clearInterval(timerInterval);
                    timerExpired = true; // Set flag to true when timer runs out
                    showPopup('timer-runout-popup');
                    localStorage.removeItem('endTime');
                } else {
                    updateDisplay(remainingTime, display);
                    localStorage.setItem('remainingTime', remainingTime); // Update the stored remaining time
                }
            }, 1000);
        }

        // Function to update display
        function updateDisplay(remainingTime, display) {
            var minutes = Math.floor(remainingTime / 60);
            var seconds = remainingTime % 60;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            display.textContent = minutes + ":" + seconds;
        }

        // When the window loads
        window.onload = function () {
            var timeLimit = parseInt(document.querySelector('input[name="time_limit"]').value, 10) * 60,
                display = document.querySelector('#timer');

            startTimer(timeLimit, display);
        };

        // Handles Popups
        function showPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }
        function closeSuccessPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            window.location.href = 'enroll.php#assessments-tab';
        }
        function closeErrorPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            handleSubmit();
        }

        function handleSubmit() {
            if (timerExpired) {
                closePopup('timer-runout-popup');
                submitForm();
            } else {
                closePopup('confirmation-popup');
                submitForm();
            }
        }

        /* Handles Form Submission */
        function submitForm() {
            // Create a new FormData object from the form
            var formData = new FormData(document.getElementById('quiz-form'));

            // Create an XMLHttpRequest object
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_quiz.php', true);

            // Set up a handler for when the request completes
            xhr.onload = function () {
                if (xhr.status === 200) {
                    localStorage.removeItem('endTime');
                    localStorage.removeItem('remainingTime');
                    clearInterval(timerInterval);
                    showPopup('success-popup');
                } else {
                    showPopup('error-popup');
                }
            };
            xhr.send(formData); // Send the form data
        }

        function viewResult() {
            window.location.href = 'results.php'; // Redirect to results page
        }
    </script>
</body>
</html>