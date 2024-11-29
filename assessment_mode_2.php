<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id is set in URL
if (!isset($_GET['assessment_id']) && !isset($_GET['administer_id'])) {
    header('location: load_assessments.php');
    exit();
}

$student_id = $_SESSION['login_id'];
$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$administer_id = $conn->real_escape_string($_GET['administer_id']);

// Fetch administer assessment details
$administer_query = $conn->query("
    SELECT max_warnings
    FROM assessment
    WHERE assessment_id = '$assessment_id'
");

// Check if there is administer assessment details
if ($administer_query->num_rows>0) {
    $administer_row = $administer_query->fetch_assoc();
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
            INSERT INTO join_assessment (student_id, administer_id, status, attempts)
            VALUES ('$student_id', '$administer_id', 1, 1)
        ");

        $attempts = 1;
        $is_submitted = false;

        if (!$insert_join_query) {
            echo "Error inserting record: " . $conn->error;
        }
    } else {
        $join_details = $join_query->fetch_assoc();
        
        if ($join_details['status'] == 2) {
            $attempts = $join_details['attempts'];
            $is_submitted = true;
        } else {
            $is_submitted = false;
            if ($join_details['attempts'] < 3) {
                // Update the join_assessment status to 1 (answering)
                $update_join_query = $conn->query("
                    UPDATE join_assessment 
                    SET 
                        status = 1,
                        attempts = attempts + 1
                    WHERE administer_id = '$administer_id' 
                    AND student_id = '$student_id'
                ");

                $attempts = $join_details['attempts'] + 1;

                if (!$update_join_query) {
                    echo "Error updating record: " . $conn->error;
                }
            } else {
                $attempts = $join_details['attempts'] + 1;
            } 
        }
    }
}

// Fetch assessment details
$assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
$assessment = $assessment_query->fetch_assoc();
$assessment_mode = $assessment['assessment_mode'];

// Check order of questions (normal or randomized)
$random = $assessment['randomize_questions'];

// Fetch questions related to the assessment
$questions_query = $conn->query("
    SELECT * 
    FROM questions 
    WHERE assessment_id = '$assessment_id' 
    ORDER BY
        CASE
            WHEN $random = 1 then RAND()
            ELSE 0
        END;
");

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
        <div class = "main-container">
            <input type="hidden" id="maxWarnings_container" value="<?php echo $max_warnings;  ?>" />

            <form id="quiz-form" action="submit_assessment.php" method="POST">
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

                                echo "<div class='option-buttons'>";
                                $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                                while ($choice = $choices_query->fetch_assoc()) {
                                    echo "<div class='form-check'>";
                                    echo "<input id='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='" . htmlspecialchars($choice['option_txt']) . "' required>";
                                    echo "<label for='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                                    echo "</div>";
                                }
                                echo "</div>";
                            } elseif ($question_type == 2) { // Multiple choice
                                echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";

                                echo "<div class='option-buttons'>";
                                $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                                while ($choice = $choices_query->fetch_assoc()) {
                                    echo "<div class='form-check'>";
                                    echo "<input id='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-input' type='checkbox' name='answers[" . $question['question_id'] . "][]' value='" . htmlspecialchars($choice['option_txt']) . "'>";
                                    echo "<label for='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                                    echo "</div>";
                                }
                                echo "</div>";
                            } elseif ($question_type == 3) { // True/False
                                echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";

                                echo "<div class='option-buttons'>";
                                    echo "<div class='form-check'>";
                                    echo "<input id='true_" . htmlspecialchars($question['question_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='true' required>";
                                    echo "<label for='true_" . htmlspecialchars($question['question_id']) . "' class='form-check-label'>True</label>";
                                    echo "</div>";
                                    echo "<div class='form-check'>";
                                    echo "<input id='false_" . htmlspecialchars($question['question_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='false' required>";
                                    echo "<label for='false_" . htmlspecialchars($question['question_id']) . "' class='form-check-label'>False</label>";
                                    echo "</div>";
                                echo "</div>";
                            } elseif ($question_type == 4 || $question_type == 5) { // Fill in the blank and identification
                                echo "<div class='form-check-group'>";
                                echo "<input type='text' id='answer_" . htmlspecialchars($question['question_id']) . "' class='quiz-modes-input' name='answers[" . $question['question_id'] . "]' placeholder='Type your answer here' required>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                    <input type="hidden" name="assessment_id" value="<?php echo htmlspecialchars($assessment_id); ?>">
                    <input type="hidden" name="assessment_mode" value="<?php echo htmlspecialchars($assessment_mode); ?>">
                    <input type="hidden" id="administerId_container" name="administer_id" value="<?php echo htmlspecialchars($administer_id);  ?>" />
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global variables
        var timerInterval;
        var currentQuestionIndex = 0; // To track the current question
        var questions = document.querySelectorAll('.questions-container .question');
        const assessmentId = document.querySelector('input[name="assessment_id"]').value;
        let warningCount = parseInt(sessionStorage.getItem(`warningCount_${assessmentId}`)) || 0;
        const max_warnings = parseInt(document.getElementById('maxWarnings_container').value);
        let altKeyPressed = false;
        let winKeyPressed = false;
        let ctrlKeyPressed = false;
        let warningTracker = false;

        // Handle number of attempts warning
        var attempts = <?php echo $attempts; ?>;
        var is_submitted = <?php echo json_encode($is_submitted); ?>;

        if (is_submitted === true) {
            Swal.fire({
                title: 'Assessment Already Submitted!',
                text: 'You have already answered and submitted your answers for this assessment.',
                icon: 'error',
                showConfirmButton: false,
                allowOutsideClick: false,
                customClass: {
                    popup: 'popup-content'
                },
                timer: 5000,
                timerProgressBar: true
            }).then(() => {
                window.location.href = 'results.php';
            });
        } else {
            if (attempts > 3) {
                Swal.fire({
                    title: 'Maximum Attempts Reached!',
                    text: 'You have already reached the maximum number of attempts for this assessment.',
                    icon: 'error',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'popup-content',
                    },
                    timer: 5000,
                    timerProgressBar: true
                }).then(() => {
                    handleSubmit();
                    warningTracker = false;
                });
            } else if (attempts === 3) {
                Swal.fire({
                    title: 'This is your last attempt!',
                    text: 'You can no longer answer this assessment after this attempt.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'popup-content',
                        confirmButton: 'secondary-button'
                    }
                }).then(() => {
                    warningTracker = false;
                    showQuestion(currentQuestionIndex);
                });
            } else {
                var attemptText = '';
                var attemptTitle = '';

                if (attempts === 1) {
                    attemptTitle = 'First Attempt';
                    attemptText = 'You are about to make your first attempt. Remember, you can only attempt to answer this assessment 3 times.';
                } else if (attempts === 2) {
                    attemptTitle = 'Second Attempt';
                    attemptText = 'This is your second attempt. You have one more chance to answer this assessment.';
                }

                Swal.fire({
                    title: attemptTitle,
                    text: attemptText,
                    icon: 'info',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'popup-content',
                        confirmButton: 'secondary-button'
                    }
                }).then(() => {
                    warningTracker = false;
                    showQuestion(currentQuestionIndex);
                });
            } 
        }

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
            sessionStorage.setItem(`endTime_${assessmentId}`, endTime);

            // Update display immediately
            updateDisplay(timer);

            timerInterval = setInterval(function () {
                var now = Date.now();
                var remainingTime = Math.max(0, Math.floor((endTime - now) / 1000));

                if (remainingTime <= 0) {
                    clearInterval(timerInterval);
                    timerExpired = true; // Set flag to true when timer runs out
                    nextQuestion(); // Move to the next question
                    sessionStorage.removeItem('endTime');
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

        // Handles Popups
        function showPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }
        function closeErrorPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            handleSubmit();
        }


        // FORM SUBMISSION HANDLING
        function handleSubmit() {
            const administerId = parseInt(document.getElementById('administerId_container').value);
            console.log('Checking submission status for administer_id:', administerId);
            
            $.ajax({
                url: 'check_submission.php',
                method: 'GET',
                data: { administer_id: administerId},
                dataType: 'json',
                success: function(response) {
                    console.log("AJAX success response:", response);     
                    if (response.status === 'submitted') {
                        Swal.fire({
                            title: 'Assessment Already Submitted!',
                            text: 'You have submitted your answers to this assessment already',
                            icon: 'error',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                            },
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = 'results.php';
                        });
                    } else {
                        console.log("Form not submitted yet, proceeding with form submission");
                        submitForm();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('checking of submission failed:', error);
                }
            });
        }

        function submitForm() {
            var formData = new FormData(document.getElementById('quiz-form'));
            formData.append('warningCount', warningCount);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_assessment.php', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    sessionStorage.removeItem('endTime');
                    sessionStorage.removeItem('remainingTime');
                    sessionStorage.removeItem('warningCount');
                    clearInterval(timerInterval);
                    showPopup('success-popup');
                } else {
                    showPopup('error-popup');
                }
            };
            xhr.send(formData);

            // Close any open popups
            closePopup('confirmation-popup');
        }

        // SUSPICIOUS ACTIVITIES HANDLING
       // Warning system
       function handleWarning(method) {
            warningCount++;
            if (warningCount > max_warnings) {
                warningCount = max_warnings;
            }
           
            sessionStorage.setItem(`warningCount_${assessmentId}`, warningCount);
            console.log(`Warning triggered via ${method}. Total warnings: ${warningCount}`);

            temporarilyHideOverlay();

            const administerId = parseInt(document.getElementById('administerId_container').value);

            fetch('switchTab_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    suspicious_act: warningCount, 
                    administer_id: administerId,
                    method: method
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.new_count !== undefined) {
                        warningCount = data.new_count;
                        sessionStorage.setItem(`warningCount_${assessmentId}`, warningCount);
                    }
                    if (warningCount >= max_warnings) {
                        clearInterval(timerInterval);
                        Swal.fire({
                            title: 'Maximum Warnings Reached!',
                            text: 'Your assessment will be submitted automatically.',
                            icon: 'error',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                            },
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            handleSubmit();
                            warningTracker = false;
                        });
                        return;
                    }
                    Swal.fire({
                        title: 'Warning!',
                        text: `${method} attempt detected. You have ${max_warnings - warningCount} warnings left.`,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'popup-content',
                            confirmButton: 'secondary-button'
                        }
                    }).then(() => {
                        warningTracker = false;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // USER VISUAL EXPERIENCE
        // Displays random small markers on the entire screen to deter screen capture attempts
        function setupAntiScreenshotOverlay() {
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'transparent';
            overlay.style.zIndex = '9999';
            overlay.style.pointerEvents = 'none';
            document.body.appendChild(overlay);

            setInterval(() => {
                const marker = document.createElement('div');
                marker.style.position = 'absolute';
                marker.style.width = '5px';
                marker.style.height = '5px';
                marker.style.backgroundColor = 'rgba(0,0,0,0.1)';
                marker.style.top = Math.random() * 100 + '%';
                marker.style.left = Math.random() * 100 + '%';
                overlay.appendChild(marker);
                setTimeout(() => marker.remove(), 500);
            }, 100);
        }

        // Trigger flash effect when a screen capture attempt is detected
        function flashScreen() {
            const flash = document.createElement('div');
            flash.style.position = 'fixed';
            flash.style.top = '0';
            flash.style.left = '0';
            flash.style.width = '100%';
            flash.style.height = '100%';
            flash.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
            flash.style.zIndex = '10000';
            flash.style.opacity = '0';
            document.body.appendChild(flash);

            // Animate the flash effect
            flash.animate([
                { opacity: '0' },
                { opacity: '1' },
                { opacity: '0' }
            ], {
                duration: 300,
                easing: 'ease-in-out',
                fill: 'forwards'
            });

            setTimeout(() => flash.remove(), 300);
        }

        // Black screen overlay
        const blackScreen = document.createElement('div');
        blackScreen.style.position = 'fixed';
        blackScreen.style.top = '0';
        blackScreen.style.left = '0';
        blackScreen.style.width = '100%';
        blackScreen.style.height = '100%';
        blackScreen.style.backgroundColor = 'black';
        blackScreen.style.zIndex = '10000';
        blackScreen.style.display = 'none';
        document.body.appendChild(blackScreen);

        function showBlackScreen() {
            blackScreen.style.display = 'block';
            setTimeout(() => {
                blackScreen.style.display = 'none';
            }, 2000);
        }

        // Screen capture detection
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'transparent';
        overlay.style.pointerEvents = 'none';
        overlay.style.zIndex = '9999';
        document.body.appendChild(overlay);

        function temporarilyHideOverlay() {
            overlay.style.display = 'none';
            setTimeout(() => {
                overlay.style.display = 'block';
            }, 1000);
        }

        // EVENT LISTENERS FOR VARIOUS KEYBOARD SHORTCUTS
        document.addEventListener('keydown', (e) => {
            // const restrictedKeys = ['F12'];
            if (e.key === 'Alt') altKeyPressed = true;
            if (e.key === 'Meta' || e.key === 'Win' || e.key === 'Windows') {
                winKeyPressed = true;
                showBlackScreen();
            }
            if (e.ctrlKey) {
                ctrlKeyPressed = true;
                showBlackScreen();
            }

            //Screen Capture
            if ((winKeyPressed && e.shiftKey && ['3', '4', '5'].includes(e.key)) ||
                (winKeyPressed && (e.shiftKey || e.key === 'S')) ||
                (winKeyPressed && e.key === 'g')) {
                e.preventDefault();
                e.stopPropagation();
                flashScreen();
                handleWarning('Screen capture');
                warningTracker = true;
                return;      
            }

            // Restricted Key
            if (restrictedKeys.includes(e.key)) {
                e.preventDefault();
                e.stopPropagation();
                flashScreen();
                handleWarning('Restricted key use');
                warningTracker = true;
                return; 
            }
            
            // Screen Record
            if (winKeyPressed && (altKeyPressed || e.key === 'r')) {        
                e.preventDefault();
                e.stopPropagation();
                flashScreen();
                handleWarning('Screen recording');
                warningTracker = true;
                return;
            }

            // Print Event
            if (ctrlKeyPressed && e.key === 'p') {
                e.preventDefault();
                e.stopPropagation();
                flashScreen();
                handleWarning('Print event');
                warningTracker = true;
                return;
            }

            // Save Event
            if (ctrlKeyPressed && (e.key === 'S' || (e.shiftKey && e.key === 'S'))) {
                e.preventDefault();
                e.stopPropagation();
                flashScreen();
                handleWarning('File saving');
                warningTracker = true;
                return;
            }
        }, true);

        // Reset key state when released
        document.addEventListener('keyup', (e) => {
            if (e.key === 'Alt') altKeyPressed = false;
            if (e.key === 'Meta' || e.key === 'Win' || e.key === 'Windows') winKeyPressed = false;
            if (e.ctrlKey) ctrlKeyPressed = false;
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                e.stopPropagation();
                showBlackScreen();
                flashScreen(); 
                handleWarning('Screen capture');
                warningTracker = true;  
                return false;
            }
        }, true);

        // Event listeners for various capture methods
        window.addEventListener('beforeprint', (e) => {
            e.preventDefault();
            showBlackScreen();
            flashScreen();
            handleWarning('Print event');
            warningTracker = true;
        });

        // TAB SWITCHING DETECTION
        window.addEventListener("focus", () => {
            if (!warningTracker) {
                handleWarning('Tab switching');
            } 
        });

        // ADDITIONAL SECURITY MEASURES
        // Disables right-click, text selection, and copying of content
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('selectstart', event => event.preventDefault());
        document.addEventListener('copy', event => event.preventDefault());

        // Browser screenshot detection
        window.addEventListener('screenshot', (e) => {
            e.preventDefault();
            flashScreen();
            handleWarning('Screen capture');
            warningTracker = true;
        });

        // Mobile phone screenshot andriod detection (three fingers)
        let touchCount = 0;

        document.addEventListener('touchstart', function(event) {
            touchCount = event.touches.length;
            
            if (touchCount === 3) {
                handleWarning('Screenshot');
                warningTracker = true;
            }
        }, true)

        // Initialize timer and set up event listeners
        window.onload = function () {
            document.getElementById('quiz-form').addEventListener('submit', handleSubmit);

            setupAntiScreenshotOverlay();
        };

        function viewResult() {
            const assessmentMode = document.querySelector('input[name="assessment_mode"]').value;
            const administerId = document.getElementById('administerId_container').value;
            window.location.href = 'ranking.php?assessment_id=' + encodeURIComponent(assessmentId) + '&assessment_mode=' + encodeURIComponent(assessmentMode) + '&administer_id=' + encodeURIComponent(administerId);
        }
    </script>
</body>
</html>