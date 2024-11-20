<?php
include('header.php');
include('auth.php');
include('db_connect.php');

// Retrieve assessment ID and class ID from query parameters
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($assessment_id > 0) {
    // Fetch the assessment details and time limits based on mode
    $assessment_query = "
        SELECT a.assessment_name, a.topic, a.time_limit AS assessment_time_limit, a.assessment_mode, 
               q.question, q.ques_type, qo.option_txt, qo.is_right, qi.identification_answer, q.time_limit AS question_time_limit
        FROM assessment a
        LEFT JOIN questions q ON a.assessment_id = q.assessment_id
        LEFT JOIN question_options qo ON q.question_id = qo.question_id
        LEFT JOIN question_identifications qi ON q.question_id = qi.question_id
        WHERE a.assessment_id = ?
        ORDER BY q.order_by ASC";
    
    $stmt = $conn->prepare($assessment_query);
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $assessment_id);
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $assessment_details = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "<p>No assessment found for the given ID.</p>";
        exit;
    }
} else {
    echo "<p>Invalid assessment ID.</p>";
    exit;
}

// Calculate the overall time limit
$assessment_mode = $assessment_details[0]['assessment_mode'];
$overall_time_limit_minutes = 0;

if ($assessment_mode == 1) { // Normal Mode
    $overall_time_limit_minutes = intval($assessment_details[0]['assessment_time_limit']);
} elseif ($assessment_mode == 2 || $assessment_mode == 3) { // Quiz Bee or Speed Mode
    $total_question_time_limit = 0;
    $counted_questions = array();
    foreach ($assessment_details as $detail) {
        if (isset($detail['question_time_limit']) && !in_array($detail['question'], $counted_questions)) {
            $total_question_time_limit += intval($detail['question_time_limit']);
            $counted_questions[] = $detail['question'];
        }
    }
    $overall_time_limit_minutes = ceil($total_question_time_limit / 60);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assessment | Quilana</title>
    <style>
        #scores {
            height: calc(100% - 140px);
        }
        .scores-container {
            height: 100%;
        }
        .table-responsive {
            height: 100%;
            overflow: hidden;
        }
        .back-arrow {
            font-size: 24px; 
            margin-top: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 30px;
        }
        .back-arrow a {
            color: #4A4CA6; 
            text-decoration: none;
        }
        .back-arrow a:hover {
            color: #0056b3; 
        }

        .tab-content {
            padding: 10px;
        }
        .assessment-details {
            background-color: #FFFFFF;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .assessment-details h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #4A4CA6;
            margin-bottom: 10px;
        }
        .assessment-details p {
            margin-bottom: 0.5px;
            font-size: 16px;
            color: #555;
        }
        .questions-container {
            background-color: #FFFFFF !important;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            max-height: 60vh;
            overflow-y: auto;
            width: 100% !important;
            margin: 0 !important;
        }
        .question {
            margin-bottom: 25px;
            padding: 15px;
            border-left: 4px solid #4A4CA6;
            background-color: #f9f9f9;
        }
        .question-number {
            font-weight: bold;
            color: #4A4CA6;
            margin-bottom: 10px;
        }
        .option {
            margin-left: 20px;
            margin-bottom: 8px;
        }
        .option label {
            display: flex;
            align-items: center;
        }
        .option input[type="radio"],
        .option input[type="checkbox"] {
            margin-right: 10px;
            accent-color: #4A4CA6;
        }
        .checked {
            background-color: #e6e6fa;
            padding: 5px;
            border-radius: 4px;
            margin-left: -5px;
        }
        table {
            width: 100%;
            height: 100%;
            margin-bottom: 0 !important; 
            border-collapse: separate !important;
            border-radius: 15px;
            border: 2px solid rgba(59, 39, 110, 0.80);
            overflow: hidden;
            border-spacing: 0;
            table-layout: fixed;
        }
        thead {
            display: table;
            width: 100%;
            table-layout: fixed;
            padding-right: 8px;
            background: #E0E0EC;
        }
        tbody {
            display: block;
            height: calc(100% - 45px);
            overflow-y: auto;
            scrollbar-gutter: stable;
        }
        th, td {
            padding: 12px;
            text-align: center !important;
            border: none;
            color: #4a4a4a;
            border-right: 1px solid rgba(59, 39, 110, 0.80);
            width: 20%;
            border-bottom: none !important;
            border-top: none !important;
        }
        thead th {
            background-color: #E0E0EC;
            color: #474747;
            font-size: 16px;
        }
        td:last-child, th:last-child {
            border-right: none;
        }
        tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed; /* Ensure tbody rows align with header */
        }
        tbody::-webkit-scrollbar {
            width: 8px; /* or smaller width */
        }
        #download {
            font-size: 16px;
            padding: 8px;
            outline: none;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>
    <div class="content-wrapper">
        <div class="back-arrow">
            <a href="class_list.php?class_id=<?php echo htmlspecialchars($class_id); ?>&show_modal=true">
                <i class="fa fa-arrow-left"></i>
            </a>
            <button class="secondary-button" id="download" style="display:none;">Download Results</button>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="assessment">Assessment Details</li>
                <li class="tab-link" data-tab="scores">Student Scores</li>
            </ul>
        </div>

        <!-- Assessment Details Tab Content -->
        <div class="tab-content active" id="assessment">
            <div class="assessment-details">
                <h2><?php echo htmlspecialchars($assessment_details[0]['assessment_name']); ?></h2>
                <p><strong>Topic:</strong> <?php echo htmlspecialchars($assessment_details[0]['topic']); ?></p>
                <p><strong>Mode:</strong> <?php echo htmlspecialchars($assessment_mode == 1 ? 'Normal' : ($assessment_mode == 2 ? 'Quiz Bee' : 'Speed')) . ' Mode'; ?></p-->
                <!--p><strong>Overall Time Limit:</strong> <?php echo htmlspecialchars($overall_time_limit_minutes) . ' minutes (' . ($assessment_mode == 1 ? 'Normal' : ($assessment_mode == 2 ? 'Quiz Bee' : 'Speed')) . ' Mode)'; ?></p-->
            </div>

            <div class="questions-container">
                <?php
                $current_question = null;
                $question_number = 1;

                foreach ($assessment_details as $detail) {
                    if ($current_question !== $detail['question']) {
                        // Close the previous question if it exists
                        if ($current_question !== null) {
                            echo '</div>'; // Close previous question
                        }
                        $current_question = $detail['question'];
                        echo '<div class="question">';
                        echo '<div class="question-number">Question ' . $question_number . ':</div>';
                        echo '<p>' . htmlspecialchars($current_question) . '</p>';

                        $question_number++;
                    }

                    // Display options based on question type
                    switch ($detail['ques_type']) {
                        case 1:  // Multiple Choice
                        case 2:  // Multiple Select (Checkbox)
                        case 3:  // True/False
                            $input_type = $detail['ques_type'] == 2 ? 'checkbox' : 'radio';
                            $checked_attr = $detail['is_right'] ? 'checked' : '';
                            $checked_class = $detail['is_right'] ? 'checked' : ''; 
                            
                            echo '<div class="option">';
                            echo '<label class="' . $checked_class . '">'; 
                            echo '<input type="' . $input_type . '" class="non-interactive" name="question_' . ($question_number - 1) . '" ' . $checked_attr . '>' . htmlspecialchars($detail['option_txt']);
                            echo '</label>';
                            echo '</div>';
                            break;

                        case 4:  // Identification
                        case 5:  // Fill in the Blank
                            echo '<div class="option"><strong>Answer:</strong> <span>' . htmlspecialchars($detail['identification_answer']) . '</span></div>';
                            break;
                    }
                }

                if ($current_question !== null) {
                    echo '</div>'; // Close the last question
                }
                ?>
            </div>
        </div>

        <!-- Student Scores Tab Content -->
        <div class="tab-content" id="scores" style="display:none;">
            <div class="scores-container">
                <div id="loading-scores">Loading scores...</div>
                <div class="table-responsive">
                    <table id="scores-table" style="display:none;" class="table table-striped">
                        <thead>
                            <tr>
                                <!-- Table Headers -->
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('.tab-link').on('click', function() {
            const tabId = $(this).data('tab');
            $('.tab-content').hide().removeClass('active');
            $('#' + tabId).show().addClass('active');
            $('.tab-link').removeClass('active');
            $(this).addClass('active');

            $('#download').hide();

            if (tabId === 'scores') {
                loadStudentScores();
                $('#download').show();
            }
        });

        function loadStudentScores() {
            const assessmentId = <?php echo $assessment_id; ?>;
            const classId = <?php echo $class_id; ?>;
            const assessmentMode = <?php echo $assessment_mode; ?>;

            $('#loading-scores').show();
            $('#scores-table').hide();

            $.ajax({
                url: 'get_scores.php',
                method: 'GET',
                data: { assessment_id: assessmentId, class_id: classId },
                dataType: 'json',
                success: function(data) {
                    const thead = $('#scores-table thead');
                    const tbody = $('#scores-table tbody');
                    
                    thead.empty();
                    tbody.empty();

                    if (data.scores && data.scores.length > 0) {
                        if (assessmentMode == 1) {
                            thead.append(
                                `<tr>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Score</th>
                                    <th>Remarks</th>
                                </tr>
                            `);

                            data.scores.forEach(score => {
                                tbody.append(`
                                    <tr>
                                        <td>${score.lastname}</td>
                                        <td>${score.firstname}</td>
                                        <td>${score.score !== null ? score.score + ' / ' + score.total_score : 'Not Taken'}</td>
                                        <td>${score.remarks}</td>
                                    </tr>
                                `);
                            });
                        } else if (assessmentMode == 2) {
                            thead.append(`
                                <tr>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Score</th>
                                    <th>Rank</th>
                                    <th>Remarks</th>
                                </tr>
                            `);

                            data.scores.forEach(score => {
                                tbody.append(`
                                    <tr>
                                        <td>${score.lastname}</td>
                                        <td>${score.firstname}</td>
                                        <td>${score.score !== null ? score.score + ' / ' + score.total_score : 'Not Taken'}</td>
                                        <td>${score.rank}</td>
                                        <td>${score.remarks}</td>
                                    </tr>
                                `);
                            });
                        } else if (assessmentMode == 3) {
                            thead.append(`
                                <tr>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Score</th>
                                    <th>Rank</th>
                                </tr>
                            `);

                            data.scores.forEach(score => {
                                tbody.append(`
                                    <tr>
                                        <td>${score.lastname}</td>
                                        <td>${score.firstname}</td>
                                        <td>${score.score !== null ? score.score + ' / ' + score.total_score : 'Not Taken'}</td>
                                        <td>${score.rank}</td>
                                    </tr>
                                `);
                            });
                        }
                        
                    } else {
                        tbody.append('<tr><td colspan="4">No scores available.</td></tr>');
                    }

                    $('#scores-table').show();
                    $('#loading-scores').hide();
                },
                error: function() {
                    $('#loading-scores').text('Error loading scores. Please try again later.');
                    $('#scores-table').hide();
                }
            });
        }

        $('#download').on('click', function () {
            const assessmentId = <?php echo json_encode($assessment_id); ?>;
            const classId = <?php echo json_encode($class_id); ?>; 

            // Create a form to submit the request
            const form = $('<form>', {
                action: 'generate_report.php',
                method: 'GET'
            }).append($('<input>', { name: 'assessment_id', value: assessmentId }))
            .append($('<input>', { name: 'class_id', value: classId }));

            $('body').append(form);
            form.submit();
            form.remove();
        });

        $('.non-interactive').on('click', function(event) {
            event.preventDefault(); // Prevent default action
        });
    });
    </script>
</body>
</html>
