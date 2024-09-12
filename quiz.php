<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id is set in URL
if (!isset($_GET['assessment_id'])) {
    header('location: quiz.php');
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$student_id = $_SESSION['login_id'];

// Check if the student has already submitted this assessment (status 1 or 2)
$submission_check_query = $conn->query("SELECT * FROM student_submission 
                                        WHERE assessment_id = '$assessment_id' 
                                        AND student_id = '$student_id' 
                                        AND status IN (1, 2)");

if ($submission_check_query->num_rows > 0) {
    // The student has already taken the assessment, display a message
    $message = "You have already taken this assessment. You cannot take it again.";
} else {
    // Fetch assessment details
    $assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
    $assessment = $assessment_query->fetch_assoc();

    // Fetch questions related to the assessment
    $questions_query = $conn->query("SELECT * FROM questions WHERE assessment_id = '$assessment_id'");

    // Get the time limit for the quiz
    $time_limit = $assessment['time_limit'];
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
        /* Popup Styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }
        .popup-content {
            background: #fff;
            padding-left: 45px;
            padding-right:45px;
            padding-top: 60px;
            border-radius: 25px;
            width: 500px;
            height: 300px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }
        .popup-title {
            font-weight: bold;
            color: #1E1A43;
        }
        .popup-message {
            margin: 10px 0;
            font-size: 14px;
            color: #4a4a4a;
        }
        /* Popup Buttons */
        .popup-buttons button {
            background-image: linear-gradient(to right, #8794F2, #6E72C1);
            background-color: #4A4CA6;
            margin-top: 10px;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 60px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .popup-buttons-confirm, 
        .popup-buttons-cancel {
            margin-left: 10px;
            margin-right: 10px;
        }
        .popup-buttons-confirm:hover,
        .popup-buttons-cancel:hover,
        .popup-buttons-results:hover {
            background-color: #4A4CA6;
            background-image: none;
            cursor: pointer;
            box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.25);
        }
        .popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #6a6a6a;
        }

        /* Timer Style */
        .timer {
            font-size: 1.0em;
            color: red;
            font-weight: bold;
        }

        /* Question Styles */
        .question {
            margin-bottom: 10px;
            color: #1E1A43;
        }
        .question p {
            margin: 0px;
        }

        /* Options and Answer Boxes Styles */
        .form-check {
            margin-top: 2px;
            margin-bottom: 2px;
            padding-left: 35px;
            color: #8F8F9D;
        }
        .form-group {
            margin-top: 2px;
            margin-bottom: 2px;
            padding-left: 15px;
        }
        .form-group input[type="text"] {
            color: rgba(143, 143, 157, 0.75);
            border-color: #8F8F9D;
        }
        .form-group input[type="text"]:focus {
            color: rgba(143, 143, 157, 0.75);
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
            color: #787878;
        }

        /* Button and Timer Container */
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

        /* Tabs Container */
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

        /* Questions Container */
        .questions-container {
            background-color: #FFFFFF;
            border: 1px solid #F8F9FA;
            border-radius: 8px;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.25);
            overflow: auto;
            width: calc(100% - 20px);
            height: calc(100vh - 250px);
            margin: 10px;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Submit Button */
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
    <!-- Confirmation Popup -->
    <div id="confirmation-popup" class="popup-overlay">
        <div class="popup-content">
            <button class="popup-close" onclick="closePopup('confirmation-popup')">&times;</button>
            <h2 class="popup-title">Are you sure you want to submit your answers?</h2>
            <p class="popup-message">THIS ACTION CANNOT BE UNDONE</p>
            <div class="popup-buttons">
                <button class="popup-buttons-cancel" onclick="closePopup('confirmation-popup')">Cancel</button>
                <button class="popup-buttons-confirm" onclick="submitForm()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="success-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <button class="popup-close" onclick="closeSuccessPopup('success-popup')">&times;</button>
            <h2 class="popup-title">Your answers have been submitted and recorded successfully!</h2>
            <div class="popup-buttons">
                <button class="popup-buttons-result" onclick="viewResult()">View Result</button>
            </div>
        </div>
    </div>

    <div class="container-fluid admin">
        <?php if (isset($message)): ?>
            <div class="alert alert-danger">
                <strong><?php echo $message; ?></strong>
            </div>
            <a href="results.php?assessment_id=<?php echo $assessment_id; ?>" class="btn btn-primary">View Results</a>
        <?php else: ?>
            <!-- Quiz form will appear here if the student hasn't already taken the assessment -->
            <div class="tabs-container">
                <ul class="tabs">
                    <li class="tab-link active" data-tab="assessment-tab"><?php echo htmlspecialchars($assessment['assessment_name']); ?></li>
                </ul>
            </div>

            <div class="questions-container">
                <form id="quiz-form" action="submit_quiz.php" method="POST">
                    <!-- Timer and Submit Button -->
                    <div class="header-container">
                        <p>Time Left: <span id="timer" class="timer"><?php echo htmlspecialchars($time_limit); ?>:00</span></p>
                        <button type="button" onclick="showPopup('confirmation-popup')" class="btn btn-primary btn-sm submit">Submit</button>
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
                                echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='" . htmlspecialchars($choice['option_txt']) . "' required>";
                                echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                                echo "</div>";
                            }
                        } elseif ($question_type == 2) { // Multiple choice (checkboxes)
                            $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                            while ($choice = $choices_query->fetch_assoc()) {
                                echo "<div class='form-check'>";
                                echo "<input class='form-check-input' type='checkbox' name='answers[" . $question['question_id'] . "][]' value='" . htmlspecialchars($choice['option_txt']) . "'>";
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
                    <input type="hidden" name="time_limit" value="<?php echo $time_limit; ?>">
                </form>
            </div>
        <?php endif; ?>
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

        /* Handles Popups */
        function showPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        } 
        function closeSuccessPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            window.location.href = 'enroll.php';
        }

        /* Handles Form Submission */
        function submitForm() {
            // Create a new FormData object from the form
            var formData = new FormData(document.getElementById('quiz-form'));
            
            // Close the confirmation popup
            closePopup('confirmation-popup');
            
            // Create an XMLHttpRequest object
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_quiz.php', true);

            // Set up a handler for when the request completes
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Show the success popup
                    showPopup('success-popup');
                } else {
                    alert('An error occurred while submitting the form.');
                }
            };

            // Send the form data
            xhr.send(formData);
        }

        function viewResult() {
            // Redirect to result page or handle result viewing
            window.location.href = 'results.php';
        }
        window.onload = function () {
        var timeLimit = parseInt(document.querySelector('input[name="time_limit"]').value, 10) * 60,
            display = document.querySelector('#timer');
        
        // Fetch the submitted status from the server
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_submission.php?assessment_id=<?php echo $assessment_id; ?>', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                if (xhr.responseText === 'submitted') {
                    window.location.href = 'results.php?assessment_id=<?php echo $assessment_id; ?>';
                } else {
                    startTimer(timeLimit, display);
                }
            } else {
                alert('An error occurred while checking submission status.');
            }
        };
        xhr.send();
    };

    </script>
</body>
</html>