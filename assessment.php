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
                    SELECT a.*, c.course_name, cl.subject 
                    FROM assessment a 
                    JOIN class cl ON a.class_id = cl.class_id 
                    JOIN course c ON cl.course_id = c.course_id 
                    WHERE a.faculty_id = '".$_SESSION['login_id']."' 
                    ORDER BY c.course_name, cl.subject, a.assessment_name ASC
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
                                <span class="dot"></span>
                                <span class="dot"></span>
                                <span class="dot"></span>
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
                                data-course="<?php echo $row['course_id']; ?>" 
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
                                <select name="class_id" id="class_id" required="required" class="form-control">
                                    <option value="">Select Subject</option>
                                </select>
                                <label>Topic</label>
                                <input type="text" name="topic" required="required" class="form-control" />
                                <label>Time Limit (in minutes)</label>
                                <input type="number" name="time_limit" id="time_limit" class="form-control" required />
                                <small id="time-limit-hint" class="form-text text-muted">
                                    For Normal Mode, this is the total time. For Quiz Bee and Speed Mode, this is the time per question.
                                </small>
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
                        <div class="modal-body">
                            <div id="msg"></div>
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
                            <div class="form-group" id="time-limit-group">
                                <label for="time_limit">Time Limit (in minutes)</label>
                                <input type="number" name="time_limit" id="time_limit" class="form-control" required />
                                <small id="time-limit-hint" class="form-text text-muted">
                                    For Normal Mode, this is the total time. For Quiz Bee and Speed Mode, this is the time per question.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="save"><span class="glyphicon glyphicon-save"></span> Administer</button>
                        </div>
                    </form>
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
                            $('#class_id').html(response); // Populate subjects dropdown
                        }
                    });
                } else {
                    $('#class_id').html('<option value="">Select Subject</option>'); // Clear subjects dropdown
                }
            });

            // Update time limit hint based on assessment mode
            $('#assessment_mode').change(function() {
                var mode = $(this).val();
                if (mode == '1') { // Normal Mode
                    $('#time_limit').attr('placeholder', 'Total time for the entire assessment');
                    $('#time-limit-hint').text('For Normal Mode, this is the total time.');
                } else { // Quiz Bee or Speed Mode
                    $('#time_limit').attr('placeholder', 'Time limit per question');
                    $('#time-limit-hint').text('For Quiz Bee and Speed Mode, this is the time per question.');
                }
            });

            // Show modal when "Administer Assessment" is clicked
            $(document).on('click', '.administer', function() {
                var courseId = $(this).data('course');  // Get the course ID
                var subjectName = $(this).data('subject');  // Get the subject name
                var mode = $(this).data('mode');  // Get the assessment mode
                var assessmentId = $(this).data('id');  // Get the assessment ID

                // Set the course, subject, and mode fields in the modal
                $('#administer_course').val(courseId);
                $('#administer_mode').val(mode);
                $('#administer_subject').val(subjectName);

                // Set the hidden assessment ID field in the form
                $('input[name="assessment_id"]').val(assessmentId);

                // Load classes based on selected course and subject
                if (courseId && subjectName) {
                    $.ajax({
                        url: 'administer_class.php',
                        method: 'POST',
                        data: { course_id: courseId, subject: subjectName },
                        success: function(response) {
                            $('#administer_class_id').html(response); // Populate classes dropdown
                        }
                    });
                } else {
                    $('#administer_class_id').html('<option value="">Select Class</option>'); // Clear classes dropdown
                }

                // Show the modal
                $('#administer_assessment_modal').modal('show');
            });

            // Handle administer form submission
            $('#administer-assessment-frm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: 'administer_assessment.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        alert('Assessment administered successfully!');
                        $('#administer_assessment_modal').modal('hide');
                    },
                    error: function() {
                        alert('Error administering assessment.');
                    }
                });
            });
        });
            </script>
        </div>
    </div>
</body>
</html>