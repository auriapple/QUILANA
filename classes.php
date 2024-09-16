<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Courses | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
<body>
    <?php include('nav_bar.php') ?>

<div class="content-wrapper">
        <!-- Header Container -->
        <div class="add-course-container">
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
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
                        <div class="meatball-menu-container">
                        <button class="meatball-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                            <div class="meatball-menu">
                                <a href="#" class="edit_course" data-id="<?php echo $row['course_id'] ?>" data-name="<?php echo $row['course_name'] ?>">Edit</a>
                                <a href="#" class="delete_course" data-id="<?php echo $row['course_id'] ?>" data-name="<?php echo $row['course_name'] ?>">Delete</a>
                            </div>
                        </div>
                        <div class="course-card-title"><?php echo $row['course_name'] ?></div>
                        <div class="course-card-text"><?php echo $classCount ?> Class(es)</div>
                        <div class="course-actions">
                            <button id="viewClasses" class="tertiary-button" data-id="<?php echo $row['course_id'] ?>" data-name="<?php echo $row['course_name'] ?>" type="button">Classes</button>
                            <button id="viewCourseDetails" class="main-button" data-id="<?php echo $row['course_id'] ?>" type="button">View Details</button>
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

        <!-- Class Details Modal -->
        <div class="modal fade" id="class_details" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="classDetailsLabel">Class Details</h4>
                        <button type="button" class="close back_vcd_false" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" id="classDetailsBody">
                        <!-- Class details will be dynamically loaded here -->
                    </div>
                    <div class="modal-footer">
                    <div id="back-button-container"></div> <!-- If View Class from the View Course Details is clicked -->
                    <button class="btn btn-secondary back_vcd_false" data-dismiss="modal">Close</button>
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
        
        <!-- Edit Course Modal -->
        <div class="modal fade" id="manage_edit_course" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Edit Course</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id='edit-course-frm'>
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label>Course Name</label>
                                <input type="hidden" name="course_id" id="course_id"/>
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="course_name" required="required" class="form-control" value=""/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Course Modal -->
        <div class="modal fade" id="manage_delete_course" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Delete Course</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id='delete-course-frm'>
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label> Are you sure you want to delete the course: <strong id="modal_course_name"></strong>?</label>
                                <input type="hidden" name="course_id" id="course_id"/>
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                            </div>
                        </div>
                        <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="confirm_delete_btn">Delete</button>
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
                                <label>Section</label>
                                <input type="hidden" name="course_id" />
                                <input type="hidden" name="class_id" />
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="class_name" required="required" placeholder="Course, Year, and Section (ex. BSIT 1-1)" class="form-control" />
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

        <!-- Edit Class Modal -->
        <div class="modal fade" id="manage_edit_class" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Edit Class</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id='edit-class-frm'>
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label>Class Name</label>
                                <input type="hidden" name="class_id" id="class_id"/>
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="class_name" required="required" class="form-control" value=""/>
                                <label>Course Subject</label>
                                <input type="text" name="subject" required="required" class="form-control" value=""/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span>Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Class Modal -->
        <div class="modal fade" id="manage_delete_class" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Delete Class</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id='delete-class-frm'>
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label> Are you sure you want to delete the class: <strong id="modal_class_name"></strong> (<strong id="modal_subject"></strong>)?</label>
                                <input type="hidden" name="class_id" id="class_id"/>
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                            </div>
                        </div>
                        <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="confirm_delete_btn">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                            <h3><a id="modal_class_name"></a> (<a id="modal_subject"></a>)</h3>
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
        $(document).ready(function() {
            // Initialize meatball menu
            initializeMeatballMenu();

            // Show the appropriate button based on the active tab
            function updateButtons() {
                var activeTab = $('.tab-link.active').data('tab');
                // Hide both buttons initially
                $('#addCourse').hide();
                $('#addClass').hide();

                if (activeTab === 'courses-tab') {
                    $('#addCourse').show();
                } else if (activeTab === 'classes-tab') {
                    $('#addClass').show();
                }
            }

            // Hide Classes tab link initially
            $('#classes-tab-link').hide();

            // Handle tab click for courses tab
            $('.tab-link').click(function() {
                var tabId = $(this).data('tab');

                if (tabId === 'courses-tab') {
                    $('#classes-tab-link').hide(); // Hide the Classes tab when Courses tab is clicked
                }

                $('.tab-link').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $('#' + tabId).addClass('active');

                updateButtons();

                // For Meatball Menu to load
                updateMeatballMenu();
            });

            // When add new course button is clicked
            $('#addCourse').click(function() {
                $('#msg').html('');
                $('#manage_course .modal-title').html('Add New Course');
                $('#manage_course #course-frm').get(0).reset();
                $('#manage_course').modal('show');
            });

            // When edit button (course) is clicked
            $(document).on('click', '.edit_course', function() {
                var courseId = $(this).data('id');
                var courseName = $(this).data('name');

                $('#msg').html('');
                $('#manage_edit_course .modal-title').html('Edit Course');
                $('#manage_edit_course #edit-course-frm').get(0).reset();
                $('#manage_edit_course #course_id').val(courseId);
                $('#manage_edit_course input[name="course_name"]').val(courseName);
                $('#manage_edit_course').modal('show');
            });

                //When delete button is clicked
                $('.delete_course').click(function() {
                    var courseId = $(this).data('id');
                    var courseName = $(this).data('name');

                    // Open a modal for deleting
                    $('#msg').html('');
                    $('#manage_delete_course .modal-title').html('Delete Course');
                    $('#manage_delete_course #delete-course-frm').get(0).reset();
                    $('#manage_delete_course #course_id').val(courseId);
                    $('#modal_course_name').text(courseName);
                    $('#manage_delete_course').modal('show');
                });

            // When add new class button is clicked
            $('#addClass').click(function() {
                $('#msg').html('');
                $('#manage_class .modal-title').html('Add New Class');
                $('#manage_class #class-frm').get(0).reset();
                $('#manage_class').modal('show');
            });

            // When edit button (class) is clicked
            $(document).on('click', '.edit_class', function() {
                var classId = $(this).data('class-id');
                var className = $(this).data('class-name');
                var subject = $(this).data('subject');

                $('#msg').html('');
                $('#manage_edit_class .modal-title').html('Edit Class');
                $('#manage_edit_class #edit-class-frm').get(0).reset();
                $('#manage_edit_class #class_id').val(classId);
                $('#manage_edit_class input[name="class_name"]').val(className);
                $('#manage_edit_class input[name="subject"]').val(subject);
                $('#manage_edit_class').modal('show');
            });

            //When delete button (class) is clicked
            $(document).on('click', '.delete_class', function() {
                    var classId = $(this).data('class-id');
                    var className = $(this).data('class-name');
                    var subject = $(this).data('subject');

                    //Open a modal for deleting
                    $('#msg').html('');
                    $('#manage_delete_class .modal-title').html('Delete Course');
                    $('#manage_delete_class #delete-class-frm').get(0).reset();
                    $('#manage_delete_class #class_id').val(classId);
                    $('#modal_class_name').text(className);
                    $('#modal_subject').text(subject);
                    $('#manage_delete_class').modal('show');
                });

                $(document).on('click', '.get_code', function() {
                    var classId = $(this).data('class-id');
                    var className = $(this).data('class-name');
                    var subject = $(this).data('subject');

                    $('#msg').html('');
                    $('#manage_get_code .modal-title').html('Join Code');
                    $('#manage_get_code #modal_class_name').text(className);
                    $('#manage_get_code #modal_subject').text(subject);

                    // Fetch the code dynamically using AJAX
                    $.ajax({
                        url: 'generated_code.php',
                        type: 'POST',
                        data: { classId: classId }, 
                        success: function(response) {
                            $('#modal_code').text(response);
                            $('#manage_get_code').modal('show');
                        },
                        error: function(xhr, status, error) {
                            $('#modal_code').text('Error fetching code. Please try again.');
                            console.error('Error:', error);
                        }
                    });
                });

            // Handle Edit Form (Course)
            $('#edit-course-frm').submit(function(event) {
                event.preventDefault();

                $.ajax({
                    url: './save_editted_course.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            alert('Course saved successfully.');
                            $('#manage_edit_course').modal('hide');
                            location.reload();
                        } else {
                            alert('Failed to save course: ' + response.msg);
                        }
                    },
                    error: function() {
                        alert('An error occurred while saving course details.');
                    }
                });
            });

            // Handle Delete Form (Course)
            $('#delete-course-frm').submit(function(event) {
                event.preventDefault();

                $.ajax({
                    url: 'delete_course.php', 
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            alert('Course deleted successfully.');
                            $('#manage_delete_course').modal('hide');
                            location.reload(); // Reload the page to see the updated course list
                        } else {
                            alert('Failed to delete course: ' + response.msg);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while deleting the course.');
                    }
                });
            });


            // Handle Edit Form (Class)
            $('#edit-class-frm').submit(function(event) {
                event.preventDefault();

                $.ajax({
                    url: './save_editted_class.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            alert('Class saved successfully.');
                            $('#manage_edit_class').modal('hide');
                            location.reload();
                        } else {
                            alert('Failed to save class: ' + response.msg);
                        }
                    },
                    error: function() {
                        alert('An error occurred while saving class details.');
                    }
                });
            });

             // Handle Delete Form (Class)
             $('#delete-class-frm').submit(function(event) {
                    event.preventDefault();

                    $.ajax({
                        url: './delete_class.php', 
                        method: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 1) {
                                alert('Class deleted successfully.');
                                $('#manage_delete_class').modal('hide');
                                location.reload(); // Reload the page to see the updated class list
                            } else {
                                alert('Failed to delete class: ' + response.msg);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log("Request failed: " + textStatus + ", " + errorThrown);
                            alert('An error occurred while deleting the class.');
                        }
                    });
                });

            // View course details button
            $(document).on('click', '#viewCourseDetails', function() {
                var course_id = $(this).attr('data-id');
                $.ajax({
                    url: 'get_course_details.php',
                    method: 'GET',
                    data: { course_id: course_id },
                    success: function(response) {
                        $('#courseDetailsBody').html(response);
                        $('#course_details').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while fetching course details.');
                    }
                });
            });

            // View class details button
            $(document).on('click', '#viewClassDetails', function() {
                var class_id = $(this).attr('data-id');
                $.ajax({
                    url: 'get_class_details.php',
                    method: 'GET',
                    data: { class_id: class_id },
                    success: function(response) {
                        $('#classDetailsBody').html(response);
                        $('#class_details').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while fetching class details.');
                    }
                });
            });
            
            let isButtonClicked = false;
            
            // View Course Details Action Button: View Class
            $(document).on('click', '.action_vcd', function() {
                isButtonClicked = true;

                $('#course_details').modal('hide');

                $('#course_details').one('hidden.bs.modal', function() {
                    $('#class_details').modal('show');
                });
            });

            // View Class Details: Back Button
            $(document).on('click', '.back_vcd', function() {
                isButtonClicked = false;
                
                $('#class_details').modal('hide');

                $('#class_details').one('hidden.bs.modal', function() {
                    $('#course_details').modal('show');
                });
            });

            // To make sure that the isButtonClicked false after exiting the Class Details
            $(document).on('click', '.back_vcd_false', function() {
                isButtonClicked = false;
            });
            
            // When the next modal is shown, check the boolean value
            $('#class_details').on('shown.bs.modal', function() {
                if (isButtonClicked) {
                    $('#back-button-container').html('<button class="btn btn-secondary back_vcd" data-dismiss="modal">Back</button>');
                } else {
                    $('#back-button-container').html('');
                }
            });

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
            $('#viewClasses').click(function() {
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
                        updateMeatballMenu();
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
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 1) {
                            alert(response.msg);
                            
                            var course_id = $('input[name="course_id"]').val();
                            // Fetch and display the updated classes
                            $.ajax({
                                url: 'get_classes.php',
                                method: 'POST',
                                data: { course_id: course_id },
                                success: function(response) {
                                    $('#class-container').html(response);
                                    $('#manage_class').modal('hide');
                                    updateMeatballMenu();
                                    location.reload();
                                }
                            });
                        } else {
                            alert(response.msg);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while saving the class.');
                    }
                });
            });

            function initializeMeatballMenu() {
            console.log("Meatball menu initialized");

            // Ensure the click event is bound to dynamically loaded elements
            $(document).on('click', '.meatball-menu-btn', function(event) {
                event.stopPropagation();
                $('.meatball-menu-container').not($(this).parent()).removeClass('show');
                $(this).parent().toggleClass('show');
            });

            // Close the menu if clicked outside
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.meatball-menu-container').length) {
                    $('.meatball-menu-container').removeClass('show');
                }
            });
        }

        function updateMeatballMenu() {
            // Remove any existing open menus
            $('.meatball-menu-container').removeClass('show');
        }

            // Check if the URL contains `show_modal=true`
            const urlParams = new URLSearchParams(window.location.search);
            const showModal = urlParams.get('show_modal');
            const classId = urlParams.get('class_id');

            // If `show_modal` is true, open the class details modal
            if (showModal === 'true' && classId) {
                // Show the modal
                $('#class_details').modal('show');

                // Fetch class details dynamically
                fetchClassDetails(classId);
            }

            function fetchClassDetails(classId) {
                $.ajax({
                    url: 'get_class_details.php',
                    type: 'GET',
                    data: { class_id: classId },
                    success: function (response) {
                        // Load the response into the modal body
                        $('#classDetailsBody').html(response);
                    },
                    error: function () {
                        $('#classDetailsBody').html('<p>Error loading class details.</p>');
                    }
                });
            }

            // Ensure meatball menu is initialized after any dynamic content changes
            $(document).ajaxComplete(function() {
                updateMeatballMenu();
            });
        });
        </script>
    </body>
</html>