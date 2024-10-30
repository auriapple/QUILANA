<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Classes | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="assets/css/classes.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('nav_bar.php') ?>
    <div class="content-wrapper">
        <!-- Header Container -->
        <div class="join-class-container">
            <button class="secondary-button" id="joinClass">Join Class</button>
            <form class="search-bar">
                <input id="search-input" type="text" name="query" placeholder="Search" required>
                <button><i class="fa fa-search"></i></button>
            </form>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="classes-tab">Classes</li>
                <li class="tab-link" id="class-name-tab" data-tab="assessments-tab" style="display: none;"></li>
            </ul>
        </div>

        <!-- Scrollable Content -->
        <div class="scrollable-content">
            <!-- Classes Tab -->
            <div id="classes-tab" class="tab-content active">
                <div class="class-container">
                    <?php
                    $student_id = $_SESSION['login_id'];

                    // Fetch student's enrolled classes
                    $enrolled_classes_query = $conn->query("SELECT c.class_id, c.subject, c.class_name, f.firstname, f.lastname 
                                                            FROM student_enrollment e
                                                            JOIN class c ON e.class_id = c.class_id
                                                            JOIN faculty f ON c.faculty_id = f.faculty_id
                                                            WHERE e.student_id = '$student_id' AND e.status = '1'");
                    // Check if there are any enrolled classes
                    if ($enrolled_classes_query->num_rows>0) {
                        while ($row = $enrolled_classes_query->fetch_assoc()) {
                    ?>        
                        
                        <!-- Display class details -->
                        <div class="class-card" id="class_<?php echo $row['class_id']; ?>">
                            <div class="meatball-menu-container">
                            <button class="meatball-menu-btn">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                                <div class="meatball-menu">
                                    <div class="arrow-up"></div>
                                    <a href="#" class="unenroll"
                                        data-id = "<?php echo $row['class_id'] ?>"
                                        data-name = "<?php echo $row['class_name'] ?>" >
                                        <span class="material-symbols-outlined">exit_to_app</span>
                                        Unenroll</a>
                                    <a href="#" class="report">
                                    <span class="material-symbols-outlined">report</span>
                                        Report</a>
                                </div>
                            </div>
                            <div class="class-card-title"><?php echo $row['subject'] ?></div>
                            <div class="class-card-text">Section: <?php echo $row['class_name'] ?> <br>Professor: <?php echo $row['firstname'] . ' ' . $row['lastname'] ?></div>
                            <div class="class-actions">
                                <button id="viewClassDetails_<?php echo $row['class_id']; ?>" class="main-button" data-id="<?php echo $row['class_id'] ?>" type="button">View Class</button>
                            </div>
                        </div>
                        <?php }
                    } else {
                        echo '<div class="no-records">You are not enrolled in any classes</div>';
                    }
                    ?>           
            </div>
        </div>
        
        <!-- Assessments Tab -->
        <div id="assessments-tab" class="tab-content">
            <div id="class-container">
                <!-- If a class is selected, assessments are loaded here -->
                <?php
                    if (isset($_GET['class_id'])) {
                        $class_id = $_GET['class_id'];
                        include('load_assessment.php');
                    }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal for entering class code to join a class -->
    <div id="join-class-popup" class="popup-overlay"> 
        <div id="join-modal-content" class="popup-content">
            <button class="popup-close">&times;</button>
            <h2 id="join-class-title" class="popup-title">Join Class</h2>

            <!-- Form to submit the class code -->
            <form id='code-form' action="" method="POST">
                <div class="modal-body">
                    <div class="class-code">
                        <input type="text" name="get_code" required="required" class="code" placeholder="Class Code" />
                    </div>
                </div>
                <div class="join-button">
                    <button id="join" type="submit" class="secondary-button" name="join_by_code">Join</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for unenrolling -->
    <div id="unenroll-popup" class="popup-overlay">
        <div id="unenroll-modal-content" class="popup-content">
            <button class="popup-close">&times;</button>
            <h2 id="unenroll-title" class="popup-title">Unenroll from Class</h2>
            <p id="unenroll-message" class="popup-message">Are you sure you want to unenroll from <strong id="unenroll_class_name" style="font-weight: bold;"></strong>?</p>
            <div class="popup-buttons">
                <button id="cancel" type="submit" class="secondary-button">Cancel</button>
                <button id="confirm" type="submit" class="secondary-button" data-student-id="<?php echo $_SESSION['login_id'] ?>">Unenroll</button>
            </div>
        </div>
    </div>

    <script>  
        $(document).ready(function() {
            // Button Visibility
            function updateButtons() {
                var activeTab = $('.tab-link.active').data('tab');
        
                if (activeTab === 'assessments-tab') {
                    $('#joinClass').hide();
                } else {
                    $('#joinClass').show();
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
                    $('#assessments-tab').removeClass('active').empty();
                }
                updateButtons();
            });

            // Initialize button visibility
            updateButtons();

            // Handles Popups
            function showPopup(popupId) {
                $('#' + popupId).css('display', 'flex');
            }
            function closePopup(popupId) {
                $('#' + popupId).css('display', 'none');
            }

            // Join class button functionality
            $('#joinClass').click(function() {
                $('#msg').html('');
                $('#join-class-popup #code-form').get(0).reset();
                showPopup('join-class-popup');
            });

            // Handles code submission
            $('#code-form').submit(function(event) {
                event.preventDefault();
                console.log("Form submitted");

                $.ajax({
                    type: 'POST',
                    url: 'join_class.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        console.log("AJAX call successful", response);
                        
                        // Close the join class popup
                        closePopup('join-class-popup');

                        if (response.status === 'success') {
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
                                text: response.message,
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
                        console.log("AJAX request failed", jqXHR.responseText, textStatus, errorThrown);
                        
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while joining the class. Please try again.',
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


            // View Class Details
            $('[id^=viewClassDetails_]').click(function() {
                var class_id = $(this).data('id');
                var class_name = $(this).closest('.class-card').find('.class-card-title').text();

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

            initializeMeatballMenu();

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

            // Ensure meatball menu is initialized after any dynamic content changes
            $(document).ajaxComplete(function() {
                updateMeatballMenu();
            });

            // Unenroll button functionality
            $(document).on('click', '.unenroll', function() {
                var classId = $(this).data('id');
                var className = $(this).data('name');

                $('#confirm').data('class-id', classId); // Set class ID on confirm button
                $('#unenroll_class_name').text(className);
                showPopup('unenroll-popup');
            });

            // Confirm delete action
            $('#confirm').click(function() {
                var classId = $(this).data('class-id');
                var studentId = $(this).data('student-id');
                var status = '2';

                closePopup('unenroll-popup');

                $.ajax({
                    url: 'status_update.php',
                    method: 'POST',
                    data: { 
                        class_id: classId,
                        student_id: studentId,
                        status: status 
                    },
                    success: function(response) {
                        if (response == "success") {
                            Swal.fire({
                                title: 'Success!',
                                text: 'You have successfully unenrolled from the class',
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
                                text: 'Unable to enroll from the class. Please try again.',
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
                });
            });
            // Close the join class popup when close button is clicked
            $('.popup-close').on('click', function() {
                closePopup('join-class-popup');
            });
            // Close the unenroll popup when the close button or cancel button is clicked
            $(document).on('click', '.popup-close, #cancel', function() {
                closePopup('unenroll-popup');
            });

            // Search functionality
            $('.search-bar').submit(function(e) {
                e.preventDefault();
                performSearch();
            });

            // input event listener in search field
            $('.search-bar input[name="query"]').on('input', function() {
                performSearch();
            });

                function performSearch() {
                var query = $('.search-bar input[name="query"]').val();
            
                     $.ajax({
                        url: 'search_classes.php',
                        method: 'GET',
                        data: { 
                            query: query,
                            student_id: <?php echo $_SESSION['login_id']; ?>
                        },
                        success: function(response) {
                            $('#classes-tab').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Search failed:', error);
                        }
                    });
                }
            
        });
    </script>
</body>
</html>