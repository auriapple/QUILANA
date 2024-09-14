<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Courses | Quilana</title>
</head>
<body>
    <?php include('nav_bar.php') ?>
    <div class="container-fluid admin">
        <!-- Header Container -->
        <div class="header-container">
            <button class="secondary-button" id="joinClass">Join Class</button>
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="classes-tab">Classes</li>
                <li class="tab-link" id="class-name-tab" data-tab="assessments-tab" style="display: none;"></li>
            </ul>
        </div>

        <!-- Classes Tab -->
        <div id="classes-tab" class="tab-content active">
            <div class="course-container">
                <?php
                $student_id = $_SESSION['login_id'];

                // Fetch student's enrolled classes
                $enrolled_classes_query = $conn->query("SELECT c.class_id, c.subject, f.firstname, f.lastname 
                                                        FROM student_enrollment e
                                                        JOIN class c ON e.class_id = c.class_id
                                                        JOIN faculty f ON c.faculty_id = f.faculty_id
                                                        WHERE e.student_id = '$student_id' AND e.status = '1'");

                while ($row = $enrolled_classes_query->fetch_assoc()) {
                ?>

                <!-- Display class details -->
                <div class="course-card">
                    <div class="course-card-title"><?php echo $row['subject'] ?></div>
                    <div class="course-card-text">Professor: <?php echo $row['firstname'] . ' ' . $row['lastname'] ?></div>
                    <div class="course-actions">
                        <button id="viewClassDetails_<?php echo $row['class_id']; ?>" class="main-button" data-id="<?php echo $row['class_id'] ?>" type="button">View Class</button>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <!-- Assessments Tab -->
        <div id="assessments-tab" class="tab-content">
            <div id="course-container">

                <!-- If a class is selected, assessments are loaded here -->
                <?php
                if (isset($_GET['class_id'])) {
                    $class_id = $_GET['class_id'];
                    include('load_assessment.php');
                }
                ?>
            </div>
        </div>

        <!-- Modal for entering class code to join a class-->
        <div class="modal fade" id="manage_class" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Join Class</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>

                    <!-- Form to submit the class code -->
                    <form id='code-frm' action="" method="POST">
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <input type="text" name="get_code" required="required" class="form-control" placeholder="Class Code" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" name="join_by_code"><span class="glyphicon glyphicon-save"></span>Join</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Button Visibility
            function updateButtons() {
                var activeTab = $('.tab-link.active').data('tab');
        
                if (activeTab === 'assessments-tab') {
                    $('#join_class').hide();
                } else {
                    $('#join_class').show();
                }
            }
            
            // Tab Functionality
            $('.tab-link').click(function() {
                var tab_id = $(this).attr('data-tab');
                $('.tab-link').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $("#" + tab_id).addClass('active');

                // If the "Classes" tab is clicked, hide the assessment tab
                if (tab_id === 'classes-tab') {
                    $('#class-name-tab').hide();
                    $('#assessments-tab').removeClass('active').empty(); // Optionally empty the content
                }
                updateButtons();
            });

            // Initialize button visibility
            updateButtons();

            // Join Class
            $('#joinClass').click(function() {
                $('#msg').html('');
                $('#manage_class #code-frm').get(0).reset();
                $('#manage_class').modal('show');
            });

            // View Class Details
            $('[id^=viewClassDetails_]').click(function() {
                var class_id = $(this).data('id');
                var class_name = $(this).closest('.course-card').find('.course-card-title').text();

                // Change the tab title to the class name
                $('#class-name-tab').text(class_name).show();

                // Switch to the new tab
                $('.tab-link').removeClass('active');
                $('.tab-content').removeClass('active');
                $('#class-name-tab').addClass('active');
                $('#assessments-tab').addClass('active');

                // Load the assessments for the selected class
                $.ajax({
                    type: 'POST',
                    url: 'load_assessments.php',
                    data: { class_id: class_id },
                    success: function(response) {
                        $('#assessments-tab').html(response);
                    }
                });
                updateButtons();
            });

            // Handles code submission
            $('#code-frm').submit(function(event) {
                event.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: 'join_class.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        var result = JSON.parse(response);
                        
                        if (result.status === 'success') {
                            $('#msg').html('<div class="alert alert-success">' +
                                            'Enrollment request sent! Please wait for approval.' +
                                            '</div>');
                        } else {
                            $('#msg').html('<div class="alert alert-danger">' + result.message + '</div>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while saving the course.');
                    }
                });
            });
        });
    </script>
</body>
</html>