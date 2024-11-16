<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Programs | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="assets/css/faculty-dashboard.css">
    <link rel="stylesheet" href="assets/css/classes.css">
<body>
    <?php include('nav_bar.php') ?>

    <div class="content-wrapper">
        <!-- Header Container -->
        <div class="add-course-container">
            <button class="secondary-button" id="addCourse">Add Program</button>
            <button class="secondary-button" id="addClass" style="display:none;">Add Class</button>
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="courses-tab">Programs</li>
                <li class="tab-link" id="classes-tab-link" style="display: none;" data-tab="classes-tab">Classes</li>
            </ul>
        </div>

        <div id="courses-tab" class="tab-content scrollable-content active">
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
                                <div class="arrow-up"></div>
                                <a href="#" class="edit_course" 
                                    data-id="<?php echo $row['course_id'] ?>" 
                                    data-name="<?php echo $row['course_name'] ?>">
                                    <span class="material-symbols-outlined">Edit</span>
                                    Edit</a>
                                <a href="#" class="delete_course" 
                                    data-id="<?php echo $row['course_id'] ?>" 
                                    data-name="<?php echo $row['course_name'] ?>">
                                    <span class="material-symbols-outlined">delete</span>
                                    Delete</a>
                            </div>
                        </div>
                        <div class="course-card-title"><?php echo $row['course_name'] ?></div>
                        <div class="course-card-text"><?php echo $classCount ?> Class(es)</div>
                        <div class="course-actions">
                            <button id="viewClasses" class="tertiary-button viewClasses" data-id="<?php echo $row['course_id'] ?>" data-name="<?php echo $row['course_name'] ?>" type="button">Classes</button>
                            <button id="viewCourseDetails" class="main-button" data-id="<?php echo $row['course_id'] ?>" type="button">View Details</button>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo '<div class="no-records" style="grid-column: 1/-1;"> No programs have been created yet </div>';
                }
                ?>
            </div>
        </div>

        <div id="classes-tab" class="tab-content scrollable-content">
            <div class="course-container" id="class-container">
                <!-- Classes will be dynamically loaded here -->
            </div>
        </div>

        <!-- Course Details Modal -->
        <div id="program-details-popup" class="popup-overlay"> 
            <div id="program-details-modal-content" class="popup-content details-popup" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="program-details-title" class="popup-title">Program Details</h2>

                <div class="modal-body" id="courseDetailsBody">
                    <!-- Course details will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <button class="tertiary-button close-popup">Close</button>
                </div>
            </div>
        </div>

        <!-- Class Details Modal -->
        <div id="class-details-popup" class="popup-overlay"> 
            <div id="class-details-modal-content" class="popup-content details-popup" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="class-details-title" class="popup-title">Class Details</h2>

                <div class="modal-body" id="classDetailsBody">
                    <!-- Class details will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <div id="back-button-container"></div> <!-- If View Class from the View Course Details is clicked -->
                    <button class="tertiary-button close-popup back_vcd_false">Close</button>
                </div>
            </div>

            <div class="alert-container" id="alert-container">
                <!-- Alert for Accepting/Rejecting Students will be displayed here -->
            </div>
        </div>

        <!-- Manage Course Modal -->
        <div id="add-program-popup" class="popup-overlay"> 
            <div id="add-program-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="add-program-title" class="popup-title">Add New Program</h2>

                <!-- Form to add new program -->
                <form id='course-form' class="popup-form">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <label>Program Name</label>
                            <input type="hidden" name="course_id" id="course_id_container"/>
                            <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                            <input type="text" name="course_name" required="required" class="popup-input" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="add-program" class="secondary-button" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit Course Modal -->
        <div id="edit-program-popup" class="popup-overlay"> 
            <div id="edit-program-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="edit-program-title" class="popup-title">Edit Program</h2>

                <!-- Form to edit program details -->
                <form id='edit-course-form' class="popup-form">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <label>Program Name</label>
                            <input type="hidden" name="course_id" id="course_id"/>
                            <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                            <input type="text" name="course_name" required="required" class="popup-input" value=""/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="secondary-button" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                    </div>
                </form>
            </div>
        </div>
                
        <!-- Delete Course Modal -->
        <div id="delete-program-popup" class="popup-overlay"> 
            <div id="delete-program-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="delete-program-title" class="popup-title">Delete Program</h2>

                <!-- Form to delete the program-->
                <form id='delete-course-form' class="popup-form">
                    <div class="modal-body" id="delete-modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <p id="delete-message" class="popup-message"> Are you sure you want to delete  <strong id="modal_course_name"></strong>?</p>
                            <input type="hidden" name="course_id" id="course_id"/>
                            <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="tertiary-button close-popup" type="button">Cancel</button>
                        <button class="secondary-button" id="confirm_delete_btn" type="submit">Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manage Class Modal -->
        <div id="add-class-popup" class="popup-overlay"> 
            <div id="add-class-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="add-class-title" class="popup-title">Add New Class</h2>

                <!-- Form to add new class -->
                <form id='class-form' class="popup-form">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <input type="hidden" name="course_id" />
                        <input type="hidden" name="class_id" />
                        <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                        <div class="form-group">
                            <label>Class Name</label>
                            <input type="text" name="class_name" required="required" placeholder="Course, Year, and Section (ex. BSIT 1-1)" class="popup-input" />
                        </div>
                        <div class="form-group">
                            <label>Course Subject</label>
                            <input type="text" name="subject" required="required" class="popup-input" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="secondary-button" name="save"><span class="glyphicon glyphicon-save"></span> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Class Modal -->
        <div id="edit-class-popup" class="popup-overlay"> 
            <div id="edit-class-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="edit-class-title" class="popup-title">Edit Class</h2>

                <!-- Form to edit class -->
                <form id='edit-class-form' class="popup-form">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <input type="hidden" id="course_id" />
                            <input type="hidden" name="class_id" id="class_id"/>
                            <input type="hidden" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                            <label>Class Name</label>
                            <input type="text" name="class_name" required="required" class="popup-input" value=""/>
                        </div>
                        <div class="form-group">
                            <label>Course Subject</label>
                            <input type="text" name="subject" required="required" class="popup-input" value=""/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="secondary-button" name="save"><span class="glyphicon glyphicon-save"></span>Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Class Modal -->
        <div id="delete-class-popup" class="popup-overlay"> 
            <div id="delete-class-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="delete-class-title" class="popup-title">Delete Class</h2>

                <!-- Form to delete class -->
                <form id='delete-class-form' class="popup-form">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <p id="delete-message" class="popup-message"> Are you sure you want to delete <strong id="modal_class_name"></strong> (<strong id="modal_subject"></strong>)?</p>
                            <input type="hidden" id="course_id" />
                            <input type="hidden" name="class_id" id="class_id"/>
                            <input type="hidden" id="faculty_id" name="faculty_id" value="<?php echo $_SESSION['login_id']; ?>" />
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button class="tertiary-button close-popup" type="button">Cancel</button>
                    <button class="secondary-button" id="confirm_delete_btn" type="submit">Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Get Code Modal -->
        <div id="class-code-popup" class="popup-overlay"> 
            <div id="class-code-modal-content" class="popup-content" role="document">
                <button class="popup-close">&times;</button>
                <h2 id="class-code-title" class="popup-title">Class Code</h2>

                <!-- Get Code -->
                <div class="modal-body">
                    <div id="msg"></div>
                    <div class="form-group">
                        <h3 style="font-weight: bold;"><a id="modal_class_name"></a> (<a id="modal_subject"></a>)</h3>
                        <h1 id="modal_code"></h1>
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

            function getClasses(course_id) {
                $.ajax({
                    url: 'get_class_cards.php',
                    method: 'POST',
                    data: { course_id: course_id },
                    success: function(response) {
                        $('#class-container').html(response);
                        updateMeatballMenu();
                    }
                });
            } 

            const urlParams = new URLSearchParams(window.location.search);

            // Close the popup when close button is clicked
            $('.popup-close').on('click', function() {
                var activePopup = this.parentElement.parentElement.id;
                closePopup(activePopup);

                if (activePopup == 'class-details-popup') {
                    urlParams.delete('show_modal');
                    urlParams.delete('class_id');

                    const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                    history.replaceState(null, '', newUrl); // This will update the URL
                }
            });
            
            // For other close button
            $('.close-popup').on('click', function() {
                var activePopup;
                if (this.parentElement.parentElement.id == 'program-details-modal-content' || this.parentElement.parentElement.id == 'class-details-modal-content') {
                    activePopup = this.parentElement.parentElement.parentElement.id;
                } else {
                    activePopup = this.parentElement.parentElement.parentElement.parentElement.id;
                }
                
                closePopup(activePopup);
                
                if (activePopup == 'class-details-popup') {
                    urlParams.delete('show_modal');
                    urlParams.delete('class_id');

                    const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                    history.replaceState(null, '', newUrl); // This will update the URL
                }
            });

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
                $('#add-program-popup #course-form').get(0).reset();
                showPopup('add-program-popup');
            });

            // When edit button (course) is clicked
            $(document).on('click', '.edit_course', function() {
                var courseId = $(this).data('id');
                var courseName = $(this).data('name');

                $('#msg').html('');
                $('#edit-program-popup #course_id').val(courseId);
                $('#edit-program-popup input[name="course_name"]').val(courseName);
                $('#edit-program-popup #edit-course-form');
                showPopup('edit-program-popup')
            });

                //When delete button is clicked
                $('.delete_course').click(function() {
                    var courseId = $(this).data('id');
                    var courseName = $(this).data('name');

                    // Open a modal for deleting
                    $('#msg').html('');
                    $('#delete-program-popup #delete-course-form').get(0).reset();
                    $('#delete-program-popup #course_id').val(courseId);
                    $('#delete-program-popup #modal_course_name').text(courseName);
                    showPopup('delete-program-popup');
                });

            // When add new class button is clicked
            $('#addClass').click(function() {
                $('#msg').html('');
                $('#add-class-popup #class-form').get(0).reset();
                showPopup('add-class-popup');
            });

            // When edit button (class) is clicked
            $(document).on('click', '.edit_class', function() {
                var courseId = $(this).data('course-id');
                var classId = $(this).data('class-id');
                var className = $(this).data('class-name');
                var subject = $(this).data('subject');

                $('#msg').html('');
                $('#edit-class-popup .modal-title').html('Edit Class');
                $('#edit-class-popup #edit-class-form').get(0).reset();
                $('#edit-class-popup #course_id').val(courseId);
                $('#edit-class-popup #class_id').val(classId);
                $('#edit-class-popup input[name="class_name"]').val(className);
                $('#edit-class-popup input[name="subject"]').val(subject);
                showPopup('edit-class-popup');
            });

            //When delete button (class) is clicked
            $(document).on('click', '.delete_class', function() {
                var courseId = $(this).data('course-id');
                var classId = $(this).data('class-id');
                var className = $(this).data('class-name');
                var subject = $(this).data('subject');

                //Open a modal for deleting
                $('#msg').html('');
                $('#delete-class-popup #delete-class-form').get(0).reset();
                $('#delete-class-popup #course_id').val(courseId);
                $('#delete-class-popup #class_id').val(classId);
                $('#modal_class_name').text(className);
                $('#modal_subject').text(subject);
                showPopup('delete-class-popup');
            });

            $(document).on('click', '.get_code', function() { 
                var classId = $(this).data('class-id');
                var className = $(this).data('class-name');
                var subject = $(this).data('subject');

                $('#msg').html('');
                $('#class-code-popup #modal_class_name').text(className);
                $('#class-code-popup #modal_subject').text(subject);

                // Fetch the code dynamically using AJAX
                $.ajax({
                    url: 'generated_code.php',
                    type: 'POST',
                    data: { class_id: classId }, 
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            $('#class-code-popup #modal_code').text(result.code);
                        } else {
                            $('#class-code-popup #modal_code').text('Error: ' + result.error);
                        }
                        showPopup('class-code-popup');
                    },
                    error: function(xhr, status, error) {
                        $('#modal_code').text('Error fetching code. Please try again.');
                        console.error('Error:', error);
                    }
                });
            });

            // Handle Edit Form (Course)
            $('#edit-course-form').submit(function(event) {
                event.preventDefault();
                closePopup('edit-program-popup');

                $.ajax({
                    url: './save_editted_course.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'The program was successfully editted!',
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
                                text: 'Failed to save course: ' + response.msg,
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
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while saving course details.',
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
            });

            // Handle Delete Form (Course)
            $('#delete-course-form').submit(function(event) {
                event.preventDefault();
                closePopup('delete-program-popup');

                $.ajax({
                    url: 'delete_course.php', 
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.msg,
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
                                text: 'Error: ' + response.msg,
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
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the program.',
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
            });

            // Handle Edit Form (Class)
            $('#edit-class-form').submit(function(event) {
                event.preventDefault();
                closePopup('edit-class-popup');
                var course_id = $('#edit-class-popup #course_id').val();
                closePopup('edit-class-popup');

                $.ajax({
                    url: './save_editted_class.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'The program was successfully editted!',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    getClasses(course_id);
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to edit class: ' + response.msg,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while editting class details.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                                confirmButton: 'secondary-button'
                            }
                        });
                    }
                });
            });

             // Handle Delete Form (Class)
             $('#delete-class-form').submit(function(event) {
                    event.preventDefault();
                    closePopup('delete-class-popup');
                    var course_id = $('#delete-class-popup #course_id').val();
                    var faculty_id = $('#delete-class-popup #faculty_id').val();
                    var class_id = $('#delete-class-popup #class_id').val();

                    $.ajax({
                        url: 'delete_class.php', 
                        method: 'POST',
                        data: {
                            course_id: course_id,
                            class_id: class_id,
                            faculty_id: faculty_id 
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 1) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.msg,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    customClass: {
                                        popup: 'popup-content',
                                        confirmButton: 'secondary-button'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        getClasses(course_id);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Error: ' + response.msg,
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    customClass: {
                                        popup: 'popup-content',
                                        confirmButton: 'secondary-button'
                                    }
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log("Request failed: " + textStatus + ", " + errorThrown);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while deleting the class.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            });
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
                        $('#program-details-popup #courseDetailsBody').html(response);
                        // $('#course_details').modal('show');
                        showPopup('program-details-popup');
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
                console.log(class_id);
                
                $.ajax({
                    url: 'get_class_details.php',
                    method: 'GET',
                    data: { class_id: class_id },
                    success: function(response) {
                        $('#class-details-popup #classDetailsBody').html(response);
                        showPopup('class-details-popup');
                        refreshAssessmentTable(class_id);
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

                $('#program-details-popup').modal('hide');

                $('#program-details-popup').one('hidden.bs.modal', function() {
                    $('#class-details-popup').modal('show');
                });
            });

            // View Class Details: Back Button
            $(document).on('click', '.back_vcd', function() {
                isButtonClicked = false;
                
                $('#program-details-popup').modal('hide');

                $('#program-details-popup').one('hidden.bs.modal', function() {
                    $('#program-details-popup').modal('show');
                });
            });

            // To make sure that the isButtonClicked false after exiting the Class Details
            $(document).on('click', '.back_vcd_false', function() {
                isButtonClicked = false;
            });
            
            // When the next modal is shown, check the boolean value
            $('#class-details-popup').on('shown.bs.modal', function() {
                if (isButtonClicked) {
                    $('#back-button-container').html('<button class="btn btn-secondary back_vcd" data-dismiss="modal">Back</button>');
                } else {
                    $('#back-button-container').html('');
                }
            });

            // Saving new course
            $('#course-form').submit(function(e) {
                e.preventDefault();
                $('#course-frm [name="save"]').attr('disabled', true).html('Saving...');
                $('#msg').html('');
                closePopup('add-program-popup');

                $.ajax({
                    url: './save_course.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    error: function(err) {
                        console.log(err);
                        alert('An error occurred');
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                                confirmButton: 'secondary-button'
                            }
                        });
                        $('#course-frm [name="save"]').removeAttr('disabled').html('Save');
                    },
                    success: function(resp) {
                        if (typeof resp != undefined) {
                            resp = JSON.parse(resp);
                            if (resp.status == 1) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'The program was successfully added!',
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
                                $('#msg').html('<div class="alert alert-danger">' + resp.msg + '</div>');
                            }
                        }
                    }
                });
            });

            // Handle Classes button click
            $('.viewClasses').click(function() {
                var course_id = $(this).attr('data-id');
                var course_name = $(this).attr('data-name');

                // Show the Classes tab and set the course name
                $('#classes-tab-link').show().click();
                $('#classes-tab-link').text(course_name);

                // Fetch and display classes associated with the course
                getClasses(course_id);

                // Set the hidden course_id field in the add class form
                $('#add-class-popup input[name="course_id"]').val(course_id);
            });

            // AJAX form submission for adding a class
            $('#class-form').submit(function(e) {
                e.preventDefault();
                closePopup('add-class-popup');
                var course_id = $('#add-class-popup input[name="course_id"]').val();
                closePopup('add-class-popup');
                
                $.ajax({
                    url: 'save_class.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'The program was successfully editted!',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                customClass: {
                                    popup: 'popup-content',
                                    confirmButton: 'secondary-button'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    getClasses(course_id);
                                }
                            });
                        } else {
                            alert(response.msg);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Request failed: " + textStatus + ", " + errorThrown);
                        alert('An error occurred while saving the class.');
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while saving the class details.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                                confirmButton: 'secondary-button'
                            }
                        });
                    }
                });
            });

            function initializeMeatballMenu() {
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
            const showModal = urlParams.get('show_modal');
            const classId = urlParams.get('class_id');

            // If `show_modal` is true, open the class details modal
            function fetchClassDetails(classId) {
                $.ajax({
                    url: 'get_class_details.php',
                    type: 'GET',
                    data: { class_id: classId },
                    success: function (response) {
                        // Load the response into the modal body
                        $('#class-details-popup #classDetailsBody').html(response);
                    },
                    error: function () {
                        $('#class-details-popup #classDetailsBody').html('<p>Error loading class details.</p>');
                    }
                });
            }

            if (showModal === 'true' && classId) {
                // Show the modal
                showPopup('class-details-popup');

                // Fetch class details dynamically
                fetchClassDetails(classId);
            }

            // Ensure meatball menu is initialized after any dynamic content changes
            $(document).ajaxComplete(function() {
                updateMeatballMenu();
            });
        });
        </script>
    </body>
</html>