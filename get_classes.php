<!DOCTYPE html>
<html lang="en">
    <body>
        <?php
            include('db_connect.php');

            // Check if course_id is set
            if (isset($_POST['course_id'])) {
                $course_id = $conn->real_escape_string($_POST['course_id']);

                // Fetch classes associated with the course
                $sql = "SELECT * FROM class WHERE course_id = '$course_id' ORDER BY class_name ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
        ?>  
                        <div class="class-card">
                            <div class="class-card-body">
                                <div class="meatball-menu-container">
                                    <button class="meatball-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="meatball-menu">
                                        <div class="arrow-up"></div>
                                        <a href="#" class="edit_class" 
                                            data-course-id= <?php echo $course_id ?>
                                            data-class-id="<?php echo $row['class_id'] ?>" 
                                            data-class-name="<?php echo $row['class_name']?>" 
                                            data-subject="<?php echo $row['subject']?>">
                                            <span class="material-symbols-outlined">Edit</span>
                                            Edit</a>
                                        <a href="#" class="delete_class" 
                                            data-course-id= <?php echo $course_id ?>
                                            data-class-id="<?php echo $row['class_id'] ?>" 
                                            data-class-name="<?php echo $row['class_name'] ?>" 
                                            data-subject="<?php echo $row['subject']?>">
                                            <span class="material-symbols-outlined">delete</span>
                                            Delete</a>
                                        <a href="#" class="get_code" 
                                            data-class-id="<?php echo $row['class_id'] ?>"
                                            data-class-name="<?php echo $row['class_name'] ?>" 
                                            data-subject="<?php echo $row['subject']?>">
                                            <span class="material-symbols-outlined">key</span>
                                            Get Code</a>
                                    </div>
                                </div>
                                <div class="class-card-title"><?php echo htmlspecialchars($row['class_name']) ?></div>
                                <div class="class-card-text">Course Subject: <?php echo htmlspecialchars($row['subject']) ?> </div>
                                <div class="class-actions">
                                    <button id="viewClassDetails" class="main-button" data-id="<?php echo $row['class_id'] ?>" type="button">View Details</button>
                                </div>
                            </div>
                        </div>
        <?php 
                    }
                } else {
                    echo '<div class="no-records" style="grid-column: 1/-1; text-align: center;">No classes found for this program</div>';
                }

                // Close the connection
                $conn->close();
            } else {
                echo '<div class="alert alert-danger">Course ID is missing.</div>';
            }
        ?>
    </body>
</html>
