<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

// Fetch scheduled assessments
$today = date('Y-m-d');
$scheduleQuery = $conn->query("SELECT date_scheduled FROM schedule_assessments WHERE date_scheduled >= '$today' AND faculty_id = '".$_SESSION['login_id']."'");
$schedules = [];
while ($row = $scheduleQuery->fetch_assoc()) {
    $schedules[] = $row['date_scheduled'];
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include('header.php') ?>
        <title>Dashboard | Quilana</title>
        <link rel="stylesheet" href="assets/css/faculty-dashboard.css">
        <link rel="stylesheet" href="assets/css/calendar.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,200,0,200" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="assets/js/calendar.js" defer></script>
    </head>
    <body>
        <?php include 'nav_bar.php'; ?>
        <div class="content-wrapper dashboard-container">

            <!-- Dashboard Summary -->
            <div class="dashboard-summary">
                <h1> Welcome, <?php echo $firstname ?> </h1>
                <h2> Summary </h2>
                <div class="cards"> 
                    <div class="card" style="background-color: #ffe2e5;">
                        <img class="icons" src="image/DashboardCoursesIcon.png" alt="Courses Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalCourses FROM course 
                                                WHERE faculty_id = '".$_SESSION['login_id']."'");
                        $resTotalCourses = $result->fetch_assoc();
                        $totalCourses = $resTotalCourses['totalCourses'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalCourses ?> </h3>
                            <label>Total Courses</label> 
                        </div>
                    </div>
                    <div class="card" style="background-color: #FADEFF"> 
                        <img class="icons" src="image/DashboardClassesIcon.png" alt="Classes Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalClasses FROM class
                                                WHERE faculty_id = '".$_SESSION['login_id']."'");
                        $resTotalClasses = $result->fetch_assoc();
                        $totalClasses = $resTotalClasses['totalClasses'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalClasses ?> </h3>
                            <label>Total Classes</label> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Requests -->
            <div class="dashboard-requests">
                <h1> Pending Requests </h1>
                <div id="pending-requests" class="requests">
                    <!-- Requests will be loaded here -->
                </div>
            </div>

            <!-- Dashboard Calendar -->
            <div class="dashboard-calendar">
                <div class="wrapper">
                    <!-- Calendar -->
                    <header>
                        <div class="icons">
                            <span id="prev" class="material-symbols-rounded">chevron_left</span>
                        </div>
                        <p class="current-date"></p>
                        <div class="icons">
                            <span id="next" class="material-symbols-rounded">chevron_right</span>
                        </div>
                    </header>
                    <div class="calendar">
                        <ul class="weeks">
                        <li>Sun</li>
                        <li>Mon</li>
                        <li>Tue</li>
                        <li>Wed</li>
                        <li>Thu</li>
                        <li>Fri</li>
                        <li>Sat</li>
                        </ul>
                        <ul class="days"></ul>
                    </div>
                    <!-- Today's Schedule -->
                    <footer>
                        <div class="line"></div>
                        <h1>Today</h1>
                        <div class="today-schedule">
                            <?php
                            // Fetch today's date
                            $today = date('Y-m-d');

                            // Fetch today's scheduled assessment details
                            $todayAssessments = $conn->query("
                                SELECT a.assessment_name, c.class_name, a.subject
                                FROM assessment a
                                JOIN schedule_assessments sa ON a.assessment_id = sa.assessment_id
                                JOIN class c ON sa.class_id = c.class_id
                                WHERE sa.faculty_id = '".$_SESSION['login_id']."' AND sa.date_scheduled = '$today'
                            ");

                            // Count scheduled assessments today
                            $todayCount = $todayAssessments->num_rows;

                            // Check if there is/are assessment/s scheduled today
                            if ($todayCount > 0) {
                                // Display details if there is only one record
                                if ($todayCount === 1) {
                                    $row = $todayAssessments->fetch_assoc();
                                    echo "<div class='schedule-item'>";
                                    echo "<h3>" . htmlspecialchars($row['assessment_name']) . "</h3>";
                                    echo "<p>" . htmlspecialchars($row['class_name']) . " (" . htmlspecialchars($row['subject']) . ")</p>";
                                    echo "</div>";
                                // If there are more than one assessment
                                } else {
                                    echo "<p class='no-records'>You have $todayCount assessments scheduled today.</p>";
                                }
                            } else {
                                echo "<p class='no-records'>No assessments scheduled today</p>";
                            }
                            ?>
                        </div>
                        <div class="add-button">
                            <!-- Schedule Assessment Button -->
                            <button class="add-schedule">
                                <i class="fa fa-plus"></i>
                            </button>       
                        </div> 
                    </footer>
                </div>
            </div>

            <!-- Scheduled Assessments -->
            <div class="dashboard-schedule">
                <div class="schedule-label">
                    <h1>Upcomming Assessments</h1>
                    <img class="icons" src="image/Calendar.png" alt="Calendar Icon">  
                </div>
                <div id="schedules" class="schedules">
                    <?php 
                    // Fetch all scheduled assessments details
                    $assessment = $conn->query("
                        SELECT a.assessment_name, c.class_name, a.subject, sa.date_scheduled
                        FROM assessment a
                        JOIN schedule_assessments sa on a.assessment_id = sa.assessment_id
                        JOIN class c ON sa.class_id = c.class_id
                        WHERE sa.faculty_id = '".$_SESSION['login_id']."' AND sa.date_scheduled >= '$today'
                        ORDER BY date_scheduled ASC
                    ");

                    // Initialize current date for display
                    $currentDate = '';

                    // Check if there is/are any assessment/s scheduled
                    if ($assessment->num_rows > 0) {
                        // Display assessment details
                        while ($row = $assessment->fetch_assoc()) {
                            if ($row['date_scheduled'] !== $currentDate) {
                                $currentDate = $row['date_scheduled'];
                                echo "<div id='schedule-separator' class='content-separator'>";
                                echo "<span id='date' class='content-name'> " . $currentDate . "</span>";
                                echo "<hr class='separator-line'>";
                                echo "</div>";
                            }

                            echo "<div class='schedule-item'>";
                            echo "<h3>" . htmlspecialchars($row['assessment_name']) . "</h3>";
                            echo "<p>" . htmlspecialchars($row['class_name']) . " (" . htmlspecialchars($row['subject']) . ")</p>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='no-records'>No upcoming assessments</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Schedule Assessment Popup -->
            <div id="add-schedule-popup" class="popup-overlay">
                <div class="popup-content">
                    <span class="popup-close">&times;</span>
                    <h2 class="popup-title">Schedule Assessment</h2>
                    <form id="schedule-form" class="form">
                        <div class="form-group">
                            <label for="selected-date">Date</label>
                            <input type="text" id="selected-date">                     
                        </div>
                        
                        <div class="form-group">
                            <label for="assessment-dropdown">Assessment</label>
                            <select id="assessment-dropdown" required>
                                <option value="" disabled selected>Select Assessment</option>
                                <?php
                                // Fetch all available assessments
                                $assessment_qry = $conn->query("
                                    SELECT * 
                                    FROM assessment 
                                    WHERE faculty_id = '".$_SESSION['login_id']."'
                                ");

                                // Add available assessments in the options
                                while($assessment_row = $assessment_qry->fetch_assoc()) {
                                    echo "<option value='".$assessment_row['assessment_id']."'>". htmlspecialchars($assessment_row['assessment_name']) . " (" . htmlspecialchars($assessment_row['subject']) . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="class-dropdown">Class</label>
                            <select name="class" id="class-dropdown" required>
                                <option value="" disabled selected>Select Class</option>
                                <!-- Classes will be populated here -->
                            </select>
                        </div> 
                    </form>
                        
                    <div class="popup-buttons">
                        <button class="secondary-button" id="save-button">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const scheduledDates = <?php echo json_encode($schedules); ?>;

            // Function to fetch pending requests from students
            function fetchPendingRequests() {
                fetch('get_requests.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('pending-requests').innerHTML = data;
                })
                .catch(error => console.error('Error fetching pending requests:', error));
            }

            // Pending requests buttons functionality
            $(document).on('click', '.accept-btn, .reject-btn', function() {
                var classId = $(this).data('class-id');
                var studentId = $(this).data('student-id');
                var status = $(this).data('status');

                $.ajax({
                    url: 'status_update.php',
                    type: 'POST',
                    data: {
                        class_id: classId,
                        student_id: studentId,
                        status: status
                    },
                    success: function(response) {
                        if (response == 'success') {
                            alert('Student status updated.');
                            location.reload();
                        } else {
                            alert('Failed to update status.');
                        }
                    } 
                });
            });
            
            // Initial fetch
            fetchPendingRequests();

            // Refresh every 5 seconds
            setInterval(fetchPendingRequests, 10000);

            // Modal functionality
            const modal = document.getElementById("add-schedule-popup");
            const btn = document.querySelector(".add-schedule");
            const span = document.getElementsByClassName("popup-close")[0];
            const selectedDateInput = document.getElementById("selected-date");

            // Function to show the add schedule popup
            btn.onclick = function() {
                clearFormFields();
                console.log(selectedDate);
                // Set the selected date to the input field when the modal opens
                selectedDateInput.value = selectedDate || `${currMonth + 1}/${date.getDate()}/${currYear}`; // Default to today's date if none selected
                modal.style.display = "flex";
            }

            // Function to clear form fields
            function clearFormFields() {
                selectedDateInput.value = '';
                document.getElementById("assessment-dropdown").selectedIndex = 0;
                document.getElementById("class-dropdown").innerHTML = '<option value="" disabled selected>Select Class</option>';
            }

            // Function to close the add schedule popup
            span.onclick = function() {
                modal.style.display = "none";
            }

            // Close the add schedule popup when the user clicks anywhere outside of the popup
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            }

            // Load classes based on selected assessment
            $('#assessment-dropdown').change(function() {
                var assessment_id = $(this).val();
                if (assessment_id) {
                    $.ajax({
                        url: 'get_class.php',
                        method: 'POST',
                        data: { assessment_id: assessment_id },
                        success: function(response) {
                            $('#class-dropdown').html(response); //  classes dropdown
                        }
                    });
                } else {
                    $('#class-dropdown').html('<option value="" disabled>Select Class</option>'); // Clear classes dropdown
                }
            });

            // Save button function
            document.getElementById("save-button").onclick = function(e) {
                e.preventDefault();
                const form = document.getElementById('schedule-form');

                // Prevent default submission of form
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                const selectedDate = new Date(selectedDateInput.value);
                const today = new Date();
                
                // Reset time for comparison
                today.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);

                // Check if selected date is before today
                if (selectedDate < today) {
                    alert('Make sure to pick a date that is either today or in the upcoming days.'); // Show error message
                    return;
                }

                // Gather data for the AJAX request
                const assessmentId = document.getElementById("assessment-dropdown").value;
                const classId = document.getElementById("class-dropdown").value;
                const facultyId = <?php echo $_SESSION['login_id']; ?>;

                console.log("Selected Date:", selectedDateInput.value);
                console.log("Assessment ID:", assessmentId);
                console.log("Class ID:", classId);
                console.log("Faculty ID:", facultyId);

                // Send data to save_schedule.php via AJAX
                $.ajax({
                    url: 'save_schedule.php',
                    method: 'POST',
                    data: {
                        date: selectedDateInput.value,
                        assessment_id: assessmentId,
                        class_id: classId,
                        faculty_id: facultyId
                    },
                    success: function(response) {
                        console.log(response);
                        if (response === 'success') {
                            alert('Assessment scheduled successfully!');
                            modal.style.display = "none";
                            location.reload();
                        } else if (response === 'exists') {
                            alert('This assessment has already been scheduled for this class.');
                        } else {
                            alert('Failed to schedule the assessment. Please try again.');
                        }
                    },
                    error: function() {
                        alert('An error occurred while trying to save the assessment. Please try again.');
                    }
                });
            }        
        </script>
    </body>
</html>