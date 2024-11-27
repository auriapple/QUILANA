<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

// Fetch scheduled assessments for calendar display
$today = date('Y-m-d');
$scheduleQuery = $conn->query("
    SELECT sa.*
    FROM schedule_assessments sa
    JOIN student_enrollment se on sa.class_id = se.class_id
    WHERE sa.date_scheduled >= '$today' AND se.student_id = '".$_SESSION['login_id']."' AND se.status = 1
");
$schedules = [];
while ($schedule_row = $scheduleQuery->fetch_assoc()) {
    $schedules[] = $schedule_row['date_scheduled'];
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
        <script src="assets/js/chart.js"></script>
        <script src="assets/js/calendar.js" defer></script>
        <script>
            const scheduledDates = <?php echo json_encode($schedules); ?>;
        </script>
    </head>
    <body>
        <?php include 'nav_bar.php';
        $settings_query = $conn->query("
            SELECT * FROM dashboard_settings WHERE user_id = '".$_SESSION['login_id']."' AND user_type = 3
        ");
        $settings = $settings_query->fetch_assoc();

        $chart_query = $conn->query("
            SELECT sr.score / sr.total_score * a.passing_rate + (100 - a.passing_rate) AS percentage FROM student_results sr JOIN assessment a ON sr.assessment_id = a.assessment_id WHERE student_id = '118'
        ");
        $chart_data = [];
        while ($chart = $chart_query->fetch_assoc()) {
            $chart_data[] = $chart['percentage'];
        }

        $json_chart_data = json_encode($chart_data);
    ?>
        <div hidden>
            <input id='summary-input' value = "<?php echo $settings['summary'] ?>">
            <input id='recent-input' value = "<?php echo $settings['recent'] ?>">
            <input id='report-input' value = "<?php echo $settings['report'] ?>">
            <input id='calendar-input' value = "<?php echo $settings['calendar'] ?>">
            <input id='upcoming-input' value = "<?php echo $settings['upcoming'] ?>">
        </div>

        <div class="content-wrapper dashboard-container">
            <div class="section1"> 
                <div class="section1-1" id="section1-1">
                    <!-- Dashboard Summary -->
                    <div class="dashboard-summary" id="dashboard-summary">
                        <?php 
                            $name_query = $conn->query("
                                SELECT firstname FROM student WHERE student_id = '".$_SESSION['login_id']."'
                            ");
                            $name = $name_query->fetch_assoc();
                            $firstname = $name['firstname'];
                        ?>
                        <h1> Welcome, <?php echo $firstname ?> </h1>
                        <h2> Summary </h2>
                        <div class="cards">
                            <!-- Total Number of Classes -->
                            <div class="card" style="background-color: #FFE2E5; cursor: pointer;" onclick="window.location.href='enroll.php';">
                                <img class="icons" src="image/DashboardCoursesIcon.png" alt="Classes Icon">
                                <?php
                                $result = $conn->query("SELECT COUNT(*) as totalClasses 
                                                        FROM class c
                                                        JOIN student_enrollment s ON c.class_id = s.class_id
                                                        WHERE s.student_id = '".$_SESSION['login_id']."'
                                                        AND s.status = '1'");
                                $resTotalClasses = $result->fetch_assoc();
                                $totalClasses = $resTotalClasses['totalClasses'];
                                ?>
                                <div class="card-data">
                                    <h3> <?php echo $totalClasses ?> </h3>
                                    <label>Total Classes</label> 
                                </div>
                            </div>
                            <!-- Total Number of Quizzes -->
                            <div class="card" style="background-color: #FADEFF; cursor: pointer;" onclick="window.location.href='results.php';">
                                <img class="icons" src="image/DashboardClassesIcon.png" alt="Quizzes Icon">
                                <?php
                                $result = $conn->query("SELECT COUNT(*) as totalQuizzes 
                                                        FROM student_submission s
                                                        JOIN assessment a ON s.assessment_id = a.assessment_id
                                                        WHERE s.student_id = '".$_SESSION['login_id']."'
                                                        AND a.assessment_type = 1
                                ");
                                $resTotalQuizzes = $result->fetch_assoc();
                                $totalQuizzes = $resTotalQuizzes['totalQuizzes'];
                                ?>
                                <div class="card-data">
                                    <h3> <?php echo $totalQuizzes ?> </h3>
                                    <label>Total Quizzes</label> 
                                </div>
                            </div>
                            <!-- Total Number of Exams -->
                            <div class="card" style="background-color: #DCE1FC; cursor: pointer;" onclick="window.location.href='results.php?tab=exams';">
                                <img class="icons" src="image/DashboardExamsIcon.png" alt="Exams Icon">
                                <?php
                                $result = $conn->query("SELECT COUNT(*) as totalExams
                                                        FROM student_submission s
                                                        JOIN assessment a ON s.assessment_id = a.assessment_id
                                                        WHERE s.student_id = '".$_SESSION['login_id']."'
                                                        AND a.assessment_type = 2
                                ");
                                $resTotalExams = $result->fetch_assoc();
                                $totalExams = $resTotalExams['totalExams'];
                                ?>
                                <div class="card-data">
                                    <h3> <?php echo $totalExams ?> </h3>
                                    <label>Total Exams</label> 
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Assessments -->
                    <div class="recent-assessments" id="recent-assessments">
                        <h1> Recents </h1>
                        <div class="recent-scrollable">
                            <?php
                            $result = $conn->query("
                                SELECT a.assessment_name, a.assessment_id, a.assessment_type, c.class_name, c.subject, ss.date_taken, aa.administer_id
                                FROM student_results sr
                                JOIN student_submission ss ON sr.submission_id = ss.submission_id
                                JOIN assessment a ON sr.assessment_id = a.assessment_id
                                JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
                                JOIN class c ON aa.class_id = c.class_id
                                WHERE ss.student_id = '".$_SESSION['login_id']."' AND aa.administer_id = ss.administer_id
                                ORDER BY ss.date_taken DESC
                            ");

                            $currentDate = '';

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $assessmentName = htmlspecialchars($row['assessment_name']);
                                    $assessmentType = htmlspecialchars($row['assessment_type']);
                                    $className = htmlspecialchars($row['class_name']);
                                    $subjectName = htmlspecialchars($row['subject']);
                                    $dateTaken = date("Y-m-d", strtotime($row['date_taken']));

                                    // Divider by date_taken
                                    if ($dateTaken !== $currentDate) {
                                        $currentDate = $dateTaken;
                                        echo "<div id='assessment-separator' class='content-separator'>";
                                        echo "<span id='date' class='content-name'> " . $currentDate . "</span>";
                                        echo "<hr class='separator-line'>";
                                        echo "</div>";
                                    }

                                    // Setting the background color and icon based on assessment type
                                    $bgColor = ($assessmentType == 1) ? '#FADEFF' : '#DCE1FC';
                                    $icon = ($assessmentType == 1) ? 'DashboardClassesIcon.png' : 'DashboardExamsIcon.png';

                                    // Display the card with proper icon and background color
                                    echo "<div id='recents' class='cards' onclick='redirectToResults({$assessmentType}, {$row['assessment_id']}, {$row['administer_id']});'>";
                                        echo "<div id='recent-card' class='card' style='background-color: {$bgColor}; cursor: pointer;'>";   
                                            echo "<img class='icons' src='image/{$icon}' alt='" . (($assessmentType == 1) ? 'Quiz' : 'Exam') . " Icon'>";
                                            echo "<div id='recent-details' class='card-data'>";
                                                echo "<h3>{$assessmentName}</h3>";
                                                echo "<label>{$className} ({$subjectName})</label>";
                                            echo "</div>";    
                                        echo "</div>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p class='no-records'>No recent assessments</p>";
                            }
                            ?>
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

            <div class="section2" id="section2">
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
                                    JOIN student_enrollment se ON c.class_id = se.class_id
                                    WHERE se.student_id = '".$_SESSION['login_id']."' AND se.status = 1 AND sa.date_scheduled = '$today'
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
                        </footer>
                    </div>
                </div>

                <!-- Scheduled Assessments -->
                <div class="dashboard-schedule">
                    <div class="schedule-label">
                        <h1>Upcoming Assessments</h1>
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
                            JOIN student_enrollment se on c.class_id = se.class_id
                            WHERE se.student_id = '".$_SESSION['login_id']."' AND se.status = 1 AND sa.date_scheduled >= '$today'
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
            

            <!-- Dashboard Customization -->
            <button id='dashboard-options-button' data-type="3" data-id="<?php echo $_SESSION['login_id']; ?>"><span class="material-symbols-outlined">settings</span></button> 

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
                            <label>Recents</label>
                            <label class="switch">
                                <input type="checkbox" id="recents-toggle">
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
        <script>
            function redirectToResults(assessmentType, assessmentId, administerId) {
                var url = 'results.php';
                var params = [];

                if (assessmentType == 2) {
                    params.push('tab=exams');
                }

                params.push('assessment_id=' + assessmentId);
                params.push('administer_id=' + administerId);

                url += '?' + params.join('&');

                window.location.href = url;
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
                            $('#recents-toggle').prop('checked', response?.recent == 1 ?? false);
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
            const recentCheckbox = document.getElementById('recents-toggle');
            const reportCheckbox = document.getElementById('report-toggle');
            const calendarCheckbox = document.getElementById('calendar-toggle');
            const upcomingCheckbox = document.getElementById('upcoming-toggle');

            let summary;
            let recent;
            let report;
            let calendar;
            let upcoming;

            function showToggledDashboard() {
                $('.dashboard-summary').hide();
                $('.recent-assessments').hide();
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

                if (document.getElementById('recent-input').value == 1) {
                    $('.recent-assessments').show();
                    ifSection1Show = true;
                    ifSection1_1Show = true;
                    console.log('recent');
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
                }
                if (!ifSection1_2Show) {
                    $('.section1-2').hide();
                    document.getElementById('section1-1').style.flexDirection = 'column';
                    document.getElementById('section1-1').style.maxHeight = 'none';
                    document.getElementById('recent-assessments').style.maxHeight = 'none';

                    if (document.getElementById('recent-input').value != 1) {
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
                recent = recentCheckbox.checked ? 1 : 0;
                report = reportCheckbox.checked ? 1 : 0;
                calendar = calendarCheckbox.checked ? 1 : 0;
                upcoming = upcomingCheckbox.checked ? 1 : 0;

                $.ajax({
                    type: 'POST',
                    url: 'update_dashboardSettings.php',
                    data: { 
                        user_id : userId,
                        user_type : 3,
                        summary : summary,
                        recent : recent,
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
            const dataPoints = <?php echo $json_chart_data; ?>;

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
                        x: { title: { display: true, text: 'Index' } },
                        y: { title: { display: true, text: 'Value' } }
                    }
                }
            });
        </script>
    </body>
</html>