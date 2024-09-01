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
                        <div class="course-card">
                            <div class="course-card-body">
                                <div class="meatball-menu-container">
                                    <button class="meatball-menu-btn">
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                    </button>
                                    <div class="meatball-menu">
                                        <a href="#" class="edit_class" data-class-id="<?php echo $row['class_id'] ?>" data-class-name="<?php echo $row['class_name']?>" data-subject="<?php echo $row['subject']?>">Edit</a>
                                        <a href="#" class="delete_class" data-class-id="<?php echo $row['class_id'] ?>" data-class-name="<?php echo $row['class_name'] ?>" data-subject="<?php echo $row['subject']?>">Delete</a>
                                        <a href="#" class="get_code" data-class-id="<?php echo $row['class_id'] ?>" data-class-name="<?php echo $row['class_name'] ?>" data-subject="<?php echo $row['subject']?>" data-code="<?php echo $row['code']?>">Get Code</a>
                                    </div>
                                </div>
                                <div class="course-card-title"><?php echo htmlspecialchars($row['class_name']) ?></div>
                                <div class="course-card-text"><br>Course Subject: <br> <?php echo htmlspecialchars($row['subject']) ?> </div>
                                <div class="class-actions">
                                    <button class="btn btn-primary btn-sm view_class_details" data-id="<?php echo $row['class_id']?> "type="button">View Details</button>
                                </div>
                            </div>
                        </div>
        <?php 
                    }
                } else {
                    echo '<div class="alert alert-info">No classes found for this course.</div>';
                }

                // Close the connection
                $conn->close();
            } else {
                echo '<div class="alert alert-danger">Course ID is missing.</div>';
            }
        ?>

        <script>
            const meatballMenuBtns = document.querySelectorAll('.meatball-menu-btn');
            meatballMenuBtns.forEach(function(meatballMenuBtn) {
                meatballMenuBtn.addEventListener('click', function(event) {
                    // Close any open menus first
                    document.querySelectorAll('.meatball-menu-container').forEach(function(container) {
                        if (container !== meatballMenuBtn.parentElement) {
                            container.classList.remove('show');
                        }
                    });

                    // Toggle the clicked menu
                    const meatballMenuContainer = meatballMenuBtn.parentElement;
                    meatballMenuContainer.classList.toggle('show');

                    // Stop the event from bubbling up to the document
                    event.stopPropagation();
                });
            });

            // Close the menu if clicked outside
            document.addEventListener('click', function(event) {
                document.querySelectorAll('.meatball-menu-container').forEach(function(container) {
                    if (!container.contains(event.target)) {
                        container.classList.remove('show');
                    }
                });
            });
        </script>
    </body>
</html>