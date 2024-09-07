<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Results | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <style>
        .class-separator {
            color: #A0A0A0;
            font-weight: bold;
            margin: 20px 0;
            position: relative;
            text-align: center;
        }
        .class-separator:before,
        .class-separator:after {
            content: '';
            position: absolute;
            width: 45%;
            height: 1px;
            background-color: #A0A0A0;
            top: 50%;
        }
        .class-separator:before {
            left: 0;
        }
        .class-separator:after {
            right: 0;
        }
        .no-assessments {
            color: #CDCDCD;
            text-align: center;
            font-style: italic;
        }
        .tabs .tab-link {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="container-fluid admin">
        <div class="add-course-container">
            <div class="search-bar">
                <form action="#" method="GET">
                    <input type="text" name="query" placeholder="Search" required>
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>

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
                                                WHERE s.student_id = '$student_id'");
                
                if ($classes_query->num_rows > 0) {
                    while ($class = $classes_query->fetch_assoc()) {
                        echo '<div class="class-separator">' . $class['subject'] . '</div>';
                        
                        // Fetch quizzes for each class
                        $assessments_taken_query = $conn->query("SELECT a.assessment_id, a.assessment_name, a.topic 
                                                                FROM assessment a 
                                                                WHERE a.class_id = '" . $class['class_id'] . "'");
                        if ($assessments_taken_query->num_rows > 0) {
                            while ($row = $assessments_taken_query->fetch_assoc()) {
                                echo '<div class="assessment-card">';
                                echo '<div class="assessment-card-body">';
                                echo '<div class="assessment-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
                                echo '<div class="assessment-card-text">Topic: ' . htmlspecialchars($row['topic']) . '</div>';
                                echo '<div class="assessment-actions">';
                                echo '<button class="btn btn-primary btn-sm view_assessment_details" data-id="' . $row['assessment_id'] . '" type="button">View Result</button>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
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
                $classes_query = $conn->query("SELECT c.class_id, c.subject 
                                                FROM class c 
                                                JOIN student_enrollment s ON c.class_id = s.class_id 
                                                WHERE s.student_id = '$student_id'");

                if ($classes_query->num_rows > 0) {
                    while ($class = $classes_query->fetch_assoc()) {
                        echo '<div class="class-separator">' . $class['subject'] . '</div>';
                        
                        // Fetch exams for each class
                        $assessments_taken_query = $conn->query("SELECT a.assessment_id, a.assessment_name, a.topic 
                                                                FROM assessment a 
                                                                WHERE a.class_id = '" . $class['class_id'] . "'");

                        if ($assessments_taken_query->num_rows > 0) {
                            while ($row = $assessments_taken_query->fetch_assoc()) {
                                echo '<div class="assessment-card">';
                                echo '<div class="assessment-card-body">';
                                echo '<div class="assessment-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
                                echo '<div class="assessment-card-text">Topic: ' . htmlspecialchars($row['topic']) . '</div>';
                                echo '<div class="assessment-actions">';
                                echo '<button class="btn btn-primary btn-sm view_assessment_details" data-id="' . $row['assessment_id'] . '" type="button">View Result</button>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
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
            // Tab functionality
            $('.tab-link').click(function() {
                var tab_id = $(this).attr('data-tab');
                $('.tab-link').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $("#" + tab_id).addClass('active');
            });

            // View assessment results
            $('.view_assessment_details').click(function() {
                var assessment_id = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: 'load_results.php',
                    data: { assessment_id: assessment_id },
                    success: function(response) {
                        alert('Assessment results loaded.');
                        // You can replace this alert with the code to display results.
                    },
                    error: function() {
                        alert('Failed to load assessment results.');
                    }
                });
            });
        });
    </script>
</body>
</html>
