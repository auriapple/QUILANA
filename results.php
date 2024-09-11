<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Results | Quilana</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Popup Styles */
        table {
            width: 100%;
            border-collapse: separate;
            border-radius: 15px;
            border: 2px solid rgba(59, 39, 110, 0.80);
            overflow: hidden;
            border-spacing: 0;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: none;
            color:#4a4a4a;
        }
        thead th {
            background-color: #E0E0EC;
            color: #474747;
            font-size: 16px;
        }
        .modal {
            display: none;
            position: fixed; 
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            position: relative;
            background-color: #fff;
            width: 600px;
            height: 350px;
            margin: 10% auto;
            padding-right:45px;
            padding-left: 45px;
            padding-top: 60px;
            border-radius: 25px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
            z-index: 1001;
            text-align: center;
            align-items: center;
        }
        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            color: #333;
            cursor: pointer;
        }
        h2#assessment-title {
            font-family: 'Inter', sans-serif;
            color: #1E1A43;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        p#assessment-topic {
            font-family: 'Inter', sans-serif;
            color: #4a4a4a;
            margin-bottom: 20px;
            text-align: center;
            font-size: 20px;
        }

        /* Class Name, Separator, and Label */
        .class-separator {
            display: flex;
            align-items: center;
            color: #A0A0A0;
            font-weight: bold;
            margin: 20px 0;
            text-align: left;
            position: relative;
        }
        .subject-name {
            flex: 0 0 auto;
            margin-right: 20px;
        }
        .separator-line {
            flex: 1;
            border: none;
            border-top: 2px solid rgba(160, 160, 160, 0.5);
            margin: 0;
        }
        .no-assessments {
            color: #CDCDCD;
            text-align: center;
            font-style: italic;
            width: 100%;
        }

        /* Tabs Styles */
        .tabs .tab-link {
            font-weight: bold;
            cursor: pointer;
            padding: 10px;
            margin: 0;
        }
        .tabs .tab-link.active {
            border-bottom: 2px solid #4A4CA6;
        }

        /* Assessment Container Styles */
        .quizzes-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: start;
        }
        .assessment-card {
            background-color: #FFFFFF;
            border: 1px solid #F8F9FA;
            border-radius: 8px;
            box-shadow: 4px 4px 4px rgba(0, 0, 0, 0.25);
            width: 330px;
            height: 208px;
            margin: 10px;
            padding: 20px;
            box-sizing: border-box;
        }
        .assessment-card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .assessment-card-title {
            color: #1E1A43;
            font-size: 20px;
            font-weight: bold;
        }
        .assessment-card-text {
            color: #8F8F9D;
            font-size: 16px;
            margin: 10px 0;
        }
        .assessment-actions {
            margin-top: 10px;
        }
        .view_assessment_details {
            background-image: linear-gradient(to right, #6E72C1, #4A4CA6);
            background-color: #4A4CA6;
            width: 100%;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 30px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: none; 
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .view_assessment_details:hover {
            background-color: #4A4CA6;
            background-image: none;
            cursor: pointer;
            box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.25);
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

        <!-- Assessment Results Modal -->
        <div id="assessment-popup" class="modal">
            <div class="modal-content">
                <span id="popup-close" class="modal-close">&times;</span>
                <h2 id="assessment-title"></h2>
                <p id="assessment-topic"></p>
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
        // Fetch exams separately, ensuring assessment_type is 2
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

            // View assessment results
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