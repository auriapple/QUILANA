<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Results | Quilana</title>
    <link rel="stylesheet" href="assets/css/results.css">
</head>
<body>
    <?php include('nav_bar.php') ?>
    <div class="content-wrapper">
        <!-- Header Container -->
        <div class="search-container">
            <form class="search-bar" action="#" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>

        <!-- Assessment Results Modal -->
        <div id="assessment-popup" class="popup-overlay">
            <div id="result-content" class="popup-content">
                <button class="popup-close">&times;</button>
                <h2 id="assessment-title" class="popup-title"></h2>
                <p id="assessment-topic" class="popup-message"></p>
                <table id="assessment-details" class="modal-table">
                    <!-- Column Names -->
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Score</th>
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

        <!-- Scrollable Content -->
        <div class="scrollable-content">
            <!-- Quizzes Tab -->
            <div id="quizzes-tab" class="tab-content active">
                <div class="assessments-container">
                <?php
                $student_id = $_SESSION['login_id'];

                // Fetch student's enrolled classes
                $classes_query = $conn->query("SELECT c.class_id, c.subject, c.class_name
                                                FROM class c 
                                                JOIN student_enrollment s ON c.class_id = s.class_id 
                                                WHERE s.student_id = '$student_id' AND s.status='1'");

                if ($classes_query->num_rows > 0) {
                    while ($class = $classes_query->fetch_assoc()) {
                        echo '<div class="content-separator">';
                        echo '<span class="content-name">' . htmlspecialchars($class['class_name']) . ' (' . htmlspecialchars($class['subject']) . ')</span>';
                        echo '<hr class="separator-line">';
                        echo '</div>';

                        // Fetch quizzes for each class
                        $quizzes_query = $conn->query("
                            SELECT a.assessment_id, a.assessment_name, a.topic, aa.administer_id
                            FROM assessment a
                            JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
                            WHERE aa.class_id = '" . $class['class_id'] . "' AND a.assessment_type = 1
                        ");

                        $quizzes = [];
                        while ($row = $quizzes_query->fetch_assoc()) {
                            $quizzes[] = $row;
                        }

                        if (count($quizzes) > 0) {
                            $has_results = false;// Track if any quizzes have been taken by the student

                            echo '<div class="quizzes-container">';
                            foreach ($quizzes as $quiz) {
                                // Check if the student has already taken the quiz
                                $results_query = $conn->query("
                                    SELECT 1 
                                    FROM student_results sr
                                    JOIN student_submission ss ON ss.submission_id = sr.submission_id
                                    WHERE sr.student_id = '$student_id' AND ss.administer_id = '" . $quiz['administer_id'] . "'
                                ");

                                if ($results_query->num_rows > 0) {
                                    $has_results = true;
                                    echo '<div class="assessment-card">';
                                    echo '<div class="assessment-card-title">' . htmlspecialchars($quiz['assessment_name']) . '</div>';
                                    echo '<div class="assessment-card-topic">Topic: ' . htmlspecialchars($quiz['topic']) . '</div>';
                                    echo '<button id="viewResult_' . $quiz['administer_id'] . '" class="main-button" data-assessment-id="' . $quiz['assessment_id'] . '" data-administer-id="' . $quiz['administer_id'] . '" type="button">View Result</button>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                        
                            // If no quizzes have results yet
                            if (!$has_results) {
                                echo '<div class="no-records">No quizzes yet for ' . htmlspecialchars($class['subject']) . '</div>';
                            }

                        // If there are no quizzes at all    
                        } else {
                            echo '<div class="no-records">No quizzes yet for ' . htmlspecialchars($class['subject']) . '</div>';
                        }
                    }
                } else {
                    echo '<div class="no-records">No quizzes yet</div>';
                }
                ?>
                </div>
            </div>

            <!-- Exams Tab -->
            <div id="exams-tab" class="tab-content">
                <div class="assessments-container">
                <?php
                // Fetch student's enrolled classes
                $classes_query = $conn->query("SELECT c.class_id, c.subject, c.class_name
                                                FROM class c 
                                                JOIN student_enrollment s ON c.class_id = s.class_id 
                                                WHERE s.student_id = '$student_id' AND s.status='1'");

                if ($classes_query->num_rows > 0) {
                    while ($class = $classes_query->fetch_assoc()) {
                        echo '<div class="content-separator">';
                        echo '<span class="content-name">' . htmlspecialchars($class['class_name']) . ' (' . htmlspecialchars($class['subject']) . ')</span>';
                        echo '<hr class="separator-line">';
                        echo '</div>';

                        // Fetch exams for each class
                        $exams_query = $conn->query("
                            SELECT a.assessment_id, a.assessment_name, a.topic, aa.administer_id
                            FROM assessment a
                            JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
                            WHERE aa.class_id = '" . $class['class_id'] . "' AND a.assessment_type = 2
                        ");

                        $exams = [];
                        while ($row = $exams_query->fetch_assoc()) {
                            $exams[] = $row;
                        }

                        if (count($exams) > 0) {
                            $has_results = false;

                            echo '<div class="exams-container">';
                            foreach ($exams as $exam) {
                                $results_query = $conn->query("
                                    SELECT 1 
                                    FROM student_results sr
                                    JOIN student_submission ss ON ss.submission_id = sr.submission_id
                                    WHERE sr.student_id = '$student_id' AND ss.administer_id = '" . $exam['administer_id'] . "'
                                ");

                                if ($results_query->num_rows > 0) {
                                    $has_results = true;
                                    echo '<div class="assessment-card">';
                                    echo '<div class="assessment-card-title">' . htmlspecialchars($exam['assessment_name']) . '</div>';
                                    echo '<div class="assessment-card-topic">Topic: ' . htmlspecialchars($exam['topic']) . '</div>';
                                    echo '<button id="viewResult_' . $exam['assessment_id'] . '" class="main-button" data-assessment-id="' . $exam['assessment_id'] . '" data-administer-id="' . $exam['administer_id'] . '" type="button">View Result</button>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';

                            if (!$has_results) {
                                echo '<div class="no-records">No exams yet for ' . htmlspecialchars($class['subject']) . '</div>';
                            } 
                        } else {
                            echo '<div class="no-records">No exams yet for ' . htmlspecialchars($class['subject']) . '</div>';
                        }
                    }
                } else {
                echo '<div class="no-records">No exams yet</div>';
                }
                ?>
                </div>
            </div>
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

            // Handles Popups
            function showPopup(popupId) {
                $('#' + popupId).css('display', 'flex');
            }
            function closePopup(popupId) {
                $('#' + popupId).css('display', 'none');
            }
            
            // View assessment results
            $('[id^=viewResult_]').click(function() {
                var assessment_id = $(this).data('assessment-id');
                var administer_id = $(this).data('administer-id');

                $.ajax({
                    type: 'GET',
                    url: 'load_results.php',
                    data: { 
                        assessment_id: assessment_id,
                        administer_id: administer_id,
                    },
                    dataType: 'json',
                    success: function(result) {
                    if (result.title && result.topic) {
                        $('#assessment-title').text(result.title);
                        if(result.mode) {
                            $('#assessment-title').html(result.title + '<br>' + result.mode);
                        }
                        $('#assessment-topic').text(result.topic);
                        
                        // Clear previous details
                        $('#assessment-details thead').empty();
                        $('#assessment-details tbody').empty();
                        
                        if (Array.isArray(result.details) && result.details.length > 0) {
                            // Check if assessment_mode is 1
                            if (result.assessment_mode == 1) {
                                $('#assessment-details thead').append(
                                    `<tr>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Remarks</th>
                                    </tr>`
                                );
                                
                                // Add details to table
                                result.details.forEach(function(item) {
                                    $('#assessment-details tbody').append(
                                        `<tr>
                                            <td>${formatDate(item.date)}</td>
                                            <td>${item.score}/${item.total_score}</td> <!-- Display score/total_score -->
                                            <td>${item.remarks}</td>
                                        </tr>`
                                    );
                                });
                                
                            } else if (result.assessment_mode == 2) {
                                // Add rank column
                                $('#assessment-details thead').append(
                                    `<tr>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Rank</th>
                                        <th>Remarks</th>
                                    </tr>`  
                                );
                                
                                // Add details to table
                                result.details.forEach(function(item) {
                                    $('#assessment-details tbody').append(
                                        `<tr>
                                            <td>${formatDate(item.date)}</td>
                                            <td>${item.score}/${item.total_score}</td> <!-- Display score/total_score -->
                                            <td>${item.rank}</td>
                                            <td>${item.remarks}</td>
                                        </tr>`
                                    );
                                });
                            } else {
                                // Add rank column and remove remarks column
                                $('#assessment-details thead').append(
                                    `<tr>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Rank</th>
                                    </tr>`
                                );
                                
                                // Add details to table
                                result.details.forEach(function(item) {
                                    $('#assessment-details tbody').append(
                                        `<tr>
                                            <td>${formatDate(item.date)}</td>
                                            <td>${item.score}/${item.total_score}</td> <!-- Display score/total_score -->
                                            <td>${item.rank}</td>
                                        </tr>`
                                    );
                                });
                            }
                        } else {
                            // Show message if no results found
                            $('#assessment-details tbody').append(
                                '<tr>' +
                                '<td colspan="4" style="text-align: center;">No results found for this assessment</td>' +
                                '</tr>'
                            );
                        }

                        // Show result popup
                        showPopup('assessment-popup');
                    } else {
                        alert('Assessment details not found.');
                    }
                }
                });
            });
            // Close the join class popup when close button is clicked
            $('.popup-close').on('click', function() {
                closePopup('assessment-popup');
            });

            //Search Functionality
            /*function initializeSearch() {
                const searchInput = $('.search-bar input[name="query"]');
                
                // Input event listener
                searchInput.on('input', function() {
                    const query = $(this).val().trim();
                    const activeTab = $('.tab-link.active').data('tab');
                    const searchType = activeTab === 'quizzes-tab' ? 'quizzes' : 'exams';
                    
                    // If search is empty, reload all items
                    if (query === '') {
                        $.get('search_results.php', {
                            query: '',
                            type: searchType
                        }, function(response) {
                            if (searchType === 'quizzes') {
                                $('#quizzes-tab .assessments-container').html(response);
                            } else {
                                $('#exams-tab .assessments-container').html(response);
                            }
                        });
                        return;
                    }
                    
                    // Perform search
                    $.get('search_results.php', {
                        query: query,
                        type: searchType
                    }, function(response) {
                        if (searchType === 'quizzes') {
                            $('#quizzes-tab .assessments-container').html(response);
                        } else {
                            $('#exams-tab .assessments-container').html(response);
                        }
                    });
                });
                
                // Handle search form submission
                $('.search-bar').submit(function(e) {
                    e.preventDefault();
                });
            }

            $(document).ready(function() {
                initializeSearch();
                
                $('.tab-link').click(function() {
                    $('.tab-link').removeClass('active');
                    $(this).addClass('active');
                    
                    $('.search-bar input[name="query"]').val('');
                    $('.search-bar input[name="query"]').trigger('input');
                });
            });*/
        });
    </script>
</body>
</html>