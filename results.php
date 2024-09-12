<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Results | Quilana</title>
</head>
<body>
    <?php include('nav_bar.php') ?>
    <div class="container-fluid admin">
        <div class="add-course-container">
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <!-- Assessment Results Modal -->
        <div id="assessment-popup" class="popup-overlay">
            <div id="modal-content" class="popup-content">
                <span id="modal-close" class="popup-close">&times;</span>
                <h2 id="assessment-title" class="popup-title"></h2>
                <p id="assessment-topic" class="popup-message"></p>
                <table id="assessment-details" class="modal-table">
                    <!-- Column Names -->
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Score</th>
                            <th>Total Score</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Assessment details will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="quizzes-tab">Quizzes</li>
                <li class="tab-link" data-tab="exams-tab">Exams</li>
            </ul>
        </div>

        <!-- Quizzes Tab -->
<div id="quizzes-tab" class="tab-content active">
    <div class="assessments-container">
        <?php
        $student_id = $_SESSION['login_id'];

        // Fetch student's enrolled classes
        $classes_query = $conn->query("SELECT c.class_id, c.subject 
                                        FROM class c 
                                        JOIN student_enrollment s ON c.class_id = s.class_id 
                                        WHERE s.student_id = '$student_id' AND s.status='1'");

        if ($classes_query->num_rows > 0) {
            while ($class = $classes_query->fetch_assoc()) {
                echo '<div class="class-separator">';
                echo '<span class="subject-name">' . htmlspecialchars($class['subject']) . '</span>';
                echo '<hr class="separator-line">';
                echo '</div>';

                // Fetch quizzes for each class
                $quizzes_query = $conn->query("
                    SELECT a.assessment_id, a.assessment_name, a.topic 
                    FROM assessment a
                    JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
                    WHERE aa.class_id = '" . $class['class_id'] . "' AND a.assessment_type = 1
                ");

                if ($quizzes_query->num_rows > 0) {
                    while ($row = $quizzes_query->fetch_assoc()) {
                        // Check if the student has taken the assessment
                        $results_query = $conn->query("
                            SELECT 1 
                            FROM student_results 
                            WHERE student_id = '$student_id' AND assessment_id = '" . $row['assessment_id'] . "'
                        ");

                        if ($results_query->num_rows > 0) {
                            echo '<div class="assessment-card">';
                            echo '<div class="assessment-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
                            echo '<div class="assessment-card-text">Topic: ' . htmlspecialchars($row['topic']) . '</div>';
                            echo '<button class="view_assessment_details" data-id="' . $row['assessment_id'] . '" type="button">View Result</button>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="no-assessments">No quizzes yet for ' . htmlspecialchars($class['subject']) . '</div>';
                }
            }
        } else {
            echo '<div class="no-assessments">No quizzes yet</div>';
        }
        ?>
    </div>
</div>

        <!-- Exams Tab -->
        <div id="exams-tab" class="tab-content">
            <div class="assessments-container">
                <?php
                // Fetch student's enrolled classes
                $classes_query = $conn->query("SELECT c.class_id, c.subject 
                                                FROM class c 
                                                JOIN student_enrollment s ON c.class_id = s.class_id 
                                                WHERE s.student_id = '$student_id'");

        if ($classes_query->num_rows > 0) {
            while ($class = $classes_query->fetch_assoc()) {
                echo '<div class="class-separator">';
                echo '<span class="subject-name">' . htmlspecialchars($class['subject']) . '</span>';
                echo '<hr class="separator-line">';
                echo '</div>';

                // Fetch exams for each class
                $exams_query = $conn->query("
                    SELECT a.assessment_id, a.assessment_name, a.topic 
                    FROM assessment a
                    JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
                    WHERE aa.class_id = '" . $class['class_id'] . "' AND a.assessment_type = 2
                ");

                /*if ($exams_query->num_rows > 0) {
                    echo '<div class="exams-container">';
                    while ($row = $exams_query->fetch_assoc()) {
                        echo '<div class="assessment-card">';
                        echo '<div class="assessment-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
                        echo '<div class="assessment-card-text">Topic: ' . htmlspecialchars($row['topic']) . '</div>';
                        echo '<button id="viewResult" class="main-button" data-id="' . $row['assessment_id'] . '" type="button">View Result</button>';
                        echo '</div>';
                    }
                    echo '</div>';
                    } else {
                        echo '<div class="no-assessments">No exams yet for ' . htmlspecialchars($class['subject']) . '</div>'; */
                if ($exams_query->num_rows > 0) {
                    while ($row = $exams_query->fetch_assoc()) {
                        // Check if the student has taken the exam
                        $results_query = $conn->query("
                            SELECT 1 
                            FROM student_results 
                            WHERE student_id = '$student_id' AND assessment_id = '" . $row['assessment_id'] . "'
                        ");

                        if ($results_query->num_rows > 0) {
                            echo '<div class="assessment-card">';
                            echo '<div class="assessment-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
                            echo '<div class="assessment-card-text">Topic: ' . htmlspecialchars($row['topic']) . '</div>';
                            echo '<button class="view_assessment_details" data-id="' . $row['assessment_id'] . '" type="button">View Result</button>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="no-assessments">No exams yet for ' . htmlspecialchars($class['subject']) . '</div>';
                }
            }
        } else {
            echo '<div class="no-assessments">No exams yet</div>';
        }
        ?>
    </div>
</div>


        <script>
        $(document).ready(function() {
            // Assessments tab functionality
            $('.tab-link').click(function() {
                var tab_id = $(this).attr('data-tab');
                $('.tab-link').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $("#" + tab_id).addClass('active');
            });

            // Format date function
            function formatDate(dateString) {
                var date = new Date(dateString);
                var year = date.getFullYear();
                var month = ('0' + (date.getMonth() + 1)).slice(-2);
                var day = ('0' + date.getDate()).slice(-2);
                return year + '-' + month + '-' + day;
            }

            /* View assessment results
            $('#viewResult').click(function() {
                var assessment_id = $(this).data('id'); */
            $(document).on('click', '.view_assessment_details', function() {
                var assessment_id = $(this).data('id');

                $.ajax({
                    type: 'GET',
                    url: 'load_results.php',
                    data: { assessment_id: assessment_id },
                    dataType: 'json', // Expect JSON response
                    success: function(result) {
                    if (result.title && result.topic) {
                        $('#assessment-title').text(result.title);
                        $('#assessment-topic').text(result.topic);
                        
                        // Clear previous details
                        $('#assessment-details tbody').empty();
                        
                        if (Array.isArray(result.details) && result.details.length > 0) {
                            // Add new details to table
                            result.details.forEach(function(item) {
                                $('#assessment-details tbody').append(
                                    '<tr>' +
                                    '<td>' + formatDate(item.date) + '</td>' +
                                    '<td>' + item.score + '</td>' +
                                    '<td>' + item.total_score + '</td>' +
                                    '<td>' + item.remarks + '</td>' +
                                    '</tr>'
                                );
                            });
                        } else {
                            // Show message if no results found
                            $('#assessment-details tbody').append(
                                '<tr>' +
                                '<td colspan="4" style="text-align: center;">No results found for this assessment</td>' +
                                '</tr>'
                            );
                        }
                        
                        // Show the popup
                        $('#assessment-popup').show();
                    } else {
                        alert('Assessment details not found.');
                    }
                }

                });
            });

            /* Close the popup
            $('#modal-close').click(function() {
                $('#assessment-popup').hide(); */
            // Close the popup when clicking outside of it
            $(document).on('click', function(e) {
                if ($(e.target).is('.modal')) {
                    $('#assessment-popup').hide();
                }
            });
        });
    </script>
</body>
</html>