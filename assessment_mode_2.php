<html>
    <header>
        <style>
            .popup-overlay {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .secondary-button {
                padding: 10px 30px;
            }
        </style>
    </header>
</html>
<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id is set in URL
if (!isset($_GET['assessment_id'])) {
    header('location: load_assessments.php');
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$student_id = $_SESSION['login_id'];

// Fetch administer assessment details
$administer_query = $conn->query("
    SELECT aa.administer_id, a.max_warnings
    FROM administer_assessment aa
    JOIN assessment a ON aa.assessment_id = a.assessment_id
    WHERE aa.assessment_id = '$assessment_id'
");

// Check if there is administer assessment details
if ($administer_query->num_rows>0) {
    $administer_row = $administer_query->fetch_assoc();
    $administer_id = $administer_row['administer_id'];
    $max_warnings = $administer_row['max_warnings'];

    // Check if there is a join assessment record
    $join_query = $conn->query("
        SELECT * 
        FROM join_assessment 
        WHERE administer_id = '$administer_id' 
        AND student_id = '$student_id'
    ");

    // If there is no record yet
    if ($join_query->num_rows==0){
        // Insert the join details with the status of 1 (answering)
        $insert_join_query = $conn->query("
            INSERT INTO join_assessment (student_id, administer_id, status)
            VALUES ('$student_id', '$administer_id', 1)
        ");
        if (!$insert_join_query) {
            echo "Error inserting record: " . $conn->error;
        }
    }
}

// Fetch assessment details
$assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
$assessment = $assessment_query->fetch_assoc();
$assessment_mode = $assessment['assessment_mode'];

// Fetch questions related to the assessment
$questions_query = $conn->query("SELECT * FROM questions WHERE assessment_id = '$assessment_id'");

// Initialize questions array
$questions = [];
while ($question = $questions_query->fetch_assoc()) {
    $questions[] = $question;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessment['assessment_name']); ?> | Quilana</title>
    <?php include('header.php') ?>
    <link rel="stylesheet" href="assets/css/assessments.css">
</head>
<body>
    <?php include('nav_bar.php') ?>

    <!-- Confirmation Popup -->
    <div id="confirmation-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">You have finished the quiz!</h2>
            <p class="popup-message">SUBMIT YOUR ANSWERS NOW</p>
            <div class="popup-buttons">
                <button id="confirm" class="secondary-button" onclick="handleSubmit()">Submit</button>
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

    <!-- Maximum Warnings Reached Popup --->
    <div id="max-warnings-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">You have reached the maximum amount of warnings!</h2>
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
        <input type="hidden" id="administerId_container" value="<?php echo $administer_id;  ?>" />
        <input type="hidden" id="maxWarnings_container" value="<?php echo $max_warnings;  ?>" />

        <form id="quiz-form" action="submit_quiz.php" method="POST">
            <!-- Header with timer -->
            <div class="header-container">
                <p>Time Left: <span id="timer" class="timer">00:00</span></p>
            </div>

            <!-- Quiz form will appear here if the student hasn't already taken the assessment -->
            <div class="tabs-container">
                <ul class="tabs">
                    <li class="tab-link active" data-tab="assessment-tab"><?php echo htmlspecialchars($assessment['assessment_name']); ?></li>
                </ul>
            </div>

            <!-- Questions Container -->
            <div id="quiz-modes-container" class="questions-container">
                <?php foreach ($questions as $index => $question) : ?>
                    <div class="question" id="question-<?php echo $question['question_id']; ?>" data-time-limit="<?php echo $question['time_limit']; ?>" style="display: none;">
                    <div class="question-number">QUESTION # <?php echo $index + 1; ?></div>
                        <div class="question-text">
                            <p><strong><?php echo htmlspecialchars($question['question']); ?></strong></p>
                        </div>
                        <?php
                        $question_type = $question['ques_type'];
                        if ($question_type == 1) { // Single choice
                            echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";

                            $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                            while ($choice = $choices_query->fetch_assoc()) {
                                echo "<div class='form-check'>";
                                echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='" . htmlspecialchars($choice['option_txt']) . "' required>";
                                echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                                echo "</div>";
                            }
                        } elseif ($question_type == 2) { // Multiple choice
                            echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";

                            $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                            while ($choice = $choices_query->fetch_assoc()) {
                                echo "<div class='form-check'>";
                                echo "<input class='form-check-input' type='checkbox' name='answers[" . $question['question_id'] . "][]' value='" . htmlspecialchars($choice['option_txt']) . "'>";
                                echo "<label class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                                echo "</div>";
                            }
                        } elseif ($question_type == 3) { // True/False
                            echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";

                            echo "<div class='form-check'>";
                            echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='true' required>";
                            echo "<label class='form-check-label'>True</label>";
                            echo "</div>";
                            echo "<div class='form-check'>";
                            echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='false' required>";
                            echo "<label class='form-check-label'>False</label>";
                            echo "</div>";
                        } elseif ($question_type == 4 || $question_type == 5) { // Fill in the blank and identification
                            echo "<div class='form-check-group'>";
                            echo "<input type='text' id='quiz-modes-input' class='form-control' name='answers[" . $question['question_id'] . "]' placeholder='Type your answer here' required>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" name="assessment_id" value="<?php echo htmlspecialchars($assessment_id); ?>">
                <input type="hidden" name="assessment_mode" value="<?php echo htmlspecialchars($assessment_mode); ?>">
            </div>
        </form>

        <!-- Modal for warning for switching tabs -->
        <div id="switchtab-popup" class="popup-overlay" style="display: none;">
            <div id="switchtab-modal-content" class="popup-content">
                <span id="switchtab-modal-close" class="popup-close">&times;</span>
                <h2 id="switchtab-modal-title" class="popup-title">Warning!</h2>
                <div id="switchtab-modal-body" class="modal-body">
                    You only have <span id="attempts-display"></span> warning(s) left before disqualification.
                </div>
                <button id="confirm-warning" class="secondary-button button">OK</button>
            </div>
        </div>
    </div>

    <script>
        var timerInterval;
        var currentQuestionIndex = 0; // To track the current question
        var questions = document.querySelectorAll('.questions-container .question');
        var maxWarningReached = false; // Flag to track if maximum warnings have been reached

        // Function to show the current question
        function showQuestion(index) {
            questions.forEach((question, i) => {
                question.style.display = i === index ? 'block' : 'none';
            });
            
            // Start or restart the timer with the current question's time limit
            var timeLimit = parseInt(questions[index].getAttribute('data-time-limit'), 10);
            startTimer(timeLimit);
        }

        // Function to move to the next question
        function nextQuestion() {
            // Clear the timer before moving to the next question
            clearInterval(timerInterval);

            // Check if there are more questions
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                showQuestion(currentQuestionIndex);
            } else {
                // No more questions, show confirmation popup
                showPopup('confirmation-popup');
            }
        }

        // Timer functionality
        function startTimer(duration) {
            var timer = duration;
            var endTime = Date.now() + (timer * 1000);
            localStorage.setItem('endTime', endTime);

            // Update display immediately
            updateDisplay(timer);

            timerInterval = setInterval(function () {
                var now = Date.now();
                var remainingTime = Math.max(0, Math.floor((endTime - now) / 1000));

                if (remainingTime <= 0) {
                    clearInterval(timerInterval);
                    timerExpired = true; // Set flag to true when timer runs out
                    nextQuestion(); // Move to the next question
                    localStorage.removeItem('endTime');
                } else {
                    updateDisplay(remainingTime); // Update display
                }
            }, 1000);
        }

        // Function to update display
        function updateDisplay(remainingTime) {
            var seconds = remainingTime;
            var minutes = Math.floor(seconds / 60);
            seconds = seconds % 60;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            document.querySelector('#timer').textContent = minutes + ":" + seconds;
        }

        // When the window loads
        window.onload = function () {
            showQuestion(currentQuestionIndex); // Show the first question
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
            if (maxWarningReached) {
                closePopup('max-warnings-popup')
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
                    clearInterval(timerInterval);
                    showPopup('success-popup');
                } else {
                    showPopup('error-popup');
                }
            };
            xhr.send(formData); // Send the form data
        }

        function viewResult() {
            const assessmentId = document.querySelector('input[name="assessment_id"]').value;
            const assessmentMode = document.querySelector('input[name="assessment_mode"]').value;
            window.location.href = 'ranking.php?assessment_id=' + encodeURIComponent(assessmentId) + '&assessment_mode=' + encodeURIComponent(assessmentMode);
        }
        
        let counter = 0;
        let max_warnings = parseInt(document.getElementById('maxWarnings_container').value); 

        // Listen for the "blur" event on the window (when the tab loses focus)
        window.addEventListener("blur", () => {
            counter++;
            console.log("switch");

            const administerId = parseInt(document.getElementById('administerId_container').value);
            fetch('switchTab_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ tab_switches: counter, administer_id: administerId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if(counter == max_warnings) {
                        clearInterval(timerInterval);
                        showPopup('max-warnings-popup');
                        maxWarningReached = true;
                    } else {
                        document.getElementById("attempts-display").innerHTML = max_warnings - counter;
                        document.getElementById("switchtab-popup").style.display = "flex";
                    }
                    
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Modals
        // Close the message popup
        $('#switchtab-modal-close').click(function() {
            $('#switchtab-popup').hide();
        });

        $('#confirm-warning').click(function() {
            $('#switchtab-popup').hide();
        });
    </script>
</body>
</html>