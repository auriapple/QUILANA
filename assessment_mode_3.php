<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id is set in URL
if (!isset($_GET['assessment_id'])) {
    header('location: waiting_room.php');
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <button id="confirm" class="secondary-button" onclick="submitAnswer()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Final Confirmation Popup -->
    <div id="final-confirmation-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">You have finished the quiz!</h2>
            <p class="popup-message">SUBMIT YOUR ANSWERS NOW</p>
            <div class="popup-buttons">
                <button id="submit-answers" class="secondary-button" onclick="finalSubmit()">Submit</button>
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

        <form id="quiz-form" action="submit_assessment.php" method="POST">
            <!-- Header with stopwatch and submit button -->
            <div class="header-container">
                <p>Time Elapsed: <span id="stopwatch" class="timer">00:00</span></p>
                <button type="button" onclick="showPopup(currentQuestionIndex < questions.length - 1 ? 'confirmation-popup' : 'final-confirmation-popup')" id="submit" class="secondary-button">Submit</button>
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
                    <div class="question" id="question-<?php echo $question['question_id']; ?>" style="display: none;">
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

    <script>
        let stopwatchInterval;
        let elapsedTime = 0;
        let isPaused = false; // Tracks whether the timer is paused
        const questions = document.querySelectorAll('.questions-container .question');
        let currentQuestionIndex = 0;
        var maxWarningReached = false; // Flag to track if maximum warnings have been reached

        let questionTimes = Array(questions.length).fill(0);

        function showQuestion(index) {
            questions.forEach((question, i) => {
                question.style.display = i === index ? 'block' : 'none';
            });
            if (index === 0) startStopwatch();
        }

        function startStopwatch() {
            clearInterval(stopwatchInterval);
            elapsedTime = 0;
            stopwatchInterval = setInterval(() => {
                if (!isPaused) { // Only update if not paused
                    elapsedTime += 100;
                    updateStopwatchDisplay();
                }
            }, 100);
        }

        function updateStopwatchDisplay() {
            const minutes = Math.floor((elapsedTime % 3600000) / 60000);
            const seconds = Math.floor((elapsedTime % 60000) / 1000);
            document.getElementById('stopwatch').textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function nextQuestion() {
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                showQuestion(currentQuestionIndex);
                startStopwatch();
            } else {
                showPopup('final-confirmation-popup');
            }
        }

        function submitAnswer() {
            questionTimes[currentQuestionIndex] = elapsedTime;
            console.log(`Question ${currentQuestionIndex} time: ${questionTimes[currentQuestionIndex]} ms`);
            closePopup('confirmation-popup');
            nextQuestion();
        }

        function finalSubmit() {
            questionTimes[currentQuestionIndex] = elapsedTime;
            console.log(`Question ${currentQuestionIndex} time: ${questionTimes[currentQuestionIndex]} ms`);
            closePopup('final-confirmation-popup');
            submitForm();
        }

        function submitForm() {
            const formData = new FormData(document.getElementById('quiz-form'));

            formData.append('time_elapsed', JSON.stringify(questionTimes));

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_assessment.php', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    clearInterval(stopwatchInterval);
                    showPopup('success-popup');
                } else {
                    alert('Error submitting your answers. Please try again.');
                }
            };
            xhr.send(formData);
        }

        function showPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
            if (popupId === 'confirmation-popup') {
                isPaused = true; // Set paused state
            }
        }
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            if (popupId === 'confirmation-popup') {
                isPaused = false; // Resume updating
            }
        }

        // When the window loads
        window.onload = function () {
            showQuestion(currentQuestionIndex); // Show the first question
            startStopwatch();
        };

        function viewResult() {
            const assessmentId = document.querySelector('input[name="assessment_id"]').value;
            const assessmentMode = document.querySelector('input[name="assessment_mode"]').value;
            window.location.href = 'ranking.php?assessment_id=' + encodeURIComponent(assessmentId) + '&assessment_mode=' + encodeURIComponent(assessmentMode);
        }

        function handleSubmit() {
            submitForm();
        }
        
        let tabSwitched = false; // tracker for switching tab
        let counter = 0;
        let max_warnings = parseInt(document.getElementById('maxWarnings_container').value); 

        // Listen for the "blur" event on the window (when the tab loses focus)
        window.addEventListener("blur", () => {
            counter++;
            console.log("switch");
            tabSwitched = true;

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
                        maxWarningReached = true;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Show warning for focus event on the window (when the tab regains focus after being blurred)
        window.addEventListener("focus", () => {
            if (tabSwitched) {
                tabSwitched = false;
                if (counter >= max_warnings) {
                    maxWarningReached = true;
                    Swal.fire({
                        //title: 'Warning!',
                        title: 'napakagaling!',
                        //text: 'You have reached the maximum amount of warnings!',
                        text: 'at inulit-ulit mo pa talagang pasaway ka',
                        icon: 'warning',
                        confirmButtonText: 'i-submit mo na yan!!!',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'popup-content',
                            confirmButton: 'secondary-button'
                        }
                        
                    }).then((result) => {
                        if (result.isConfirmed) {
                            handleSubmit();
                        }
                    });
                } else {
                    Swal.fire({
                        //title: 'Warning!',
                        title: 'Hoi huli ka boi akala mo ha!',
                        //text: 'You only have ' + (max_warnings - counter) + ' warning/s left before disqualification.',
                        text: 'nako kang bata ka, sige isa pa at makikita mo hinahanap mo',
                        icon: 'warning',
                        confirmButtonText: 'sorry po di na mauulit >_< ',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'popup-content',
                            confirmButton: 'secondary-button'
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>