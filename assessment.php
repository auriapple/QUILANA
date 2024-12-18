<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <?php include('auth.php'); ?>
    <?php include('db_connect.php'); ?>
    <title>Assessments | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="assets/css/classes.css">
</head>
<div>
    <?php include('nav_bar.php'); ?>

    <div class="content-wrapper"> 

    
        <!-- Header Container -->
        <div class="add-assessment-container">
            <button class="secondary-button" id="addAssessment">Add Assessment</button>
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="assessment-tab">Assessments</li>
                <li class="tab-link" id="details-tab-link" style="display: none;" data-tab="details-tab">Assessment Details</li>
                <li class="tab-link" id="administer-tab-link" style="display: none; white-space: nowrap" data-status="0" data-tab="administer-tab">Administer</li>
            </ul>
        </div>

        <div class="scrollable-content">  
            <div id="assessment-tab" class="tab-content active">
                <?php
                $qry = $conn->query("
                    SELECT a.*, c.course_name
                    FROM assessment a 
                    JOIN course c ON a.course_id = c.course_id 
                    WHERE a.faculty_id = '".$_SESSION['login_id']."'
                    ORDER BY c.course_name ASC, a.subject ASC, a.date_updated DESC
                ");
                
                $current_course = '';
                $current_subject = '';

                if ($qry && $qry->num_rows > 0) {
                    while ($row = $qry->fetch_assoc()) {
                        $course_name = htmlspecialchars($row['course_name']);
                        $subject_name = htmlspecialchars($row['subject']);
                        $assessment_name = htmlspecialchars($row['assessment_name']);
                        $topic = htmlspecialchars($row['topic']);
                        $assessment_id = $row['assessment_id'];
                        
                        if ($course_name !== $current_course) {
                            if ($current_course !== '') { ?>
                                </div> 
                            <?php } ?>
                            <div class="course-section">
                                <h2><?php echo $course_name; ?></h2>
                                <?php 
                                $current_course = $course_name;
                                $current_subject = '';
                            }

                            if ($subject_name !== $current_subject) {
                                if ($current_subject !== '') { ?>
                                    </div>
                                <?php } ?>
                                <div class="content-separator">
                                    <span class="content-name"><?php echo $subject_name; ?></span>
                                    <hr class="separator-line">
                                </div>
                                <div class="assessment-container">
                                <?php 
                                $current_subject = $subject_name;
                            } ?>
                            
                            <div class="assessment-card">
                                <div class="assessment-card-body">
                                    <div class="meatball-menu-container">
                                        <button class="meatball-menu-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="meatball-menu">
                                            <div class="arrow-up"></div>
                                            <a href="#" class="edit_assessment" data-id="<?php echo $assessment_id ?>">
                                                <span class="material-symbols-outlined">Edit</span>
                                                Edit
                                            </a>
                                            <a href="#" class="delete_assessment" 
                                                data-id="<?php echo $assessment_id ?>"
                                                data-name="<?php echo $assessment_name ?>"
                                                data-subject="<?php echo $subject_name ?>">
                                                <span class="material-symbols-outlined">delete</span>
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                    <div class="assessment-card-title"><?php echo $assessment_name; ?></div>
                                    <div class="assessment-card-topic">Topic: <?php echo $topic; ?></div>
                                    <div class="assessment-actions">
                                        <a id="manage" class="tertiary-button" href="manage_assessment.php?assessment_id=<?php echo $assessment_id ?>">Manage</a>
                                        <button id="administer" class="main-button" 
                                            data-course-id="<?php echo $row['course_id']; ?>" 
                                            data-course-name="<?php echo $row['course_name']; ?>" 
                                            data-subject="<?php echo htmlspecialchars($row['subject']); ?>" 
                                            data-mode="<?php echo htmlspecialchars($row['assessment_mode']); ?>" 
                                            data-id="<?php echo $row['assessment_id']; ?>"
                                            data-assessment-name="<?php echo htmlspecialchars($row['assessment_name']); ?>">
                                            Administer
                                        </button>
                                    </div>
                                </div>
                            </div>
                    <?php }
                }  
                else {
                    echo '<div class="no-records" style="grid-column: 1/-1;"> No assessments have been created yet </div>';
                } ?>
                        </div> <!-- Close the last subject card container -->
                    </div> <!-- Close the last course section -->
                </div>
            </div> 
                   
            <div id="details-tab" class="tab-content">
                <h1></h1>
            </div>

            <div id="administer-tab" class="tab-content"> 
                <div id="administer-container">
                </div>
            </div>
        </div>

            <!-- Modal for managing assessments -->
            <div id="add-assessment-popup" class="popup-overlay"> 
                <div id="add-assessment-modal-content" class="popup-content" role="document">
                    <button class="popup-close">&times;</button>
                    <h2 id="add-assessment-title" class="popup-title">Add Assessment</h2>

                    <form id="assessment-form">
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <label>Assessment Name</label>
                                <input type="hidden" name="assessment_id" />
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="assessment_name" required="required" class="popup-input" />
                            </div>
                            <div class="form-group">
                                <label>Assessment Type</label>
                                <select name="assessment_type" id="assessment_type" required="required" class="popup-input">
                                    <option value="1">Quiz</option>
                                    <option value="2">Exam</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assessment Mode</label>
                                <select name="assessment_mode" id="assessment_mode" required="required" class="popup-input">
                                    <option value="1">Normal Mode</option>
                                    <option value="2">Quiz Bee Mode</option>
                                    <option value="3">Speed Mode</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Course</label>
                                <select name="course_id" id="course_id" required="required" class="popup-input">
                                    <option value="" disabled selected>Select Course</option>
                                    <?php
                                    $course_qry = $conn->query("SELECT * FROM course WHERE faculty_id = '".$_SESSION['login_id']."'");
                                    while($course_row = $course_qry->fetch_assoc()) {
                                        echo "<option value='".$course_row['course_id']."'>".$course_row['course_name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Course Subject</label>
                                <select name="subject" id="subject" required="required" class="popup-input">
                                    <option value="" disabled selected>Select Subject</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Topic</label>
                                <input type="text" name="topic" required="required" class="popup-input" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="secondary-button" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal for administering assessments -->
            <div id="administer-assessment-popup" class="popup-overlay"> 
                <div id="administer-assessment-modal-content" class="popup-content" role="document">
                    <button class="popup-close">&times;</button>
                    <h2 id="administer-assessment-title" class="popup-title">Administer Assessments</h2>

                    <form id="administer-assessment-form">
                        <input type="hidden" name="assessment_id" id="assessment_id_hidden" />
                        <input type="hidden" name="course_id" id="course_id_hidden" />
                        <div class="modal-body">
                            <div id="msg1"></div>
                            <div class="form-group">
                                <label for="administer_course">Course</label>
                                <input type="text" id="administer_course" class="popup-input" readonly />
                            </div>
                            <div class="form-group">
                                <label for="administer_subject">Subject</label>
                                <input type="text" id="administer_subject" class="popup-input" readonly />
                            </div>
                            <div class="form-group">
                                <label for="administer_mode">Mode</label>
                                <select id="administer_mode" class="popup-input" disabled>
                                    <option value="1">Normal Mode</option>
                                    <option value="2">Quiz Bee Mode</option>
                                    <option value="3">Speed Mode</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="administer_class_id">Select Class</label>
                                <select name="class_id" id="administer_class_id" required class="popup-input">
                                    <option value="">Select Class</option>
                                </select>
                            </div>
                            <input type="hidden" id="administer_class_name_hidden" name="class_name_hidden" />
                            <input type="hidden" id="assessment_name_hidden" name="assessment_name_hidden" />
                        </div>
                        <div class="modal-footer">
                            <button id="administer_btn" type="submit" class="secondary-button" name="save"><span class="glyphicon glyphicon-save"></span> Administer</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Assessment Modal -->
            <div id="edit-assessment-popup" class="popup-overlay"> 
                <div id="edit-assessment-modal-content" class="popup-content" role="document">
                    <button class="popup-close">&times;</button>
                    <h2 id="edit-assessment-title" class="popup-title">Edit Assessment</h2>

                    <form id="edit-assessment-frm">
                        <div class="modal-body">
                            <div id="edit-msg"></div>
                            <div class="form-group">
                                <label>Assessment Name</label>
                                <input type="hidden" name="assessment_id" id="edit_assessment_id" />
                                <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                                <input type="text" name="assessment_name" id="edit_assessment_name" required="required" class="popup-input" />
                            </div>
                            <div class="form-group">
                                <label>Assessment Type</label>
                                <select name="assessment_type" id="edit_assessment_type" required="required" class="popup-input">
                                    <option value="1">Quiz</option>
                                    <option value="2">Exam</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assessment Mode</label>
                                <select name="assessment_mode" id="edit_assessment_mode" required="required" class="popup-input">
                                    <option value="1">Normal Mode</option>
                                    <option value="2">Quiz Bee Mode</option>
                                    <option value="3">Speed Mode</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Course</label>
                                <select name="course_id" id="edit_course_id" required="required" class="popup-input">
                                    <option value="" disabled>Select Course</option>
                                    <?php
                                    $course_qry = $conn->query("SELECT * FROM course WHERE faculty_id = '".$_SESSION['login_id']."'");
                                    while($course_row = $course_qry->fetch_assoc()) {
                                        echo "<option value='".$course_row['course_id']."'>".$course_row['course_name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Course Subject</label>
                                <select name="subject" id="edit_subject" required="required" class="popup-input">
                                    <option value="" disabled>Select Subject</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Topic</label>
                                <input type="text" name="topic" id="edit_topic" required="required" class="popup-input" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="secondary-button" id="edit_save_btn" name="save"><span class="glyphicon glyphicon-save"></span> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="delete-assessment-popup" class="popup-overlay"> 
                <div id="delete-assessment-modal-content" class="popup-content" role="document">
                    <button class="popup-close">&times;</button>
                    <h2 id="delete-assessment-title" class="popup-title">Delete Assessment</h2>
                    <div class="modal-body">
                        <p class="popup-message" id="delete-message">Are you sure you want to delete <strong id="assessment_name"></strong> from <strong id="assessment_subject"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="tertiary-button close-popup" type="button">Cancel</button>
                        <button class="secondary-button" id="confirm_delete_btn" type="submit">Delete</button>
                    </div>
                </div>
            </div>
        </div>

            <script>
          $(document).ready(function() {
            // Handles Popups
            function showPopup(popupId) {
                $('#' + popupId).css('display', 'flex');
            }

            function closePopup(popupId) {
                $('#' + popupId).css('display', 'none');
            }

            // Close the popup when close button is clicked
            $('.popup-close').on('click', function() {
                var activePopup = this.parentElement.parentElement.id;
                closePopup(activePopup);
            });
            
            // For other close button
            $('.close-popup').on('click', function() {
                var activePopup = this.parentElement.parentElement.parentElement.id;
                closePopup(activePopup);
            });

            // Hide Administer tab link initially
            $('#administer-tab-link').hide();

            // Handle tab click for assessments tab
            $('.tab-link').click(function() {
                var tabId = $(this).data('tab');

                if (tabId === 'assessment-tab') {
                    if (parseInt($('#administer-tab-link').attr('data-status')) === 1) {
                        $('#administer-tab-link').hide();
                    }
                    
                    $('.add-assessment-container').show();
                }

                if (tabId === 'administer-tab') {
                    $('.add-assessment-container').hide();
                }

                $('.tab-link').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $('#' + tabId).addClass('active');
            });

            // Show modal when "Add Assessment" is clicked
            $('#addAssessment').click(function() {
                // $('#manage_assessment').modal('show');
                showPopup('add-assessment-popup');
                $('#add-assessment-popup #assessment-form').get(0).reset();
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
                    $('#subject').html('<option value="" disabled>Select Subject</option>'); // Clear subjects dropdown
                }
            });

            $('#assessment-form').submit(function(e){
                e.preventDefault(); // Prevent the default form submission
                closePopup('add-assessment-popup');
                $.ajax({
                    url: 'save_assessment.php', 
                    method: 'POST', // Use POST to send form data
                    data: $(this).serialize(), // Serialize form data
                    success: function(resp){
                        if(resp == 1){
                            Swal.fire({
                                title: 'Success!',
                                text: 'The assessment was successfully added!',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); 
                                }
                            });
                        } else {
                            $('#msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                        }
                    }
                });
            });

            var previousClassId = null; // Variable to keep track of the previously selected class

            // Show modal when "Administer Assessment" is clicked
            $(document).on('click', '#administer', function() {
                var assessmentId = $(this).data('id');      // Get the assessment ID
                var courseId = $(this).data('course-id');   // Get the course ID
                var courseName = $(this).data('course-name'); // Get the course name
                var subjectName = $(this).data('subject');  // Get the subject name
                var mode = $(this).data('mode');            // Get the assessment mode
                var assessmentName = $(this).data('assessment-name') // Get the assessment name

                // Check if the assessment has questions
                $.ajax({
                    url: 'check_questions.php', // PHP file to check questions
                    method: 'POST',
                    data: { assessment_id: assessmentId },
                    success: function(response) {
                        if (response.trim() === 'no_questions') {
                            alert('Cannot administer this assessment. No questions have been added yet.');
                        } else {
                            // Empty Messasge
                            $('#msg1').html('');

                            // Set the hidden fields
                            $('#administer-assessment-popup #assessment_id_hidden').val(assessmentId);
                            $('#administer-assessment-popup #course_id_hidden').val(courseId);
                            $('#administer-assessment-popup #assessment_name_hidden').val(assessmentName);

                            // Set other fields
                            $('#administer-assessment-popup #administer_course').val(courseName); // Display course name
                            $('#administer-assessment-popup #administer_subject').val(subjectName);
                            $('#administer-assessment-popup #administer_mode').val(mode);

                            // Load classes based on selected course and subject
                            if (courseId && subjectName) {
                                $.ajax({
                                    url: 'administer_class.php',
                                    method: 'POST',
                                    data: { course_id: courseId, subject: subjectName },
                                    success: function(response) {
                                        $('#administer-assessment-popup #administer_class_id').html(response); // Populate classes dropdown

                                        // Reset previous class id
                                        previousClassId = null;
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('AJAX Error:', status, error);
                                    }
                                });
                            } else {
                                $('#administer-assessment-popup #administer_class_id').html('<option value="">Select Class</option>'); // Clear classes dropdown
                                previousClassId = null;
                            }

                            // Show the modal
                            // $('#administer_assessment_modal').modal('show');
                            showPopup('administer-assessment-popup');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            });

            // Handle changes in the class dropdown
            $(document).on('change', '#administer_class_id', function() {
                var selectedClassId = $(this).val();

                // Enable or disable the administer button based on class selection
                if (selectedClassId !== previousClassId) {
                    $('#administer_btn').prop('disabled', false).text('Administer');
                } else {
                    $('#administer_btn').prop('disabled', true).text('Already Administered');
                }

                // Update the previous class id
                previousClassId = selectedClassId;

                var selectedOption = $(this).find('option:selected');
                var className = selectedOption.text(); // Get the class name

                $('#administer_class_name_hidden').val(className);
            });
            
            // Handle administer form submission
            $('#administer-assessment-form').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: 'administer_assessment.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json', // Expect JSON response
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#msg1').html('<div class="alert alert-success">' + response.message + '</div>');
                            $('#administer_btn').prop('disabled', true).text('Administered'); // Disable the button

                            // Close the modal after 1 second
                            setTimeout(function() {
                                closePopup('administer-assessment-popup');
                            }, 100);

                            // Activate the Administer tab
                            $('.tab-content').removeClass('active'); // Remove 'active' from all tab contents
                            $('.tab-link').removeClass('active');
                            $('#administer-tab-link').show();
                            $('#administer-tab-link').addClass('active'); // Add 'active' class to link
                            $('#administer-tab').addClass('active'); // Add 'active' class to the tab content
                            
                            // Set the Tab Name
                            $('#administer-tab-link').text($('#administer_class_name_hidden').val() + ' | ' + $('#administer_subject').val() + ' | ' + $('#assessment_name_hidden').val());

                            // Load the content for the Administer tab via AJAX
                            $.ajax({
                                url: 'administer_tab.php',
                                method: 'POST',
                                data: {
                                    assessment_id: $('#assessment_id_hidden').val(),
                                    class_id: $('#administer_class_id').val()
                                },
                                success: function(response) {
                                    $('#administer-container').html(response);
                                    $('.add-assessment-container').hide();
                                    document.getElementById("administer-tab-link").setAttribute('data-status', '0');
                                    console.log('it should have worked');
                                },
                                error: function(xhr, status, error) {
                                    $('#administer-container').html('<div class="alert alert-danger">Failed to load content. Please try again.</div>');
                                    console.error('Error in content load:', status, error); // Log error for debugging
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'An error occurred while trying to administer the assessment.',
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        allowOutsideClick: false,
                                        customClass: {
                                            popup: 'popup-content',
                                            confirmButton: 'secondary-button'
                                        }
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            location.reload(); 
                                        }
                                    });
                                }
                            });
                        } else {
                            $('#msg1').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#msg1').html('<div class="alert alert-danger">Failed to administer. Please try again.</div>');
                        console.error('Administer form failed:', status, error);  // Log for debugging
                    }
                });

                $('#administer_assessment_modal').on('hide.bs.modal', function() {
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
                        showPopup('edit-assessment-popup');
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
                closePopup('edit-assessment-popup');

                var formData = $(this).serialize();

                $.ajax({
                    url: 'save_edit_assessment.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'The assessment was successfully editted!',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); 
                                }
                            });
                        } else {
                            $('#edit-msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                        }
                    }
                });
            });

            // Delete button functionality
            $(document).on('click', '.delete_assessment', function() {
                var assessmentId = $(this).data('id');
                var assessmentName = $(this).data('name');
                var subject = $(this).data('subject');
                
                $('#confirm_delete_btn').data('id', assessmentId); // Set assessment ID on confirm button
                showPopup('delete-assessment-popup');
                $('#delete-assessment-popup #assessment_name').html(assessmentName);
                $('#delete-assessment-popup #assessment_subject').html(subject);
            });

            // Confirm delete action
            $('#confirm_delete_btn').click(function() {
                var assessmentId = $(this).data('id');
                closePopup('delete-assessment-popup');

                $.ajax({
                    url: 'delete_assessment.php',
                    method: 'POST',
                    data: { assessment_id: assessmentId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); 
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Error: Unable to delete the assessment.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); 
                                }
                            });
                        }
                    }
                });
            });
        });
            </script>
        </div>
    </div>
</body>
</html>