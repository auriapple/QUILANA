<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <?php include('auth.php'); ?>
    <?php include('db_connect.php'); ?>
    <title>Assessments | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .course-section {
            margin-bottom: 30px;
        }

        .course-section h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .subject-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .subject-header span {
            font-size: 1.1rem;
            font-weight: lighter;
            color: gray;
            margin-right: 10px;
        }

        .subject-header .line {
            flex: 1;
            border-bottom: 1.5px solid gray;
        }

        .course-cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* Course Actions */
        .assessment-actions {
            display: flex;
            justify-content: space-between;
        }

        .assessment-actions .btn {
            font-size: 14px;
            padding: 5px 15px;
           
        }

        .scrollable-content {
            max-height: 85vh; 
            overflow-y: auto;
            padding-right: 15px; 
        }

        body {
            overflow: hidden;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="container-fluid admin">
        <div class="scrollable-content">
            <div class="add-course-container">
                <button class="btn btn-primary btn-sm add-btn" id="add_assessment"><i class="fa fa-plus"></i> Add Assessment</button>
                <div class="search-bar">
                    <form id="search-form" method="GET">
                        <input type="text" name="query" placeholder="Search Course" required>
                        <button type="submit">Search</button>
                    </form>
                </div>
            </div>

            <div class="tabs-container">
                <ul class="tabs">
                    <li class="tab-link active" data-tab="assessment-tab">Assessments</li>
                    <li class="tab-link" id="details-tab-link" style="display: none;" data-tab="details-tab">Assessment Details</li>
                </ul>
            </div>

            <div id="assessment-tab" class="tab-content active">
                <?php
                $qry = $conn->query("
                    SELECT a.*, c.course_name
                    FROM assessment a 
                    JOIN course c ON a.course_id = c.course_id 
                    WHERE a.faculty_id = '".$_SESSION['login_id']."'
                    ORDER BY c.course_name, a.subject, a.assessment_name ASC
                ");
                
                $current_course = '';
                $current_subject = '';

                while ($row = $qry->fetch_assoc()) {
                    $course_name = htmlspecialchars($row['course_name']);
                    $subject_name = htmlspecialchars($row['subject']);
                    $assessment_name = htmlspecialchars($row['assessment_name']);
                    $topic = htmlspecialchars($row['topic']);
                    $assessment_id = $row['assessment_id'];
                ?>
                
                <?php if ($course_name !== $current_course) { ?>
                    <?php if ($current_course !== '') { ?></div><?php } ?>
                    <div class="course-section">
                        <h2><?php echo $course_name; ?></h2>
                <?php 
                    $current_course = $course_name;
                    $current_subject = '';
                } ?>

                <?php if ($subject_name !== $current_subject) { ?>
                    <?php if ($current_subject !== '') { ?></div><?php } ?>
                    <div class="subject-header">
                        <span><?php echo $subject_name; ?></span>
                        <div class="line"></div>
                    </div>
                    <div class="course-cards-container">
                <?php 
                    $current_subject = $subject_name;
                } ?>

                <div class="course-card">
                    <div class="course-card-body">
                        <div class="meatball-menu-container">
                            <button class="meatball-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="meatball-menu">
                                <a href="#" class="edit_assessment" data-id="<?php echo $assessment_id ?>">Edit</a>
                                <a href="#" class="delete_assessment" data-id="<?php echo $assessment_id ?>">Delete</a>
                            </div>
                        </div>
                        <div class="course-card-title"><?php echo $assessment_name; ?></div>
                        <div class="course-card-text"><br>Topic: <br><?php echo $topic; ?></div>
                        <div class="course-actions">
                            <a class="btn btn-sm btn-outline-primary view_assessment_details" 
                            href="manage_assessment.php?assessment_id=<?php echo $assessment_id ?>"> Manage</a>
                            <button class="btn btn-primary btn-sm administer" 
                                data-course-id="<?php echo $row['course_id']; ?>" 
                                data-course-name="<?php echo $row['course_name']; ?>" 
                                data-subject="<?php echo htmlspecialchars($row['subject']); ?>" 
                                data-mode="<?php echo htmlspecialchars($row['assessment_mode']); ?>" 
                                data-id="<?php echo $row['assessment_id']; ?>">Administer</button>
                        </div>
                    </div>
                </div>

                <?php } ?>
                </div> <!-- Close the last subject card container -->
                </div> <!-- Close the last course section -->
            </div>


            <div id="details-tab" class="tab-content">
                
            </div>

        <!-- Modal for managing assessments -->
        <div class="modal fade" id="manage_assessment" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Add New Assessment</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="assessment-frm">
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label>Assessment Name</label>
                                <input type="hidden" name="assessment_id" />
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="assessment_name" required="required" class="form-control" />
                                <label>Assessment Type</label>
                                <select name="assessment_type" id="assessment_type" required="required" class="form-control">
                                    <option value="1">Quiz</option>
                                    <option value="2">Exam</option>
                                </select>
                                <label>Assessment Mode</label>
                                <select name="assessment_mode" id="assessment_mode" required="required" class="form-control">
                                    <option value="1">Normal Mode</option>
                                    <option value="2">Quiz Bee Mode</option>
                                    <option value="3">Speed Mode</option>
                                </select>
                                <label>Select Course</label>
                                <select name="course_id" id="course_id" required="required" class="form-control">
                                    <option value="">Select Course</option>
                                    <?php
                                    $course_qry = $conn->query("SELECT * FROM course WHERE faculty_id = '".$_SESSION['login_id']."'");
                                    while($course_row = $course_qry->fetch_assoc()) {
                                        echo "<option value='".$course_row['course_id']."'>".$course_row['course_name']."</option>";
                                    }
                                    ?>
                                </select>
                                <label>Select Course Subject</label>
                                <select name="subject" id="subject" required="required" class="form-control">
                                    <option value="">Select Subject</option>
                                </select>
                                <label>Topic</label>
                                <input type="text" name="topic" required="required" class="form-control" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal for administering assessments -->
        <div class="modal fade" id="administer_assessment_modal" tabindex="-1">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="administerAssessmentLabel">Administer Assessment</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="administer-assessment-frm">
                    <input type="hidden" name="assessment_id" id="assessment_id_hidden" />
                    <input type="hidden" name="course_id" id="course_id_hidden" />
                        <div class="modal-body">
                            <div id="msg1"></div>
                            <div class="form-group">
                                <label for="administer_course">Course</label>
                                <input type="text" id="administer_course" class="form-control" readonly />
                            </div>
                            <div class="form-group">
                                <label for="administer_subject">Subject</label>
                                <input type="text" id="administer_subject" class="form-control" readonly />
                            </div>
                            <div class="form-group">
                                <label for="administer_mode">Mode</label>
                                <select id="administer_mode" class="form-control" disabled>
                                    <option value="1">Normal Mode</option>
                                    <option value="2">Quiz Bee Mode</option>
                                    <option value="3">Speed Mode</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="administer_class_id">Select Class</label>
                                <select name="class_id" id="administer_class_id" required class="form-control">
                                    <option value="">Select Class</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button id="administer_btn" type="submit" class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span> Administer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Assessment Modal -->
        <div class="modal fade" id="edit_assessment_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editModalLabel">Edit Assessment</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="edit-assessment-frm">
                        <div class="modal-body">
                            <div id="edit-msg"></div>
                            <div class="form-group">
                                <label>Assessment Name</label>
                                <input type="hidden" name="assessment_id" id="edit_assessment_id" />
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="assessment_name" id="edit_assessment_name" required="required" class="form-control" />
                                <label>Assessment Type</label>
                                <select name="assessment_type" id="edit_assessment_type" required="required" class="form-control">
                                    <option value="1">Quiz</option>
                                    <option value="2">Exam</option>
                                </select>
                                <label>Assessment Mode</label>
                                <select name="assessment_mode" id="edit_assessment_mode" required="required" class="form-control">
                                    <option value="1">Normal Mode</option>
                                    <option value="2">Quiz Bee Mode</option>
                                    <option value="3">Speed Mode</option>
                                </select>
                                <label>Select Course</label>
                                <select name="course_id" id="edit_course_id" required="required" class="form-control">
                                    <option value="">Select Course</option>
                                    <?php
                                    $course_qry = $conn->query("SELECT * FROM course WHERE faculty_id = '".$_SESSION['login_id']."'");
                                    while($course_row = $course_qry->fetch_assoc()) {
                                        echo "<option value='".$course_row['course_id']."'>".$course_row['course_name']."</option>";
                                    }
                                    ?>
                                </select>
                                <label>Select Course Subject</label>
                                <select name="subject" id="edit_subject" required="required" class="form-control">
                                    <option value="">Select Subject</option>
                                </select>
                                <label>Topic</label>
                                <input type="text" name="topic" id="edit_topic" required="required" class="form-control" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="edit_save_btn" name="save"><span class="glyphicon glyphicon-save"></span> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="delete_assessment_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Delete Assessment</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this assessment?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="confirm_delete_btn">Delete</button>
                    </div>
                </div>
            </div>
        </div>


            <script>
          $(document).ready(function() {
            // Show modal when "Add Assessment" is clicked
            $('#add_assessment').click(function() {
                $('#manage_assessment').modal('show');
            });
            
            // Handle assessment type change
            $('#assessment_type').change(function() {
                var type = $(this).val();
                if (type == '2') { // Exam
                    $('#assessment_mode').val('1').change(); // Set to Normal Mode
                    $('#assessment_mode').find('option').not('[value="1"]').hide(); // Hide all other modes
                } else {
                    $('#assessment_mode').find('option').show(); // Show all modes
                }
            });

            // Load subjects based on selected course
            $('#course_id').change(function() {
                var course_id = $(this).val();
                if (course_id) {
                    $.ajax({
                        url: 'get_subjects.php',
                        method: 'POST',
                        data: { course_id: course_id },
                        success: function(response) {
                            $('#subject').html(response); //  subjects dropdown
                        }
                    });
                } else {
                    $('#subject').html('<option value="">Select Subject</option>'); // Clear subjects dropdown
                }
            });

            $('#assessment-frm').submit(function(e){
                e.preventDefault(); // Prevent the default form submission
                $.ajax({
                    url: 'save_assessment.php', 
                    method: 'POST', // Use POST to send form data
                    data: $(this).serialize(), // Serialize form data
                    success: function(resp){
                        if(resp == 1){
                            alert('Assessment successfully added');
                            location.reload(); // Refresh the page to show the newly added assessment
                        } else {
                            $('#msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                        }
                    }
                });
            });


                // Show modal when "Administer Assessment" is clicked
                $(document).on('click', '.administer', function() {
                    var assessmentId = $(this).data('id');      // Get the assessment ID
                    var courseId = $(this).data('course-id');   // Get the course ID
                    var courseName = $(this).data('course-name'); // Get the course name
                    var subjectName = $(this).data('subject');  // Get the subject name
                    var mode = $(this).data('mode');            // Get the assessment mode

                    // Check if the assessment has questions
                    $.ajax({
                        url: 'check_questions.php', // PHP file to check questions
                        method: 'POST',
                        data: { assessment_id: assessmentId },
                        success: function(response) {
                            if (response.trim() === 'no_questions') {
                                alert('Cannot administer this assessment. No questions have been added yet.');
                            } else {
                                // Set the hidden fields
                                $('#assessment_id_hidden').val(assessmentId);
                                $('#course_id_hidden').val(courseId);

                                // Set other fields
                                $('#administer_course').val(courseName); // Display course name
                                $('#administer_subject').val(subjectName);
                                $('#administer_mode').val(mode);

                                // Load classes based on selected course and subject
                                if (courseId && subjectName) {
                                    $.ajax({
                                        url: 'administer_class.php',
                                        method: 'POST',
                                        data: { course_id: courseId, subject: subjectName },
                                        success: function(response) {
                                            $('#administer_class_id').html(response); // Populate classes dropdown
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('AJAX Error:', status, error);
                                        }
                                    });
                                } else {
                                    $('#administer_class_id').html('<option value="">Select Class</option>'); // Clear classes dropdown
                                }


                                // Show the modal
                                $('#administer_assessment_modal').modal('show');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                });

            });
            // Handle administer form submission
            $('#administer-assessment-frm').submit(function (e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: 'administer_assessment.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json', // Expect JSON response
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#msg1').html('<div class="alert alert-success">' + response.message + '</div>');
                            $('#administer_btn').prop('disabled', true).text('Administered'); // Disable the button

                        } else {
                            $('#msg1').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#msg1').html('<div class="alert alert-danger">Failed to administer. Please try again.</div>');
                    }
                });

                $('#administer_assessment_modal').on('hide.bs.modal', function () {
                    $('#msg1').empty();
                });
            });

            // Toggle the meatball menu visibility when the button is clicked
            $(document).on('click', '.meatball-menu-btn', function(e) {
                e.stopPropagation(); // Prevent click from bubbling up
                var $menu = $(this).siblings('.meatball-menu');
                $('.meatball-menu').not($menu).hide(); // Hide other open meatball menus
                $menu.toggle(); // Toggle the current menu
            });

            // Close the meatball menu if clicking outside of it
            $(document).click(function() {
                $('.meatball-menu').hide(); // Hide all menus when clicking outside
            });

            // Prevent the menu from closing when clicking inside the menu
            $(document).on('click', '.meatball-menu', function(e) {
                e.stopPropagation();
            });

            // Edit button functionality
            $(document).on('click', '.edit_assessment', function() {
                var assessmentId = $(this).data('id');
                
                $.ajax({
                    url: 'get_assessment.php',
                    method: 'POST',
                    data: { assessment_id: assessmentId },
                    dataType: 'json',
                    success: function(data) {
                        // Populate the edit modal fields with the assessment data
                        $('#edit_assessment_id').val(data.assessment_id);
                        $('#edit_assessment_name').val(data.assessment_name);
                        $('#edit_assessment_type').val(data.assessment_type);
                        $('#edit_assessment_mode').val(data.assessment_mode);
                        $('#edit_course_id').val(data.course_id);
                        $('#edit_topic').val(data.topic);
                        $('#edit_subject').val(data.subject);
                        
                        // Populate subjects dropdown
                        $.ajax({
                            url: 'get_subjects.php',
                            method: 'POST',
                            data: { course_id: data.course_id },
                            success: function(response) {
                                $('#edit_subject').html(response);
                                $('#edit_subject').val(data.subject); // Set the selected subject
                            }
                        });

                        // Ensure assessment mode is set to Normal Mode if type is Exam
                        if (data.assessment_type == '2') { // Exam
                            $('#edit_assessment_mode').val('1'); // Set to Normal Mode
                            $('#edit_assessment_mode').find('option').not('[value="1"]').hide(); // Hide other modes
                        } else {
                            $('#edit_assessment_mode').find('option').show(); // Show all modes
                        }

                        // Show the edit modal
                        $('#edit_assessment_modal').modal('show');
                    }
                });
            });

            // Handle assessment type change in the edit form
            $('#edit_assessment_type').change(function() {
                var type = $(this).val();
                if (type == '2') { // Exam
                    $('#edit_assessment_mode').val('1').change(); // Set to Normal Mode
                    $('#edit_assessment_mode').find('option').not('[value="1"]').hide(); // Hide all other modes
                } else {
                    $('#edit_assessment_mode').find('option').show(); // Show all modes
                }
            });

            // Save the edited assessment
            $('#edit-assessment-frm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: 'save_edit_assessment.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response == 1) {
                            alert('Assessment successfully updated');
                            location.reload(); // Reload the page to show updated data
                        } else {
                            $('#edit-msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                        }
                    }
                });
            });

            // Delete button functionality
            $(document).on('click', '.delete_assessment', function() {
                var assessmentId = $(this).data('id');
                $('#confirm_delete_btn').data('id', assessmentId); // Set assessment ID on confirm button
                $('#delete_assessment_modal').modal('show'); // Show confirmation modal
            });

            // Confirm delete action
            $('#confirm_delete_btn').click(function() {
                var assessmentId = $(this).data('id');

                $.ajax({
                    url: 'delete_assessment.php',
                    method: 'POST',
                    data: { assessment_id: assessmentId },
                    success: function(response) {
                        if (response == 1) {
                            alert('Assessment successfully deleted');
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Error: Unable to delete the assessment.');
                        }
                    }
                });
            });

            </script>
        </div>
    </div>
</body>
</html>