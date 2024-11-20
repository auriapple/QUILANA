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
        <script src="assets/js/calendar.js" defer></script>
        <script>
            const scheduledDates = <?php echo json_encode($schedules); ?>;
        </script>
    </head>
    <body>
        <?php include 'nav_bar.php'; ?>
        <div class="content-wrapper dashboard-container">
            <!-- Dashboard Summary -->
            <div class="dashboard-summary">
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
            <div class="recent-assessments">
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
        </script>
    </body>
</html>