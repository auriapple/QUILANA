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
                $enrolled_classes_query = $conn->query("SELECT c.class_id, c.subject, f.firstname, f.lastname 
                                                        FROM student_enrollment e
                                                        JOIN class c ON e.class_id = c.class_id
                                                        JOIN faculty f ON c.faculty_id = f.faculty_id
                                                        WHERE e.student_id = '$student_id' AND e.status = '1'");

                if ($enrolled_classes_query && $enrolled_classes_query->num_rows>0){
                    while ($row = $enrolled_classes_query->fetch_assoc()) {
                    ?>

                    <!-- Display class details -->
                    <div class="class-card">
                        <div class="class-card-title"><?php echo $row['subject'] ?></div>
                        <div class="class-card-text">Professor: <?php echo $row['firstname'] . ' ' . $row['lastname'] ?></div>
                        <div class="class-actions">
                            <button id="viewClassDetails_<?php echo $row['class_id']; ?>" class="main-button" data-id="<?php echo $row['class_id'] ?>" type="button">View Class</button>
                        </div>
                    </div>
                    <?php } 
                } else {
                    echo '<div class="no-assessments">No classes yet</div>';
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

        <!-- Modal for entering class code to join a class-->
        <div id="join-class-popup" class="popup-overlay">
            <div id="join-modal-content" class="popup-content">
                <span id="join-modal-close" class="popup-close">&times;</span>
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

        <!-- Modal for success/error message -->
        <div id="message-popup" class="popup-overlay" style="display: none;">
            <div id="message-modal-content" class="popup-content">
                <span id="message-modal-close" class="popup-close">&times;</span>
                <h2 id="message-popup-title" class="popup-title">Message</h2>
                <div id="message-body" class="modal-body">
                    <!-- Message will be dynamically inserted here -->
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

            $('#joinClass').click(function() {
                $('#msg').html('');
                $('#join-class-popup #code-frm').get(0).reset();
                $('#join-class-popup').show();
            });

            // Close the join class popup
            $('#join-modal-close').click(function() {
                $('#join-class-popup').hide(); 
            });

            // Close the message popup
            $('#message-modal-close').click(function() {
                $('#message-popup').hide();
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

                        // Close the popup
                        $('#join-class-popup').hide(); 
    
                        var message = '';
                        var title = '';
                        var titleColor = '';
                        if (result.status === 'success'){
                            title = 'SUCCESS';
                            titleColor = '#28A745';
                            message = result.message;
                        } else {
                            title = 'ERROR';
                            titleColor = '#DC3545';
                            message = result.message;
                        }
                        $('#message-popup-title').text(title).css('color', titleColor);
                        $('#message-body').html(message);

                        $('#message-popup').show();
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
        });
    </script>
</body>
</html>