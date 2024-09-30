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
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,200,0,200" />
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
                            <label>Total Number of Courses</label> 
                        </div>
                    </div>
                    <div class="card"> 
                        <img class="icons" src="image/DashboardClassesIcon.png" alt="Classes Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalClasses FROM class
                                                WHERE faculty_id = '".$_SESSION['login_id']."'");
                        $resTotalClasses = $result->fetch_assoc();
                        $totalClasses = $resTotalClasses['totalClasses'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalClasses ?> </h3>
                            <label>Total Number of Classes</label> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="dashboard-requests">
                <h1> Pending Requests </h1>
                <div id="pending-requests" class="requests">
                    <!-- Requests will be loaded here -->
                </div>
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
        <script>
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
            
            function fetchPendingRequests() {
                fetch('get_requests.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('pending-requests').innerHTML = data;
                })
                .catch(error => console.error('Error fetching pending requests:', error));
            }

            // Initial fetch
            fetchPendingRequests();

            // Refresh every 5 seconds
            setInterval(fetchPendingRequests, 3000);
        </script>
    </body>
</html>