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
                                        <a href="#" class="edit_class" data-class-id="<?php echo $row['class_id'] ?>" data-class-name="<?php echo $row['class_name']?>" data-subject="<?php echo $row['subject']?>">Edit</a>
                                        <a href="#" class="delete_class" data-class-id="<?php echo $row['class_id'] ?>" data-class-name="<?php echo $row['class_name'] ?>" data-subject="<?php echo $row['subject']?>">Delete</a>
                                        <a href="#" class="get_code" data-class-id="<?php echo $row['class_id'] ?>">Get Code</a>
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
                    echo '<div class="alert alert-info">No classes found for this course.</div>';
                }

                // Close the connection
                $conn->close();
            } else {
                echo '<div class="alert alert-danger">Course ID is missing.</div>';
            }
        ?>

        <!-- Get Code Modal -->
        <div class="modal fade" id="manage_get_code" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Join Code</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <h1 id="modal_code"></h1>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" data-dismiss="modal">Return</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Handle "Get Code" clicks
            document.querySelectorAll('.get_code').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();

                    const classId = this.getAttribute('data-class-id');

                    fetch('generated_code.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'class_id=' + encodeURIComponent(classId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the modal content
                            document.getElementById('modal_class_name').textContent = data.class_name;
                            document.getElementById('modal_subject').textContent = data.subject;
                            document.getElementById('modal_code').textContent = data.code;
                            // Show the modal
                            $('#manage_get_code').modal('show');
                        } else {
                            alert('Error generating code: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('A network error occurred.');
                    });
                });
            });
        </script>
    </body>
</html>
