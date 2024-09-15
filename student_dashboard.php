<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include('header.php') ?>
        <title>Dashboard | Quilana</title>
        <link rel="stylesheet" href="assets/css/faculty-dashboard.css">
        <link rel="stylesheet" href="assets/css/calendar.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
        <script src="assets/js/calendar.js" defer></script>
    </head>
    <body>
        <?php include 'nav_bar.php'; ?>
        <div class="content-wrapper dashboard-container">
            <div class="dashboard-summary">
                <h1> Welcome, <?php echo $firstname ?> </h1>
                <h2> Summary </h2>
                <div class="cards">
                    <!-- Total Number of Classes -->
                    <div class="card" style="background-color: #FFE2E5;">
                        <img class="icons" src="image/DashboardCoursesIcon.png" alt="Courses Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalClasses 
                                                FROM class c
                                                JOIN student_enrollment s ON c.class_id = s.class_id
                                                WHERE s.student_id = '".$_SESSION['login_id']."'");
                        $resTotalClasses = $result->fetch_assoc();
                        $totalClasses = $resTotalClasses['totalClasses'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalClasses ?> </h3>
                            <label>Total Classes</label> 
                        </div>
                    </div>
                    <!-- Total Number of Quizzes -->
                    <div class="card"> 
                        <img class="icons" src="image/DashboardClassesIcon.png" alt="Classes Icon">
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
                    <div class="card" style="background-color: #DCE1FC;"> 
                        <img class="icons" src="image/DashboardExamsIcon.png" alt="Classes Icon">
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

            <div class="dashboard-requests">
                <h1> Recents </h1>
                <?php
                $result = $conn->query("
                    SELECT a.assessment_name, a.assessment_type, c.class_name, c.subject, ss.date_taken
                    FROM student_results sr
                    JOIN student_submission ss ON sr.submission_id = ss.submission_id
                    JOIN assessment a ON sr.assessment_id = a.assessment_id
                    JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
                    JOIN class c ON aa.class_id = c.class_id
                    WHERE ss.student_id = '".$_SESSION['login_id']."'
                    ORDER BY ss.date_taken DESC
                    LIMIT 5
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
                            echo "<div class='assessment-separator'>";
                            echo "<span class='date'> " . $currentDate . "</span>";
                            echo "<hr class='separator-line'>";
                            echo "</div>";
                        }

                        // Setting the background color and icon based on assessment type
                        $bgColor = ($assessmentType == 1) ? '#FADEFF' : '#DCE1FC';
                        $icon = ($assessmentType == 1) ? 'DashboardClassesIcon.png' : 'DashboardExamsIcon.png';

                        // Display the card with proper icon and background color
                        echo "<div id='recents' class='cards'>";
                            echo "<div id='recent-card' class='card' style='background-color: {$bgColor};'>";
                                echo "<div id='recent-data' class='card-data'>";
                                    echo "<div class='recent-icon'>";
                                        echo "<img class='icons' src='image/{$icon}' alt='" . (($assessmentType == 1) ? 'Quiz' : 'Exam') . " Icon'>";
                                    echo "</div>";
                                    echo "<div class='recent-details'>";
                                        echo "<h3>{$assessmentName}</h3>";
                                        echo "<label>{$className} ({$subjectName})</label>";
                                    echo "</div>";
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                    }
                    } else {
                        echo "<p class='no-assessments'>No recent submissions.</p>";
                    }
                ?>
            </div>

            <div class="dashboard-calendar">
                <div class="wrapper">
                    <header>
                        <p class="current-date"></p>
                        <div class="icons">
                        <span id="prev" class="material-symbols-rounded">chevron_left</span>
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
                </div>
            </div>
        </div>
    </body>
</html>