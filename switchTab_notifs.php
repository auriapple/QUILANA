<!DOCTYPE html>
<html lang="en">
    <body>
        <?php
            include('db_connect.php');

            if (isset($_POST['class_id']) && isset($_POST['assessment_id'])) {
                $class_id = $conn->real_escape_string($_POST['class_id']);
                $assessment_id = $conn->real_escape_string($_POST['assessment_id']);

                // Fetch classes associated with the course
                $sql = "
                    SELECT s.*, CONCAT(lastname, ', ', firstname) as student_name, ja.*, aa.administer_id, a.max_warnings
                    FROM student s
                    JOIN join_assessment ja ON s.student_id = ja.student_id
                    JOIN administer_assessment aa ON ja.administer_id = aa.administer_id
                    JOIN assessment a ON aa.assessment_id = a.assessment_id
                    WHERE aa.assessment_id = $assessment_id AND aa.class_id = $class_id AND ja.if_display = true AND ja.status = 1
                    ORDER BY ja.time_updated";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
        ?>  
                        <div class="notification-card" data-administer-id="<?php echo $row['administer_id']; ?>" data-student-id="<?php echo $row['student_id']; ?>">
                            <span class="notif-close" class="popup-close">&times;</span>
                            <div> <?php echo htmlspecialchars($row['student_name']); ?> has performed a suspicious activity. They have <?php echo $row['max_warnings'] - $row['suspicious_act']?> warnings left. </div>
                            <div class="timeStamp"> <?php echo date("h:ia", strtotime($row['time_updated'])); ?> </div>
                        </div>
        <?php 
                    }
                }
                // Close the connection
                $conn->close();
            } else {
                echo '<div class="alert alert-danger">A parameter is missing.</div>';
            }
        ?>
    </body>
</html>