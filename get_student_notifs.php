<!DOCTYPE html>
<html lang="en">
    <body>
        <?php
            include('db_connect.php');

            if (isset($_POST['student_id'])) {
                $student_id = $conn->real_escape_string($_POST['student_id']);

                // Fetch classes associated with the course
                $sql = "
                    SELECT se.*, c.class_name, c.subject FROM student_enrollment se
                    JOIN class c ON se.class_id = c.class_id
                    WHERE student_id = $student_id AND if_display = 1";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
        ?>  
                        <div class="notification-card" data-enrollment-id="<?php echo $row['studentEnrollment_id']; ?>">
                            <span class="notif-close" class="popup-close">&times;</span>
                            <?php
                            if ($row['status'] == 1) {
                                echo "<div> You have been accepted to " . $row['class_name'] . " (" . $row['subject'] . ").</div>";
                            } else {
                                echo "<div> You have been rejected from " . $row['class_name'] . " (" . $row['subject'] . ") with the reason: " . $row['reason'] . ".</div>";
                            }
                            ?>
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