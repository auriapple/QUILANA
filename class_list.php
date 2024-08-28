<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Courses | Quialana</title>
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="container-fluid admin">
        <div class="add-course-container">
            <button class="btn btn-primary btn-sm add-btn" id="add_course" style="display:none;"><i class="fa fa-plus"></i> Add Course</button>
            <button class="btn btn-primary btn-sm add-btn" id="add_class" style="display:none;"><i class="fa fa-plus"></i> Add Class</button>
            <div class="search-bar">
                <form action="search_courses.php" method="GET">
                    <input type="text" name="query" placeholder="Search" required>
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="courses-tab">Courses</li>
                <li class="tab-link" id="classes-tab-link" style="display: none;" data-tab="classes-tab">Classes</li>
            </ul>
        </div>

        <div id="courses-tab" class="tab-content active">
            <div class="course-container">
                <?php
                $qry = $conn->query("SELECT * FROM course WHERE faculty_id = '".$_SESSION['login_id']."' ORDER BY course_name ASC");
                if ($qry->num_rows > 0) {
                    while ($row = $qry->fetch_assoc()) {
                        $course_id =  $row['course_id'];
                        $result = $conn->query("SELECT COUNT(*) as classCount FROM class WHERE course_id = '$course_id'");
                        $classCountRow = $result->fetch_assoc();
                        $classCount = $classCountRow['classCount'];
                ?>
                <div class="course-card">
                    <div class="course-card-body">
                        <div class="course-card-title"><?php echo $row['course_name'] ?></div>
                        <div class="course-card-text"><?php echo $classCount ?> Class(es)</div>
                        <div class="course-actions">
                            <button class="btn btn-outline-primary btn-sm classes" data-id="<?php echo $row['course_id'] ?>" data-name="<?php echo $row['course_name'] ?>" type="button">Classes</button>
                            <button class="btn btn-primary btn-sm view_course_details" data-id="<?php echo $row['course_id'] ?>" type="button">View Details</button>
                        </div>
                    </div>
                </div>
                <?php
                    }
                }
                ?>
            </div>
        </div>

        <div id="classes-tab" class="tab-content">
            <div class="course-container" id="class-container">
                <!-- Classes will be dynamically loaded here -->
            </div>
        </div>

        <!-- Course Details Modal -->
        <div class="modal fade" id="course_details" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="courseDetailsLabel">Course Details</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" id="courseDetailsBody">
                        <!-- Course details will be dynamically loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Course Modal -->
        <div class="modal fade" id="manage_course" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Add New Course</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id='course-frm'>
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label>Course Name</label>
                                <input type="hidden" name="course_id" />
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="course_name" required="required" class="form-control" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Manage Class Modal -->
        <div class="modal fade" id="manage_class" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Add New Class</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id='class-frm'>
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label>Class Name</label>
                                <input type="hidden" name="course_id" />
                                <input type="hidden" name="class_id" />
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="class_name" required="required" class="form-control" />
                                <label>Year</label>
                                <input type="number" name="year" required="required" class="form-control" />
                                <label>Section</label>
                                <input type="text" name="section" required="required" class="form-control" />
                                <label>Course Subject</label>
                                <input type="text" name="subject" required="required" class="form-control" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // Show the appropriate button based on the active tab
                function updateButtons() {
                    var activeTab = $('.tab-link.active').data('tab');
                    $('.add-btn').hide(); // Hide all buttons initially

                    if (activeTab === 'courses-tab') {
                        $('#add_course').show();
                    } else if (activeTab === 'classes-tab') {
                        $('#add_class').show();
                    }
                }

                // Handle tab switching
                $('.tab-link').click(function() {
                    var tab_id = $(this).attr('data-tab');

                    // Remove active class from all tabs and content
                    $('.tab-link').removeClass('active');
                    $('.tab-content').removeClass('active');

                    // Add active class to the clicked tab and corresponding content
                    $(this).addClass('active');
                    $("#" + tab_id).addClass('active');

                    // Update buttons visibility
                    updateButtons();
                });

                // Show the correct button when the page loads
                updateButtons();

                // When add new course button is clicked
                $('#add_course').click(function() {
                    $('#msg').html('');
                    $('#manage_course .modal-title').html('Add New Course');
                    $('#manage_course #course-frm').get(0).reset();
                    $('#manage_course').modal('show');
                });

                // When add new class button is clicked
                $('#add_class').click(function() {
                    $('#msg').html('');
                    $('#manage_class .modal-title').html('Add New Class');
                    $('#manage_class #class-frm').get(0).reset();
                    $('#manage_class').modal('show');
                });

                        // View course details button
                $(document).on('click', '.view_course_details', function() {
                    var course_id = $(this).attr('data-id');
                    $.ajax({
                        url: 'get_course_details.php',
                        method: 'GET',
                        data: { course_id: course_id },
                        success: function(response) {
                            $('#courseDetailsBody').html(response);
                            $('#course_details').modal('show');
                        },
                        error: function() {
                            alert('An error occurred while fetching course details.');
                        }
                    });
                });

                // Saving new course
                $('#course-frm').submit(function(e) {
                    e.preventDefault();
                    $('#course-frm [name="save"]').attr('disabled', true).html('Saving...');
                    $('#msg').html('');

                    $.ajax({
                        url: './save_course.php',
                        method: 'POST',
                        data: $(this).serialize(),
                        error: function(err) {
                            console.log(err);
                            alert('An error occurred');
                            $('#course-frm [name="save"]').removeAttr('disabled').html('Save');
                        },
                        success: function(resp) {
                            if (typeof resp != undefined) {
                                resp = JSON.parse(resp);
                                if (resp.status == 1) {
                                    alert('Data successfully saved');
                                    location.reload();
                                } else {
                                    $('#msg').html('<div class="alert alert-danger">' + resp.msg + '</div>');
                                }
                            }
                        }
                    });
                });
                
                // Handle Classes button click
                $('.classes').click(function() {
                    var course_id = $(this).attr('data-id');
                    var course_name = $(this).attr('data-name');

                    // Show the Classes tab and set the course name
                    $('#classes-tab-link').show().click();
                    $('#classes-tab-link').text(course_name);

                    // Fetch and display classes associated with the course
                    $.ajax({
                        url: 'get_classes.php',
                        method: 'POST',
                        data: { course_id: course_id },
                        success: function(response) {
                            $('#class-container').html(response);
                        }
                    });

                    // Set the hidden course_id field in the add class form
                    $('#manage_class input[name="course_id"]').val(course_id);
                });

                // AJAX form submission for adding a class
                $('#class-frm').submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: 'save_class.php',
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            var course_id = $('input[name="course_id"]').val();
                            // Fetch and display the updated classes
                            $.ajax({
                                url: 'get_classes.php',
                                method: 'POST',
                                data: { course_id: course_id },
                                success: function(response) {
                                    $('#class-container').html(response);
                                    $('#manage_class').modal('hide');
                                }
                            });
                        }
                    });
                });
            });
        </script>
    </body>
</html>
