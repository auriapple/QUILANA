<style>
    .swal2-actions {
        gap: 10px;
        flex-direction: row-reverse;
    }
    .swal2-actions button {
        width: 150px !important;
        margin-top: 20px !important;
    }
    #swal2-input {
        width: 340px;
    }
</style>

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
        <link rel="stylesheet" href="assets/css/classes.css">
        <script src="assets/js/calendar.js" defer></script>
    </head>
    <body>
        <?php include 'nav_bar.php';
        $settings_query = $conn->query("
            SELECT * FROM dashboard_settings WHERE user_id = '".$_SESSION['login_id']."' AND user_type = 2
        ");
        $settings = $settings_query->fetch_assoc();
    ?>
        <div hidden>
            <input id='summary-input' value = "<?php echo $settings['summary'] ?>">
            <input id='request-input' value = "<?php echo $settings['request'] ?>">
            <input id='report-input' value = "<?php echo $settings['report'] ?>">
            <input id='calendar-input' value = "<?php echo $settings['calendar'] ?>">
            <input id='upcoming-input' value = "<?php echo $settings['upcoming'] ?>">
        </div>

        <div class="content-wrapper dashboard-container">
            <div class="section1" id="section1">
                <div class="section1-1" id="section1-1">
                    <!-- Dashboard Summary -->
                    <div class="dashboard-summary" id="dashboard-summary">
                        <?php 
                            $name_query = $conn->query("
                                SELECT lastname FROM faculty WHERE faculty_id = '".$_SESSION['login_id']."'
                            ");
                            $name = $name_query->fetch_assoc();
                            $lastname = $name['lastname'];
                        ?>
                        <h1> Welcome, Prof. <?php echo $lastname ?> </h1>
                        <h2> Summary </h2>
                        <div class="cards"> 
                            <div class="card" style="background-color: #ffe2e5; cursor: pointer;" onclick="window.location.href='class_list.php';">
                                <img class="icons" src="image/DashboardCoursesIcon.png" alt="Courses Icon">
                                <?php
                                $result = $conn->query("SELECT COUNT(*) as totalCourses FROM course 
                                                        WHERE faculty_id = '".$_SESSION['login_id']."'");
                                $resTotalCourses = $result->fetch_assoc();
                                $totalCourses = $resTotalCourses['totalCourses'];
                                ?>
                                <div class="card-data">
                                    <h3> <?php echo $totalCourses ?> </h3>
                                    <label>Total Programs</label> 
                                </div>
                            </div>
                            <div class="card" style="background-color: #FADEFF; cursor: pointer;" onclick="window.location.href='class_list.php';"> 
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
                    <div class="dashboard-requests" id="dashboard-requests">
                        <h1> Pending Requests </h1>
                        <div id="pending-requests" class="requests">
                            <!-- Requests will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="section1-2" id="section1-2">
                    <div class="dashboard-chart" id="dashboard-chart">
                        <h1>Report</h1>
                        <canvas id="lineChart" width="800" height="400"></canvas>
                    </div>    
                </div>
            </div>
            
            <div class="section2">
                <!-- Dashboard Calendar -->
                <div class="dashboard-calendar" id="dashboard-calendar">
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
                        <footer id="footer">
                            <div class="line"></div>
                            <h1>Today</h1>
                            <div class="today-schedule" id="today-schedule">
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
                        <img class="icons" src="image/DashboardCalendarIcon.png" alt="Calendar Icon">  
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
            </div>

            <div class="alert-container" id="alert-container">
                <!-- Alert for Accepting/Rejecting Students will be displayed here -->
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
                            <label for="classes-dropdown">Classes</label>
                            <select id="classes-dropdown" required>
                                <option value="" disabled selected>Select Class</option>
                                <?php
                                // Fetch all available classes
                                $classes_qry = $conn->query("
                                    SELECT * 
                                    FROM class 
                                    WHERE faculty_id = '".$_SESSION['login_id']."'
                                    ORDER BY course_id
                                ");

                                // Add available classes in the options
                                while ($classes_row = $classes_qry->fetch_assoc()) {
                                    echo "<option value='".$classes_row['class_id']."' 
                                        data-course-id='".$classes_row['course_id']."' 
                                        data-subject='".htmlspecialchars($classes_row['subject'])."'>
                                        ". htmlspecialchars($classes_row['class_name']) . " (" . htmlspecialchars($classes_row['subject']) . ")
                                    </option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="assessments-dropdown">Assessments</label>
                            <select name="assessments" id="assessments-dropdown" required>
                                <option value="" disabled selected>Select Assessment</option>
                                <!-- Assessments will be populated here -->
                            </select>
                        </div> 
                    </form>
                        
                    <div class="popup-buttons">
                        <button class="secondary-button" id="save-button">Save</button>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Customization -->
            <button id='dashboard-options-button' data-type="2" data-id="<?php echo $_SESSION['login_id']; ?>"><span class="material-symbols-outlined">settings</span></button> 

            <div id="dashboard-options-popup" class="popup-overlay"> 
                <div id="dashboard-options-modal-content" class="popup-content" role="document">
                    <button class="popup-close">&times;</button>
                    <h2 id="dashboard-options-title" class="popup-title">Dashboard Customization</h2>

                    <div class="modal-body" id="dashboardOptionsBody">
                        <div class="dashboard-option">
                            <label>Summary</label>
                            <label class="switch">
                                <input type="checkbox" id="summary-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="dashboard-option">
                            <label>Requests</label>
                            <label class="switch">
                                <input type="checkbox" id="request-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="dashboard-option">
                            <label>Report</label>
                            <label class="switch">
                                <input type="checkbox" id="report-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="dashboard-option">
                            <label>Calendar</label>
                            <label class="switch">
                                <input type="checkbox" id="calendar-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="dashboard-option last">
                            <label>Upcoming Assessments</label>
                            <label class="switch">
                                <input type="checkbox" id="upcoming-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button id="save-settings" class="secondary-button" name="save">Save</button>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <script>
            const scheduledDates = <?php echo json_encode($schedules); ?>;

            function addChildAlert(studentName, className, res) {
                // Create a new div element
                const alert = document.createElement('div');
                alert.className = 'alert-card';
                alert.textContent = studentName + ' has been ' + res + ' ' + className;

                // Append the new child to the parent
                const alertContainer = document.getElementById('alert-container');
                alertContainer.appendChild(alert);

                // Store a reference to the child element
                const thisAlert = alert;

                // Set a timeout to fade out the child after 5 seconds then removed
                setTimeout(() => {
                    thisAlert.classList.add('fade');
                }, 5000);
                setTimeout(() => {
                    alertContainer.removeChild(thisAlert);
                }, 7000);
            }

            // Function to fetch pending requests from students
            function fetchPendingRequests() {
                fetch('get_requests.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('pending-requests').innerHTML = data;
                })
                .catch(error => console.error('Error fetching pending requests:', error));
            }

            function acceptRejectStudent(classId, studentId, status, classSub, studentName, reason) {
                var res = status == 1 ? 'accepted to ' : status == 2 ? 'rejected from ' : null;

                $.ajax({
                    url: 'status_update.php',
                    type: 'POST',
                    data: {
                        class_id: classId,
                        student_id: studentId,
                        status: status,
                        reason: reason
                    },
                    success: function(response) {
                        if (response == 'success') {
                            addChildAlert(studentName, classSub, res);
                            console.log(studentName + '\n' + classSub + '\n' + res);
                            fetchPendingRequests();
                        } else {
                            Swal.fire({
                                title: 'Warning!',
                                text: 'An error occured in trying to reject/accept ' + studentName + ' to ' + classSub + '.',
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
                    } 
                });
            }

            // Pending requests buttons functionality
            $(document).on('click', '.accept-btn, .reject-btn', function() {
                var classId = $(this).data('class-id');
                var studentId = $(this).data('student-id');
                var status = $(this).data('status');
                var classSub = $(this).data('class-sub');
                var studentName = $(this).data('student-name');
                var reason;

                if ($(this).hasClass('accept-btn')) {
                    acceptRejectStudent(classId, studentId, status, classSub, studentName, reason);
                } else {
                    Swal.fire({
                        title: 'Confirm Rejection.',
                        text: 'Are you sure you want to reject ' + studentName + ' from ' + classSub + '.',
                        showCancelButton: true,
                        confirmButtonText: 'Reject',
                        cancelButtonText: 'Cancel',
                        allowOutsideClick: false,
                        input: 'text',
                        inputValue: 'Student is not enrolled in the class',
                        customClass: {
                            popup: 'popup-content',
                            confirmButton: 'secondary-button',
                            cancelButton: 'tertiary-button',
                            input: 'popup-input'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const reason = result.value;
                            acceptRejectStudent(classId, studentId, status, classSub, studentName, reason);
                            warningTracker = false;
                        } else if (result.isDismissed) {
                            console.log("User canceled the removal action.");
                        }
                    });
                }
            });
            
            // Initial fetch
            fetchPendingRequests();

            // Refresh every 5 seconds
            setInterval(fetchPendingRequests, 5000);

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
                selectedDateInput.value = selectedDate || `${currYear}-${currMonth + 1}-${date.getDate()}`; // Default to today's date if none selected
                modal.style.display = "flex";
            }

            // Function to clear form fields
            function clearFormFields() {
                selectedDateInput.value = '';
                document.getElementById("classes-dropdown").selectedIndex = 0;
                document.getElementById("assessments-dropdown").innerHTML = '<option value="" disabled selected>Select Assessment</option>';
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
            $('#classes-dropdown').change(function() {
                var class_id = $(this).val();
                var subject = $(this).find('option:selected').data('subject');
                var course_id = $(this).find('option:selected').data('courseId');

                console.log(" " + course_id)

                if (class_id) {
                    $.ajax({
                        url: 'get_assessments.php',
                        method: 'POST',
                        data: { 
                            class_id: class_id,
                            subject: subject,
                            course_id: course_id
                        },
                        success: function(response) {
                            $('#assessments-dropdown').html(response); //  classes dropdown
                        }
                    });
                } else {
                    $('#assessments-dropdown').html('<option value="" disabled>Select Assessment</option>'); // Clear classes dropdown
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
                    alert('Make sure to pick a date in the upcoming days.'); // Show error message
                    return;
                }

                // Gather data for the AJAX request
                const classId = document.getElementById("classes-dropdown").value;
                const assessmentId = document.getElementById("assessments-dropdown").value;
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
            
            function showPopup(popupId) {
                $('#' + popupId).css('display', 'flex');
            }

            function closePopup(popupId) {
                $('#' + popupId).css('display', 'none');
            }

            // Close the popup when close button is clicked
            $('.popup-close').on('click', function() {
                var activePopup = this.parentElement.parentElement.id;
                closePopup(activePopup);
            });

            function checkIfHasSettings (userId, userType) {
                $.ajax({
                    url: 'check_dashboardSettings.php', // PHP script location
                    type: 'POST',
                    data: { 
                        id: userId,
                        type: userType
                     },
                     success: function (response) {
                        if (typeof response === "string") {
                            response = JSON.parse(response);
                        }

                        if (response.status === 'success') {
                            console.log("A record already exists.")
                        } else if (response.status === 'created') {
                            console.log("A new record has been created.")
                        } else {
                            console.error("Error: ", response?.message ?? "Unknown error");
                        }
                    },
                    error: function () {
                        alert('An error occurred. Please try again.');
                    }
                })
            }

            const userId = document.getElementById('dashboard-options-button').dataset.id;
            const userType = document.getElementById('dashboard-options-button').dataset.type;

            checkIfHasSettings(userId, userType);

            function setDashboardToggles(userId, userType) {
                $.ajax({
                    url: 'check_dashboardSettings.php',
                    type: 'POST',
                    data: { 
                        id: userId,
                        type: userType
                     },
                     success: function (response) {
                        if (typeof response === "string") {
                            response = JSON.parse(response);
                        }

                        if (response.status === 'success') {
                            $('#summary-toggle').prop('checked', response?.summary == 1 ?? false);
                            $('#request-toggle').prop('checked', response?.request == 1 ?? false);
                            $('#report-toggle').prop('checked', response?.report == 1 ?? false);
                            $('#calendar-toggle').prop('checked', response?.calendar == 1 ?? false);
                            $('#upcoming-toggle').prop('checked', response?.upcoming == 1 ?? false);

                            console.log('gotta work');
                        } else {
                            console.error("Error checking and setting randomization:", response?.message ?? "Unknown error");
                        }
                    },
                    error: function () {
                        alert('An error occurred. Please try again.');
                    }
                })
            }

            $('#dashboard-options-button').click(function() {
                const userId = $(this).data('id');
                const userType = $(this).data('type');

                setDashboardToggles(userId, userType);
                showPopup('dashboard-options-popup');
            });

            const summaryCheckbox = document.getElementById('summary-toggle');
            const requestCheckbox = document.getElementById('request-toggle');
            const reportCheckbox = document.getElementById('report-toggle');
            const calendarCheckbox = document.getElementById('calendar-toggle');
            const upcomingCheckbox = document.getElementById('upcoming-toggle');

            let summary;
            let request;
            let report;
            let calendar;
            let upcoming;

            function showToggledDashboard() {
                $('.dashboard-summary').hide();
                $('.dashboard-requests').hide();
                $('.dashboard-chart').hide();
                $('.dashboard-calendar').hide();
                $('.dashboard-schedule').hide();

                let ifSection1Show = false;
                let ifSection1_1Show = false;
                let ifSection1_2Show = false;
                let ifSection2Show = false;

                if (document.getElementById('summary-input').value == 1) {
                    $('.dashboard-summary').show();
                    ifSection1Show = true;
                    ifSection1_1Show = true;
                    console.log('summary');
                }

                if (document.getElementById('request-input').value == 1) {
                    $('.dashboard-requests').show();
                    ifSection1Show = true;
                    ifSection1_1Show = true;
                    console.log('request');
                }

                if (document.getElementById('report-input').value == 1) {
                    $('.dashboard-chart').show();
                    ifSection1Show = true;
                    ifSection1_2Show = true;
                    console.log('report');
                }

                if (document.getElementById('calendar-input').value == 1) {
                    $('.dashboard-calendar').show();
                    ifSection2Show = true;
                    console.log('calendar');
                }

                if (document.getElementById('upcoming-input').value == 1) {
                    $('.dashboard-schedule').show();
                    ifSection2Show = true;
                    console.log('upcoming');
                } else {
                    const footer = document.getElementById('footer');
                    footer.style.height = '100%';
                    footer.style.display = 'flex';
                    footer.style.flexDirection = 'column';
                    document.getElementById('dashboard-calendar').style.height = "100%";
                    document.getElementById('today-schedule').style.height = '100%'
                }

                if (!ifSection1Show) {
                    $('.section1').hide();
                    const section2 = document.getElementById('section2');
                    section2.style.width = '100%';
                    section2.style.flexDirection = 'row';
                    section2.style.paddingRight = '10px';
                    section2.style.paddingBottom = '30px';
                }
                if (!ifSection1_1Show) {
                    $('.section1-1').hide();
                    document.getElementById('section1-2').style.maxHeight = 'none';
                    const footer = document.getElementById('footer');
                    footer.style.height = '100%';
                    footer.style.display = 'flex';
                    footer.style.flexDirection = 'column';
                    document.getElementById('today-schedule').style.height = '100%'
                    document.getElementById('section1').style.marginRight = '10px';
                }
                if (!ifSection1_2Show) {
                    $('.section1-2').hide();
                    document.getElementById('section1-1').style.flexDirection = 'column';
                    document.getElementById('section1-1').style.maxHeight = 'none';
                    document.getElementById('dashboard-requests').style.maxHeight = 'none';
                    document.getElementById('section1').style.marginRight = '10px';

                    if (document.getElementById('request-input').value != 1) {
                        document.getElementById('dashboard-summary').style.maxHeight = 'none';
                    }
                }
                if (!ifSection2Show) {
                    $('.section2').hide();
                }
            }

            $(document).ready(function() {
                setDashboardToggles(userId, userType);
                showToggledDashboard();
            });

            $('#save-settings').click(function() {
                summary = summaryCheckbox.checked ? 1 : 0;
                request = requestCheckbox.checked ? 1 : 0;
                report = reportCheckbox.checked ? 1 : 0;
                calendar = calendarCheckbox.checked ? 1 : 0;
                upcoming = upcomingCheckbox.checked ? 1 : 0;

                $.ajax({
                    type: 'POST',
                    url: 'update_dashboardSettings.php',
                    data: { 
                        user_id : userId,
                        user_type : 2,
                        summary : summary,
                        request : request,
                        report : report,
                        calendar : calendar,
                        upcoming : upcoming,
                    },
                    dataType: 'json',
                    success: function(response) {
                        location.reload();
/*                         closePopup('dashboard-options-popup');
                        showToggledDashboard(); */
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: " + status + ": " + error);
                        alert('An error occurred while randomizing questions. Please try again.');
                    }
                });
            });

            // Fetch dynamic data from PHP
            const dataPoints = <?php echo json_encode([10, 40, 80, 30, 60, 120, 90]); ?>;

            const ctx = document.getElementById('lineChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dataPoints.map((_, index) => `Quiz ${index + 1}`),
                    datasets: [{
                        label: 'Introduction to Computing',
                        data: dataPoints,
                        borderColor: 'blue',
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { title: { display: true, text: 'Percentage' } },
                        y: { title: { display: true, text: 'Assessments' } }
                    }
                }
            });
        </script>
    </body>
</html>