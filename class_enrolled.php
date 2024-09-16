<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Courses | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
</head>
<style>
    .search-bar {
        display: flex;
        justify-content: right;
    }
</style>
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
                <li class="tab-link active" data-tab="classes-tab">Classes</li>
                <li class="tab-link" id="class-name-tab" data-tab="assessments-tab" style="display: none;"></li>
            </ul>
        </div>

        <div id="classes-tab" class="tab-content active">
            <div class="course-container">
                <?php
                $student_id = $_SESSION['login_id'];
                $enrolled_classes_query = $conn->query("SELECT c.class_id, c.subject, c.class_name, f.firstname, f.lastname 
                                                        FROM student_enrollment e
                                                        JOIN class c ON e.class_id = c.class_id
                                                        JOIN faculty f ON c.faculty_id = f.faculty_id
                                                        WHERE e.student_id = '$student_id' AND e.status = '1'");

                while ($row = $enrolled_classes_query->fetch_assoc()) {
                ?>
            <!-- Display class details -->
            <div class="class-card">
                    <div class="class-card-title"><?php echo $row['subject'] ?></div>
                    <div class="class-card-text">Section: <?php echo $row['class_name'] ?> <br>Professor: <?php echo $row['firstname'] . ' ' . $row['lastname'] ?></div>
                    <div class="class-actions">
                        <button id="viewClassDetails_<?php echo $row['class_id']; ?>" class="main-button" data-id="<?php echo $row['class_id'] ?>" type="button">View Class</button>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div id="assessments-tab" class="tab-content">
            <div id="course-container">
                <?php
                if (isset($_GET['class_id'])) {
                    $class_id = $_GET['class_id'];
                    include('load_assessment.php');
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function updateButtons() {
                var activeTab = $('.tab-link.active').data('tab');
            }

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

            updateButtons();

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
