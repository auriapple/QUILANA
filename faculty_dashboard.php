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
    </head>
    <body>
        <?php include 'nav_bar.php'; ?>
        <div class="container-flud admin" style="flex-shrink: 1;">
            <div class="dashboard-summary">
                <h1> Welcome, <?php echo $firstname ?> </h1>
                <h2> Summary </h2>
                <div class="cards"> 
                    <div class="course-card">
                        <img class="icons" src="image/DashboardCoursesIcon.png" alt="Courses Icon">
                        <?php
                        // Output for the total courses and classes (No Design)
                        $result = $conn->query("SELECT COUNT(*) as totalCourses FROM course 
                                                WHERE faculty_id = '".$_SESSION['login_id']."'");
                        $resTotalCourses = $result->fetch_assoc();
                        $totalCourses = $resTotalCourses['totalCourses'];
                        echo "<h3>" .$totalCourses. "</h3>";
                        echo "Total Number of Courses: ";
                        ?>
                    </div>
                    <div class="class-card"> 
                        <img class="icons" src="image/DashboardClassesIcon.png" alt="Classes Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalClasses FROM class
                                                WHERE faculty_id = '".$_SESSION['login_id']."'");
                        $resTotalClasses = $result->fetch_assoc();
                        $totalClasses = $resTotalClasses['totalClasses'];
                        echo "<h3>" .$totalClasses. "</h3>";
                        echo "Total Number of Classes: ";
                        ?>
                    </div>
                </div>
            </div>
            <div class="dashboard-requests">
                <h1> Pending Requests </h1>
                <?php
                $qry = $conn->query("
                    SELECT s.student_id, CONCAT(s.lastname, ', ', s.firstname) AS student_name, c.class_id, c.faculty_id, c.class_name, c.subject, se.status
                    FROM student s
                    JOIN student_enrollment se ON s.student_id = se.student_id
                    JOIN class c ON se.class_id = c.class_id
                    WHERE c.faculty_id = '".$_SESSION['login_id']."' AND se.status = '0'
                    ORDER BY c.class_name, student_name
                ");
                
                $current_class = '';

                while ($row = $qry->fetch_assoc()) {
                    $student_id = htmlspecialchars($row['student_id']);
                    $student_name = htmlspecialchars($row['student_name']);
                    $class_id = htmlspecialchars($row['class_id']);
                    $class_name = htmlspecialchars($row['class_name']);
                    $subject = htmlspecialchars($row['subject']);
                    $status = htmlspecialchars($row['status']);
                    
                    if ($class_name !== $current_class) {
                        // Close the previous class section (if it's not the first class)
                        if ($current_class !== '') {
                            echo "</div>"; // End of the previous class section
                        }
                        
                        // Start a new class section
                        $current_class = $class_name;
                        ?>
                        <div class="class-header">
                            <span><?php echo $class_name . ' ( ' . $subject . ' )' ?></span>
                            <div class="line"></div>
                        </div>
                        <div class="student-list">
                        <?php
                     }

                     // Student Items 
                     ?>
                     <div class="student-item">
                         <?php echo $student_name ?>
                        <div class="btns">
                            <button class="btn btn-primary btn-sm accept-btn accept" 
                                data-class-id="<?php echo $class_id ?>" 
                                data-student-id="<?php echo $student_id ?>" 
                                data-status="1" 
                                type="button">Accept</button>
                            <button class="btn btn-primary btn-sm reject-btn reject" 
                                data-class-id="<?php echo $class_id ?>" 
                                data-student-id="<?php echo $student_id ?>" 
                                data-status="2" 
                                type="button">Reject</button>
                        </div>
                     </div>
                     <?php
                }

                if ($current_class !== '') {
                    echo "</div>"; // Close the last student-list div
                }
                ?>
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
        </script>
    </body>
</html>
