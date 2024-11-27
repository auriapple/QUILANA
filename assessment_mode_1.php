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
    SELECT a.max_warnings, aa.start_time
    FROM administer_assessment aa
    JOIN assessment a ON aa.assessment_id = a.assessment_id
    WHERE aa.administer_id = '$administer_id'
");

// Check if there is administer assessment details
if ($administer_query->num_rows>0) {
    $administer_row = $administer_query->fetch_assoc();
    $max_warnings = $administer_row['max_warnings'];
    $start_time = $administer_row['start_time'];

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

        if (!$insert_join_query) {
            echo "Error inserting record: " . $conn->error;
        }
    } else {
        $join_details = $join_query->fetch_assoc();
        
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

// Fetch assessment details
$assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
$assessment = $assessment_query->fetch_assoc();

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
    <link rel="stylesheet" href="assets/css/assessments.css">
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
            <button id="submit-answers" class="secondary-button" onclick="submitForm()">Submit</button>
        </div>
    </div>

    <!-- Error Popup -->
    <div id="error-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">An error occurred while submitting the form. Please try again.</h2>
            <div class="popup-buttons">
                <button id="error" class="secondary-button" onclick="closeErrorPopup('error-popup')">OK</button>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class = "main-container">
            <input type="hidden" id="maxWarnings_container" value="<?php echo $max_warnings;  ?>" />
            
            <form id="quiz-form" action="submit_assessment.php" method="POST">
                <!-- Header with submit button and timer showPopup('confirmation-popup') onclick="submit()" -->
                <div class="header-container">
                    <p>Time Left: <span id="timer" class="timer">Loading...</span></p>
                    <button type="button" id="submit" class="secondary-button">Submit</button>
                </div>

                <!-- Quiz form will appear here if the student hasn't already taken the assessment -->
                <div class="tabs-container">
                    <ul class="tabs">
                        <li class="tab-link active" data-tab="assessment-tab"><?php echo htmlspecialchars($assessment['assessment_name']); ?></li>
                    </ul>
                </div>

                <!-- Questions Container -->
                <div class="questions-container1">
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
                            echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";

                            $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                            while ($choice = $choices_query->fetch_assoc()) {
                                echo "<div class='form-check'>";
                                echo "<input id='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='" . htmlspecialchars($choice['option_txt']) . "' required>";
                                echo "<label for='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                                echo "</div>";
                            }
                        // Multiple choice (checkboxes)
                        } elseif ($question_type == 2) {
                            echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";

                            $choices_query = $conn->query("SELECT * FROM question_options WHERE question_id = '" . $question['question_id'] . "'");
                            while ($choice = $choices_query->fetch_assoc()) {
                                echo "<div class='form-check'>";
                                echo "<input id='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-input' type='checkbox' name='answers[" . $question['question_id'] . "][]' value='" . htmlspecialchars($choice['option_txt']) . "'>";
                                echo "<label for='option_" . htmlspecialchars($choice['option_id']) . "' class='form-check-label'>" . htmlspecialchars($choice['option_txt']) . "</label>";
                                echo "</div>";
                            }
                        // True/False (radio buttons)
                        } elseif ($question_type == 3) {
                            echo "<input type='hidden' name='answers[" . $question['question_id'] . "]' value=''>";
                            
                            echo "<div class='form-check'>";
                            echo "<input id='true_" . htmlspecialchars($question['question_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='true' required>";
                            echo "<label for='true_" . htmlspecialchars($question['question_id']) . "' class='form-check-label'>True</label>";
                            echo "</div>";
                            echo "<div class='form-check'>";
                            echo "<input id='false_" . htmlspecialchars($question['question_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='false' required>";
                            echo "<label for='false_" . htmlspecialchars($question['question_id']) . "' class='form-check-label'>False</label>";
                            echo "</div>";
                        // Fill in the blank and identification (text input)
                        } elseif ($question_type == 4 || $question_type == 5) {
                            echo "<div class='form-check-group'>";
                            echo "<input type='text' id='answer_" . htmlspecialchars($question['question_id']) . "' class='form-control' name='answers[" . $question['question_id'] . "]' placeholder='Type your answer here' required>";
                            echo "</div>";
                        }
                        echo "</div>";
                        $question_number++;
                    }
                    ?>
                    <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
                    <input type="hidden" id="time_limit" name="time_limit" value="<?php echo $time_limit; ?>">
                    <input type="hidden" id="administerId_container" name="administer_id" value="<?php echo htmlspecialchars($administer_id);  ?>" />
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global variables
        let timerInterval;
        const assessmentId = document.querySelector('input[name="assessment_id"]').value;
        let warningCount = parseInt(sessionStorage.getItem(`warningCount_${assessmentId}`)) || 0;
        let isSubmitting = false;
        let hasSubmitted = false;
        const max_warnings = parseInt(document.getElementById('maxWarnings_container').value);
        let altKeyPressed = false;
        let winKeyPressed = false;
        let ctrlKeyPressed = false;
        let warningTracker = false;

        // Handle number of attempts warning
        var attempts = <?php echo $attempts; ?>;

        if (attempts > 3) {
            Swal.fire({
                title: 'Maximum Attempts Reached!',
                text: 'You have already reached the maximum number of attempts for this assessment.',
                icon: 'error',
                confirmButtonText: 'OK',
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
        } else if (attempts === 3) {
            Swal.fire({
                title: 'This is your last attempt!',
                text: 'This is your final chance to answer this assessment. You can no longer answer this assessment after this attempt.',
                icon: 'warning',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                customClass: {
                    popup: 'popup-content',
                    confirmButton: 'secondary-button'
                }
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
            });
        }

        // POPUP HANDLING
        function showPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }
        function closeErrorPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            isSubmitting = false;
        }

        document.getElementById('submit').addEventListener('click', function() {
            // Check if all questions are answered
            const allAnswered = checkAllAnswered();

            if (allAnswered === 1) {
                showPopup('confirmation-popup');
            } else {
                Swal.fire({
                    title: 'You cannot submit yet!',
                    text: 'Please answer all questions first.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'popup-content',
                        confirmButton: 'secondary-button'
                    }
                });
            }
        });
        

        // FORM SUBMISSION HANDLING
        function handleSubmit(event) {
            if (event) {
                event.preventDefault();
            }
            if (isSubmitting || hasSubmitted) return; // Prevent multiple submissions
            isSubmitting = true;

            submitForm();
        }

        function submitForm() {
            if (hasSubmitted) return; 

            var formData = new FormData(document.getElementById('quiz-form'));
            formData.append('warningCount', warningCount);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_assessment.php', true);

            xhr.onload = function () {
                isSubmitting = false;
                hasSubmitted = true; // Mark as submitted
                if (xhr.status === 200) {
                    sessionStorage.removeItem(`remainingTime_${assessmentId}`);
                    sessionStorage.removeItem(`warningCount_${assessmentId}`);
                    clearInterval(timerInterval);
                    showPopup('success-popup');
                } else {
                    showPopup('error-popup');
                }
            };
            xhr.send(formData);

            // Close any open popups
            closePopup('timer-runout-popup');
            closePopup('confirmation-popup');
        }

        // UNANSWERED ITEMS HANDLING
        // Select all question containers
        const questions = document.querySelectorAll('.question');
        const navContainer = document.createElement('div');
        navContainer.id = 'question-nav-container';
        document.body.appendChild(navContainer);
        let currentStartIndex = 0;
        const maxVisibleBoxes = 10;
        let seenQuestions = new Set();
        let unansweredQuestions = new Set();

        // Function to update navigation boxes (show all questions with different styles for answered and unanswered)
        function updateNavBoxes() {
            // Clear existing boxes 
            document.querySelectorAll('.question-nav-box').forEach(box => box.remove());

            // Create navigation boxes for all questions (answered or unanswered)
            questions.forEach((question, index) => {
                const navBox = document.createElement('div');
                navBox.className = 'question-nav-box';
                navBox.textContent = index + 1;
                navBox.dataset.index = index;

                // Check if the question is answered
                if (!isQuestionAnswered(question)) {
                    navBox.classList.add('unanswered'); // Add 'unanswered' class if not answered
                }

                // Scroll to the question when clicked
                navBox.onclick = () => {
                    question.scrollIntoView({
                        behavior: 'smooth',
                        block: 'end',
                    });
                };

                // Append the nav box to the container
                navContainer.appendChild(navBox);
            });
        }

        // Function to check if all questions are answered
        function checkAllAnswered() {
            let allAnswered = true;

            // Check each question to see if it's answered
            questions.forEach((question) => {
                if (!isQuestionAnswered(question)) {
                    allAnswered = false;
                }
            });

            if (allAnswered) {
                console.log("All questions have been answered!");
                return 1;
            } else {
                console.log("Some questions are still unanswered.");
                return 0;
            }
        }

        // Function to check if a question is answered
        function isQuestionAnswered(question) {
            const radios = question.querySelectorAll('input[type="radio"]');
            const checkboxes = question.querySelectorAll('input[type="checkbox"]');
            const textInput = question.querySelector('input[type="text"]');
            const isRadioAnswered = radios.length > 0 && Array.from(radios).some(radio => radio.checked);
            const isCheckboxAnswered = checkboxes.length > 0 && Array.from(checkboxes).some(checkbox => checkbox.checked);
            const isTextAnswered = textInput && textInput.value.trim() !== '';
            return isRadioAnswered || isCheckboxAnswered || isTextAnswered;
        }

        // IntersectionObserver to track seen questions (for lazy loading and marking questions as seen)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const index = Array.from(questions).indexOf(entry.target);
                if (entry.isIntersecting) {
                    seenQuestions.add(index);
                    // Update the unanswered questions set
                    if (!isQuestionAnswered(entry.target)) {
                        unansweredQuestions.add(index);
                    } else {
                        unansweredQuestions.delete(index);
                    }
                    updateNavBoxes();
                }
            });
        }, { threshold: 0.5 });

        // Observe all questions
        questions.forEach(question => observer.observe(question));

        // Listen for input changes to update answered state
        document.getElementById('quiz-form').addEventListener('input', () => {
            questions.forEach((question, index) => {
                if (seenQuestions.has(index)) {
                    if (isQuestionAnswered(question)) {
                        unansweredQuestions.delete(index);
                    } else {
                        unansweredQuestions.add(index);
                    }
                }
            });
            updateNavBoxes();
        });

        // Initial rendering of the navigation boxes
        updateNavBoxes();

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
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                                confirmButton: 'secondary-button'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                handleSubmit();
                            }
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
            const restrictedKeys = ['F12'];
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
        });

        // Mobile phone screenshot andriod detection (three fingers)
        let touchCount = 0;

        document.addEventListener('touchstart', function(event) {
            touchCount = event.touches.length;
            
            if (touchCount === 3) {
                handleWarning('Screenshot');
            }
        }, true);

        // Set Timer Functionality and Event Listeners
        window.onload = function() {
            const maxTimeLimit = parseInt(document.getElementById('time_limit').value) * 60; // Convert minutes to seconds
            const startTime = Date.parse("<?php echo $start_time; ?> GMT+0800");
            //const startTime = new Date("<?php echo $start_time; ?> GMT+0800").getTime();

            function calculateRemainingTime() {
                const now = Date.now();
                const elapsedTime = Math.floor((now - startTime) / 1000); // Calculate elapsed time in seconds
                const remainingTime = Math.max(0, maxTimeLimit - elapsedTime); // Calculate remaining time
                return remainingTime;
            }

            function startTimer() {
                const remainingTime = calculateRemainingTime();
                updateDisplay(remainingTime); // Update display immediately

                const timerInterval = setInterval(function () {
                    const newRemainingTime = calculateRemainingTime();

                    if (newRemainingTime <= 0) {
                        clearInterval(timerInterval);
                        showPopup('timer-runout-popup');
                    } else {
                        updateDisplay(newRemainingTime);
                    }
                }, 1000);
            }

            function updateDisplay(remainingTime) {
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                document.getElementById('timer').textContent = `${minutes < 10 ? '0' + minutes : minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            }

            startTimer(); // Start the timer
            
            document.getElementById('quiz-form').addEventListener('submit', handleSubmit);

            setupAntiScreenshotOverlay();
        };

        function viewResult() {
            window.location.href = 'results.php';
        }
    </script>
</body>
</html>