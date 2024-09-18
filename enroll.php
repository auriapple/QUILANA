<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Courses | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    <?php include('nav_bar.php') ?>
    <div class="content-wrapper">
        <!-- Header Container -->
        <div class="join-class-container">
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
            <div class="class-container">
                <?php
                $student_id = $_SESSION['login_id'];

                // Fetch student's enrolled classes
                $enrolled_classes_query = $conn->query("SELECT c.class_id, c.subject, c.class_name, f.firstname, f.lastname 
                                                        FROM student_enrollment e
                                                        JOIN class c ON e.class_id = c.class_id
                                                        JOIN faculty f ON c.faculty_id = f.faculty_id
                                                        WHERE e.student_id = '$student_id' AND e.status = '1'");

                while ($row = $enrolled_classes_query->fetch_assoc()) {
                ?>

                <!-- Display class details -->
                <div class="class-card">
                    <div class="meatball-menu-container">
                    <button class="meatball-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                        <div class="meatball-menu">
                            <div class="arrow-up"></div>
                            <a href="#" class="unenroll">
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
                <?php } ?>
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

        <!-- Modal for entering class code to join a class-->
        <div id="join-class-popup" class="popup-overlay">
            <div id="join-modal-content" class="popup-content">
                <span id="modal-close" class="popup-close">&times;</span>
                <h2 id="join-class-title" class="popup-title">Join Class</h2>

                <!-- Form to submit the class code -->
                <form id='code-frm' action="" method="POST">
                    <div class="modal-body">
                        <div id="msg"></div>
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

            $('#joinClass').click(function() {
                $('#msg').html('');
                $('#join-class-popup #code-frm').get(0).reset();
                $('#join-class-popup').show();
            });

            // Close the popup
            $('#modal-close').click(function() {
                $('#join-class-popup').hide(); 
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
        });
    </script>
</body>
</html>