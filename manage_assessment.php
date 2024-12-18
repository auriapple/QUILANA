<?php
include('header.php');
include('auth.php');
include('db_connect.php');

if (!isset($_GET['assessment_id'])) {
    echo "<p>Assessment ID not provided.</p>";
    exit();
}

$assessment_id = intval($_GET['assessment_id']);

// Fetch assessment details
$query = "SELECT a.assessment_name, a.assessment_type, a.assessment_mode, c.course_name, a.subject, a.time_limit, a.passing_rate,
          a.max_points, a.max_warnings, a.student_count, a.remaining_points
          FROM assessment a
          JOIN course c ON a.course_id = c.course_id
          WHERE a.assessment_id = ?";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $assessment_name = htmlspecialchars($row['assessment_name']);
        $assessment_type_code = htmlspecialchars($row['assessment_type']);
        $assessment_mode_code = htmlspecialchars($row['assessment_mode']);
        $course_name = htmlspecialchars($row['course_name']);
        $subject_name = htmlspecialchars($row['subject']);
        $assessment_time_limit = $row['time_limit'];
        $assessment_passing_rate = $row['passing_rate'];
        $assessment_max_points = $row['max_points'];
        $assessment_max_warnings = $row['max_warnings'];
        $assessment_student_count= $row['student_count'];
        $assessment_remaining_points= $row['remaining_points'];

        $assessment_type = ($assessment_type_code == 1) ? 'Quiz' : 'Exam';

        switch ($assessment_mode_code) {
            case 1:
                $assessment_mode = 'Normal Mode';
                break;
            case 2:
                $assessment_mode = 'Quiz Bee Mode';
                break;
            case 3:
                $assessment_mode = 'Speed Mode';
                break;
            default:
                $assessment_mode = 'Unknown Mode';
                break;
        }
    } else {
        echo "<p>No assessment found with the provided ID.</p>";
        exit();
    }

    $stmt->close();
} else {
    echo "<p>Error preparing the SQL query.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Assessment | Quilana</title>
    <style>
        .assessment {
            padding: 10px;
        }
        .assessment-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .assessment-details h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .assessment-details p {
            margin-bottom: 0.5px;
            font-size: 1em;
            color: #666;
        }

        .card-full-width {
            width: 100%;
            margin-bottom: 20px;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            border: none !important;
        }
        .card-header {
            background-color: #E0E0EC !important;
            font-weight: bold;
            border-bottom: none !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            height: 45vh;
            max-height: auto;
            overflow-y: auto;
        }
        .card-body::-webkit-scrollbar {
            display: none;
        }

        .list-group {
            gap: 15px;
        }
        .list-group-item {
            border-left: 4px solid #4A4CA6 !important;
            background-color: #f9f9f9 !important;
        }
        .list-group-item h6 {
            margin-bottom: 15px;
            font-weight: 500;
        }
        .list-group-item p {
            margin: 0;
        }
        .list-group-item .question-number {
            font-weight: bold;
            color: #4A4CA6;
            margin-bottom: 10px;
        }

        .back-arrow {
            font-size: 24px; 
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .back-arrow a {
            color: #4A4CA6; 
            text-decoration: none;
        }
        .back-arrow a:hover {
            color: #0056b3; 
        }

        .btn-primary {
            background-color: #4A4CA6;
            border-color: #4A4CA6;
        }
        .btn-primary:hover {
            background-color: #3a3b8c;
            border-color: #3a3b8c;
        }

        .float-right {
            display: flex;
            gap: 8px;
        }
        .mt-3 {
            display: flex;
            gap: 10px;
        }

        .randomize {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            margin: 0;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
            align-items: center;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            border-radius: 50%;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
        }
        input:checked + .slider {
            background-color: #4A4CA6;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="content-wrapper">
        <div class="back-arrow">
            <a href="assessment.php"> 
                <i class="fa fa-arrow-left"></i> 
            </a>
        </div>

        <div class="assessment">
            <div class="assessment-details">
                <h2><?php echo $assessment_name; ?></h2>
                <p><strong>Assessment Mode:</strong> <?php echo $assessment_mode; ?></p>
                <p><strong>Course and Subject:</strong> <?php echo $course_name; ?> - <?php echo $subject_name; ?></p>
                <?php if ($assessment_mode_code == 1): ?>
                    <p><strong>Time Limit:</strong> 
                        <span id="current-time-limit"><?php echo isset($assessment_time_limit) && $assessment_time_limit > 0 ? $assessment_time_limit . ' minutes': 'Not set'; ?></span>
                    </p>
                    <p><strong>Passing Rate:</strong> 
                        <span id="current-passing-rate"><?php echo isset($assessment_passing_rate) && $assessment_passing_rate > 0 ? $assessment_passing_rate . '%' : 'Not set'; ?></span>
                    </p>
                    <p><strong>Maximum Warnings:</strong> 
                        <span id="current-max-warnings"><?php echo $assessment_max_warnings; ?></span>
                    </p>
                <?php endif; ?>
                <?php if ($assessment_mode_code == 2): ?>
                    <p><strong>Passing Rate:</strong> 
                        <span id="quizbee-passing-rate"><?php echo isset($assessment_passing_rate) && $assessment_passing_rate > 0 ? $assessment_passing_rate . '%' : 'Not set'; ?></span>
                    </p>
                    <p><strong>Maximum Warnings:</strong> 
                        <span id="quizbee-max-warnings"><?php echo $assessment_max_warnings; ?></span>
                    </p>
                <?php endif; ?>
                <?php if ($assessment_mode_code == 3): ?>
                    <p><strong>Passing Rate:</strong> 
                        <span id="speedmode-passing-rate"><?php echo isset($assessment_passing_rate) && $assessment_passing_rate > 0 ? $assessment_passing_rate . '%' : 'Not set'; ?></span>
                    </p>
                    <p><strong>Max Points:</strong> 
                        <span id="current-max-points"><?php echo isset($assessment_max_points) ? $assessment_max_points : 'Not set'; ?></span>
                    </p>
                    <p><strong>Student Count:</strong> 
                        <span id="current-student-count"><?php echo isset($assessment_student_count) ? $assessment_student_count : 'Not set'; ?></span>
                    </p>
                    <p><strong>Remaining Points:</strong> 
                        <span id="current-remaining-points"><?php echo isset($assessment_remaining_points) ? $assessment_remaining_points : 'Not set'; ?></span></p>                
                    </p>
                    <p><strong>Maximum Warnings:</strong> 
                        <span id="speedmode-max-warnings"><?php echo $assessment_max_warnings; ?></span>
                    </p>
                <?php endif; ?>
                <div class="mt-3">
                    <?php if ($assessment_mode_code == 1): ?>
                        <button class="btn btn-secondary me-2" id="edit_time_limit_btn"><i class="fa fa-plus"></i> Edit Time Limit, Passing Rate, and Maximum Warnings</button>
                    <?php endif; ?>
                    <?php if ($assessment_mode_code == 2): ?>
                        <button class="btn btn-secondary me-2" id="edit_passing_rate_btn"><i class="fa fa-plus"></i> Edit Passing Rate and Maximum Warnings</button>
                    <?php endif; ?>
                    <?php if ($assessment_mode_code == 3): ?>
                        <button class="btn btn-secondary me-2" id="edit_speedmode_details_btn"><i class="fa fa-plus"></i> Edit Passing Rate, Pointing System, and Maximum Warnings</button>
                    <?php endif; ?>
                    <button class="btn btn-primary" id="add_question_btn">
                        <i class="fa fa-plus"></i> Add Question
                    </button>
                </div>
            </div>

            <?php
            $questions_query = "
                SELECT q.*, a.remaining_points,
                    CASE 
                        WHEN a.assessment_mode IN (1, 2) THEN q.total_points
                        WHEN a.assessment_mode = 3 THEN a.max_points
                        ELSE 0
                    END AS total_points
                FROM questions q
                JOIN assessment a ON q.assessment_id = a.assessment_id
                WHERE a.assessment_id = ? 
                ORDER BY q.order_by ASC
            ";
            $question_number = 1;
            
            if ($stmt = $conn->prepare($questions_query)) {
                $stmt->bind_param("i", $assessment_id);
                $stmt->execute();
                $questions_result = $stmt->get_result();

                if ($questions_result->num_rows > 0) {
                    echo '<div class="card card-full-width">';
                    echo '<div class="card-header">';
                    echo '<span>Questions</span>';
                    echo '<div class="randomize">';
                    echo '<span>Randomize</span>';
                    // Toggle Button next to "Randomize"
                    echo '<label class="switch">';
                    echo '<input type="checkbox" id="randomize-toggle">';
                    echo '<span class="slider"></span>';
                    echo '</label>';
                    echo '</div>';
                    
                    echo '</div>';
                    echo '<div class="card-body">';
                    echo '<ul class="list-group">';
                    
                    while ($row = $questions_result->fetch_assoc()) {
                        echo '<li class="list-group-item">';
                        echo '<div class="question-number">Question ' . $question_number . ':</div>';
                        echo '<h6>' . htmlspecialchars($row['question']) . '</h6>';
                        if ($assessment_mode_code == 1) {
                            echo '<p><strong>Points:</strong> ' . htmlspecialchars($row['total_points']) . '</p>';
                        } elseif ($assessment_mode_code == 2) {
                            echo '<p><strong>Points:</strong> ' . htmlspecialchars($row['total_points']) . '</p>';
                            echo '<p><strong>Time Limit:</strong> ' . htmlspecialchars($row['time_limit']) . ' seconds</p>';
                        } elseif ($assessment_mode_code == 3) {
                            echo '<p><strong>Max Points:</strong> ' . htmlspecialchars($row['total_points']) . '</p>';
                            echo '<p><strong>Min Points:</strong> ' . htmlspecialchars($row['remaining_points']) . '</p>';
                        }

                        echo '<div class="float-right">';
                        echo '<button class="btn btn-sm btn-outline-primary edit_question me-2" data-id="' . htmlspecialchars($row['question_id']) . '"><i class="fa fa-edit"></i></button>';
                        echo '<button class="btn btn-sm btn-outline-danger remove_question" data-id="' . htmlspecialchars($row['question_id']) . '"><i class="fa fa-trash"></i></button>';
                        echo '</div>';
                        echo '</li>';
                        
                        $question_number++;
                    }
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p class="alert alert-info">No questions found for this assessment. Start by adding some questions!</p>';
                }

                $stmt->close();
            } else {
                echo '<p class="alert alert-danger">Error preparing the SQL query for questions.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Modal for Adding/Editing Questions -->
    <div class="modal fade" id="manage_question" tabindex="-1" aria-labelledby="manageQuestionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="manageQuestionLabel">Add New Question</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <form id="question-frm">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <label for="question_type">Question Type:</label>
                            <select name="question_type" id="question_type" class="form-control" required>
                                <option value="">Select Question Type</option>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="checkbox">Checkbox (Multiple Select)</option>
                                <option value="true_false">True or False</option>
                                <option value="identification">Identification</option>
                                <option value="fill_blank">Fill in the Blank</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="question">Question</label>
                            <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>" />
                            <input type="hidden" name="id" />
                            <textarea id="question" rows="3" name="question" required class="form-control"></textarea>
                        </div>
                        <div id="points_container" class="form-group" style="display: none;">
                            <label for="points">Points:</label>
                            <input type="number" id="points" name="points" class="form-control">
                        </div>
                        <div id="time_limit_container" class="form-group" style="display: none;">
                            <label for="time_limit">Time Limit (seconds):</label>
                            <input type="number" id="time_limit" name="time_limit" class="form-control">
                        </div>

                        <!-- Multiple Choice Options -->
                        <div id="multiple_choice_options" class="question-type-options">
                            <label>Options:</label>
                            <div class="form-group" id="mc_options">
                                <div class="option-group d-flex align-items-center mb-2">
                                    <textarea rows="2" name="question_opt[]" id="mc_option_1" required class="form-control flex-grow-1 mr-2"></textarea>
                                    <label><input type="radio" name="is_right" value="0" required></label>
                                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="add_mc_option">Add Option</button>
                        </div>

                        <!-- Checkbox Options -->
                        <div id="checkbox_options" class="question-type-options">
                            <label>Options:</label>
                            <div class="form-group" id="cb_options">
                                <div class="option-group d-flex align-items-center mb-2">
                                    <textarea rows="2" name="question_opt[]" id="cb_option_1" required class="form-control flex-grow-1 mr-2"></textarea>
                                    <label><input type="checkbox" name="is_right[]" value="1"></label>
                                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="add_cb_option">Add Option</button>
                        </div>

                        <!-- True or False Options -->
                        <div id="true_false_options" class="question-type-options">
                            <div class="form-group text-center">
                                <label>Correct Answer:</label>
                                <div class="d-inline-flex align-items-center">
                                    <label class="mr-3"><input type="radio" name="tf_answer" value="true" required> True</label>
                                    <label><input type="radio" name="tf_answer" value="false" required> False</label>
                                </div>
                            </div>
                        </div>

                        <!-- Identification Options -->
                        <div id="identification_options" class="question-type-options">
                            <div class="form-group">
                                <label for="identification_answer">Correct Answer:</label>
                                <input type="text" id="identification_answer" name="identification_answer" class="form-control" required>
                            </div>
                        </div>

                        <!-- Fill in the Blank Options -->
                        <div id="fill_blank_options" class="question-type-options">
                            <div class="form-group">
                                <label for="fill_blank_answer">Correct Answer:</label>
                                <input type="text" id="fill_blank_answer" name="fill_blank_answer" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary back_vcd_false" data-dismiss="modal">Close</button>
                        <button id="save_question_btn" type="submit" class="btn btn-primary">Save Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Normal Mode Details -->
    <div id="edit_time_limit_modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Time Limit, Passing Rate, and Maximum Warnings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit_assessment_form">
                        <div class="form-group">
                            <label for="assessment_time_limit">Time Limit (minutes)</label>
                            <input type="number" id="assessment_time_limit" name="time_limit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="assessment_passing_rate">Passing Rate (%)</label>
                            <input type="number" id="assessment_passing_rate" name="passing_rate"  min="0" max="100" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="assessment_max_warnings">Maximum Warnings:</label>
                            <input type="number" id="assessment_max_warnings" name="max_warnings"  min="0" max="100" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="save_time_limit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Speed Mode Details -->
    <div class="modal fade" id="edit_speedmode_modal" tabindex="-1" aria-labelledby="editSpeedModeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSpeedModeLabel">Edit Passing Rate, Pointing System, and Maximum Warnings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="edit-speedmode-form">
                        <div class="form-group">
                            <label for="speedmode_passing_rate">Passing Rate (%):</label>
                            <input type="number" class="form-control" id="speedmode_passing_rate" name="passing_rate"  min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="assessment_max_points">Maximum Points:</label>
                            <input type="number" class="form-control" id="assessment_max_points" name="assessment_max_points" required>
                        </div>
                        <div class="form-group">
                            <label for="assessment_student_count">Number of Students Eligible for Maximum Points:</label>
                            <input type="number" class="form-control" id="assessment_student_count" name="assessment_student_count" required>
                        </div>
                        <div class="form-group">
                            <label for="assessment_remaining_points">Remaining Points (for those not eligible for maximum points):</label>
                            <input type="number" class="form-control" id="assessment_remaining_points" name="assessment_remaining_points" required>
                        </div>
                        <div class="form-group">
                            <label for="speedmode_max_warnings">Maximum Warnings:</label>
                            <input type="number" id="speedmode_max_warnings" name="max_warnings"  min="0" max="100" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary back_vcd_false" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save_speed_mode">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Quiz Bee Mode Details -->
    <div id="edit_passing_rate_modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Passing Rate and Maximum Warnings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit_quizbee_form">
                        <div class="form-group">
                            <label for="quizbee_passing_rate">Passing Rate (%)</label>
                            <input type="number" id="quizbee_passing_rate" name="passing_rate" class="form-control" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="quizbee_max_warnings">Maximum Warnings:</label>
                            <input type="number" id="quizbee_max_warnings" name="max_warnings"  min="0" max="100" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="save_passing_rate" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Show/hide question type options based on selection
        $('#question_type').change(function() {
            var questionType = $(this).val();

            // Hide all question type options and show the selected one
            $('.question-type-options').hide();
            $('#' + questionType + '_options').show();

            // Toggle 'required' attribute for visible fields
            $('.question-type-options:hidden').find('[required]').prop('required', false);
            $('#' + questionType + '_options').find('input, textarea').prop('required', true);

            // Initialize options for multiple choice and checkbox
            if (questionType === 'multiple_choice' || questionType === 'checkbox') {
                initializeOptions(questionType);
            }
        });

        // Initialize options if none exist for multiple choice or checkbox
        function initializeOptions(type) {
            var optionsContainer = $('#' + type + '_options');
            if (optionsContainer.find('.option-group').length === 0) {
                addOption(type);
            }
        }

        // Add a new option for multiple choice or checkbox
        function addOption(type) {
            var optionsContainer = $('#' + type + '_options');
            var optionCount = optionsContainer.find('.option-group').length + 1;

            var newOption = `
                <div class="option-group d-flex align-items-center mb-2">
                    <textarea rows="2" name="question_opt[]" id="${type}_option_${optionCount}" class="form-control flex-grow-1 mr-2" required></textarea>
                    <label><input type="${type === 'multiple_choice' ? 'radio' : 'checkbox'}" name="${type === 'multiple_choice' ? 'is_right' : 'is_right[]'}" value="${optionCount - 1}" ${type === 'multiple_choice' ? 'required' : ''}></label>
                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                </div>
            `;
            optionsContainer.find('.form-group').append(newOption);
        }

        // Add option buttons handler
        $(document).on('click', '#add_mc_option, #add_cb_option', function() {
            var type = $(this).attr('id').includes('mc') ? 'multiple_choice' : 'checkbox';
            addOption(type);
        });

        // Remove option button handler
        $(document).on('click', '.remove-option', function() {
            var optionsContainer = $(this).closest('.question-type-options');
            if (optionsContainer.find('.option-group').length > 1) {
                $(this).closest('.option-group').remove();
            } else {
                alert("You must have at least one option.");
            }
        });

        // Form submission
        $('#question-frm').submit(function(e) {
            e.preventDefault();

            var questionType = $('#question_type').val();
            var formData = new FormData(this);

            if (!formData.get('id')) {
                formData.delete('id');
            }

            // Clear and append options correctly for the selected question type
            formData.delete('question_opt[]');
            formData.delete('is_right[]');
            formData.delete('is_right');

            // Append option data for multiple_choice or checkbox types
            $('#' + questionType + '_options .option-group').each(function(index) {
                var optionText = $(this).find('textarea[name="question_opt[]"]').val();
                if (optionText && optionText.trim() !== '') {
                    formData.append('question_opt[]', optionText.trim());

                    if (questionType === 'multiple_choice') {
                        if ($(this).find('input[name="is_right"]:checked').length > 0) {
                            formData.append('is_right', index);
                        }
                    } else if (questionType === 'checkbox') {
                        if ($(this).find('input[name="is_right[]"]:checked').length > 0) {
                            formData.append('is_right[]', index);
                        }
                    }
                }
            });

            // Additional validation for specific question types
            if (!validateForm(questionType)) return;

            // AJAX submission
            $.ajax({
                type: 'POST',
                url: 'save_questions.php',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#msg').html('<div class="alert alert-success">' + response.message + '</div>');
                        $('#save_question_btn').prop('disabled', true).text('Saved');
                        setTimeout(function() {
                            $('#manage_question').modal('hide');
                            location.reload();
                        }, 1000);
                    } else {
                        $('#msg').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#msg').html('<div class="alert alert-danger">An error occurred while saving the question. Please try again.</div>');
                }
            });
        });

        // Form validation function
        function validateForm(questionType) {
            var isValid = true;

            // Validate required fields
            $('#' + questionType + '_options').find('input:visible, textarea:visible').each(function() {
                if ($(this).prop('required') && !$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                $('#msg').html('<div class="alert alert-danger">Please fill out all required fields.</div>');
                return false;
            }

            // Additional validation for question types
            switch (questionType) {
                case 'multiple_choice':
                    if ($('#' + questionType + '_options .option-group').length < 2) {
                        $('#msg').html('<div class="alert alert-danger">Please add at least two options.</div>');
                        return false;
                    }
                    if ($('#' + questionType + '_options input[name="is_right"]:checked').length === 0) {
                        $('#msg').html('<div class="alert alert-danger">Please select the correct answer.</div>');
                        return false;
                    }
                    break;
                case 'checkbox':
                    if ($('#' + questionType + '_options .option-group').length < 2) {
                        $('#msg').html('<div class="alert alert-danger">Please add at least two options.</div>');
                        return false;
                    }
                    if ($('#' + questionType + '_options input[name="is_right[]"]:checked').length === 0) {
                        $('#msg').html('<div class="alert alert-danger">Please select at least one correct answer.</div>');
                        return false;
                    }
                    break;
                case 'true_false':
                    if (!$('input[name="tf_answer"]:checked').val()) {
                        $('#msg').html('<div class="alert alert-danger">Please select True or False.</div>');
                        return false;
                    }
                    break;
            }
            return true;
        }

        // Populate form when editing question
        function populateQuestionForm(data) {
            $('#question-frm')[0].reset();
            $('#question_type').val(data.question_type).trigger('change');
            $('input[name="id"]').val(data.question_id);
            $('#question').val(data.question);
            $('#points').val(data.total_points);
            $('#time_limit').val(data.time_limit);

            if (data.question_type === 'multiple_choice' || data.question_type === 'checkbox') {
                $('#' + data.question_type + '_options .form-group').empty();

                data.options.forEach(function(option, index) {
                    var newOption = `
                        <div class="option-group d-flex align-items-center mb-2">
                            <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" required>${option.option_txt}</textarea>
                            <label>
                                <input type="${data.question_type === 'multiple_choice' ? 'radio' : 'checkbox'}" 
                                    name="${data.question_type === 'multiple_choice' ? 'is_right' : 'is_right[]'}" 
                                    value="${index}" ${option.is_right ? 'checked' : ''}>
                            </label>
                            <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                        </div>
                    `;
                    $('#' + data.question_type + '_options .form-group').append(newOption);
                });
            } 
            else if (data.question_type === 'true_false') {
                $('input[name="tf_answer"]').prop('checked', false);
                    if (Array.isArray(data.options) && data.options.length === 2) {
                        const trueOption = data.options[0].option_txt; 
                        const falseOption = data.options[1].option_txt; 
                        
                        const answer = data.options[0].is_right ? trueOption : falseOption;

                        $(`input[name="tf_answer"][value="${answer}"]`).prop('checked', true);
                    } else {
                        console.warn('Options are not valid for true_false:', data.options);
                    }
                    } 
            else if (data.question_type === 'identification') {
                    if (data.answer !== undefined) {
                        $('#identification_answer').val(data.answer);
                    } else {
                        console.warn('Answer is not defined for identification:', data.answer);
                    }
                } 
            else if (data.question_type === 'fill_blank') {
                    if (data.answer !== undefined) {
                        $('#fill_blank_answer').val(data.answer);
                    } else {
                        console.warn('Answer is not defined for fill_blank:', data.answer);
                    }
                }
            }

        // Add question button handler (for new questions)
        $(document).on('click', '#add_question_btn', function() {
            $('#question-frm')[0].reset();
            $('#question_type').val('').trigger('change');
            $('#msg').html('');
            $('#multiple_choice_options .form-group, #checkbox_options .form-group').empty();
            $('input[name="id"]').val(''); 
            $('#manageQuestionLabel').text('Add New Question');
            $('#manage_question').modal('show');

            handleAssessmentModeChange();
        });

        // Edit question button handler
        $(document).on('click', '.edit_question', function() {
            var questionId = $(this).data('id');

            // Fetch question details for editing
            $.ajax({
                type: 'GET',
                url: 'get_question.php',
                data: { question_id: questionId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        populateQuestionForm(response.data);
                        $('input[name="id"]').val(response.data.question_id); 
                        $('#manageQuestionLabel').text('Edit Question');
                        $('#manage_question').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                    alert('An error occurred while fetching the question details. Please try again.');
                }
            });
        });

        // Delete question button handler
        $(document).on('click', '.remove_question', function() {
            var questionId = $(this).data('id');

            if (confirm('Are you sure you want to delete this question?')) {
                $.ajax({
                    type: 'POST',
                    url: 'delete_question.php',
                    data: { question_id: questionId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: " + status + ": " + error);
                        alert('An error occurred while deleting the question. Please try again.');
                    }
                });
            }
        });

        // Edit normal mode assessment details
        $('#edit_time_limit_btn').click(function() {
            var currentTimeLimit = $('#current-time-limit').text();
            var currentPassingRate = $('#current-passing-rate').text();
            var currentMaxWarnings = $('#current-max-warnings').text();

            $('#assessment_time_limit').val(currentTimeLimit === 'Not set' ? '' : currentTimeLimit);
            $('#assessment_passing_rate').val(currentPassingRate === 'Not set' ? '' : currentPassingRate);
            $('#assessment_max_warnings').val(currentMaxWarnings === 'Not set' ? '' : currentMaxWarnings);

            $('#edit_time_limit_modal').modal('show');
        });

        // Save normal mode assessment details
        $('#save_time_limit').click(function() {
            var newTimeLimit = $('#assessment_time_limit').val();
            var newPassingRate = $('#assessment_passing_rate').val();
            var newMaxWarnings = $('#assessment_max_warnings').val();

            // Validate the inputs
            if (newTimeLimit === '' || newPassingRate === '' || newMaxWarnings === '') {
                alert('Please enter new valid time limit, passing rate, and max warnings.');
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'update_assessment_time_limit.php',
                data: {
                    assessment_id: <?php echo $assessment_id; ?>,
                    time_limit: newTimeLimit,
                    passing_rate: newPassingRate,
                    max_warnings: newMaxWarnings
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#current-time-limit').text(newTimeLimit === '0' ? 'Not set' : newTimeLimit);
                        $('#current-passing-rate').text(newPassingRate === '0' ? 'Not set' : newPassingRate);

                        $('#edit_time_limit_modal').modal('hide');
                        alert('Normal mode details updated successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                    alert('An error occurred while updating normal mode details. Please try again.');
                }
            });
        });

        // Edit speed mode assessment details
        $('#edit_speedmode_details_btn').click(function() {
            var currentPassingRate = $('#speedmode-passing-rate').text().replace('%', '');
            var currentMaxPoints = $('#current-max-points').text();
            var currentStudentCount = $('#current-student-count').text();
            var currentRemainingPoints = $('#current-remaining-points').text();
            var currentMaxWarnings = $('#speedmode-max-warnings').text();

            $('#speedmode_passing_rate').val(currentPassingRate !== 'Not set' ? currentPassingRate : '');
            $('#assessment_max_points').val(currentMaxPoints !== 'Not set' ? currentMaxPoints : '');
            $('#assessment_student_count').val(currentStudentCount !== 'Not set' ? currentStudentCount : '');
            $('#assessment_remaining_points').val(currentRemainingPoints !== 'Not set' ? currentRemainingPoints : '');
            $('#speedmode_max_warnings').val(currentMaxWarnings !== 'Not set' ? currentMaxWarnings: '');

            $('#edit_speedmode_modal').modal('show');
        });

        // Save speed mode assessment details
        $('#save_speed_mode').click(function() {
            var passingRate = $('#speedmode_passing_rate').val();
            var maxPoints = $('#assessment_max_points').val();
            var studentCount = $('#assessment_student_count').val();
            var remainingPoints = $('#assessment_remaining_points').val();
            var maxWarnings = $('#speedmode_max_warnings').val();

            if (passingRate === '' || !maxPoints === '' || !studentCount === '' || !remainingPoints === '' || !maxWarnings === '') {
                alert('Please fill in all fields correctly.');
                return;
            }

            // AJAX call to save the data
            $.ajax({
                type: 'POST',
                url: 'save_speedmode_details.php',
                data: {
                    assessment_id: <?php echo json_encode($assessment_id); ?>,
                    passing_rate: passingRate,
                    max_points: maxPoints,
                    student_count: studentCount,
                    remaining_points: remainingPoints,
                    max_warnings: maxWarnings
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#speedmode-passing-rate').text(passingRate === '0' ? 'Not set' : passingRate + "%");
                        $('#current-max-points').text(maxPoints === '0' ? 'Not set' : maxPoints);
                        $('#current-student-count').text(studentCount === '0' ? 'Not set' : studentCount);
                        $('#current-remaining-points').text(remainingPoints === '0' ? 'Not set' : remainingPoints);
                        $('#speedmode-max-warnings').text(maxWarnings === '0' ? 'Not set' : maxWarnings)
                        $('#edit_speedmode_modal').modal('hide');
                        alert('Speed mode details saved successfully!');
                    } else {
                        alert('Failed to save speed mode details: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                    alert('An error occurred while updating speed mode details. Please try again.') }
            });
        });

        // Edit quiz bee mode assessment details
        $('#edit_passing_rate_btn').click(function() {
            var currentPassingRate = $('#quizbee-passing-rate').text().trim();
            var currentMaxWarnings = $('#quizbee-max-warnings').text();

            $('#quizbee_passing_rate').val(currentPassingRate === 'Not set' ? '' : currentPassingRate);
            $('#assessment_max_warnings').val(currentMaxWarnings === 'Not set' ? '' : currentMaxWarnings);

            $('#edit_passing_rate_modal').modal('show');
        });

        // Save quiz bee mode assessment details
        $('#save_passing_rate').click(function() {
            var newPassingRate = $('#quizbee_passing_rate').val();
            var newMaxWarnings = $('#quizbee_max_warnings').val();

            if (newPassingRate === '') {
                alert('Please enter a valid passing rate.');
                return;
            }
            if (newMaxWarnings === '') {
                alert('Please enter a valid maximum warning.');
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'update_assessment_passing_rate.php', 
                data: {
                    assessment_id: <?php echo $assessment_id; ?>, 
                    passing_rate: newPassingRate,
                    max_warnings: newMaxWarnings
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#quizbee-passing-rate').text(newPassingRate === '0' ? 'Not set' : newPassingRate);
                        $('#edit_passing_rate_modal').modal('hide');
                        alert('Quiz bee mode details updated successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                    alert('An error occurred while updating quiz bee mode details. Please try again.');
                }
            });
        });

        // Function to handle assessment mode change
        function handleAssessmentModeChange() {
            var mode = '<?php echo $assessment_mode_code; ?>'; 

            if (mode == '2') { // Quiz Bee Mode
                $('#time_limit_container').show(); 
                $('#time_limit').prop('required', true); 
                $('#points_container').show(); 
                $('#points').prop('required', true); 
            } else if (mode == '3') { // Speed Mode
                $('#time_limit_container').hide();
                $('#time_limit').prop('required', false); 
                $('#points_container').hide(); 
                $('#points').prop('required', false); 
            } else { //Normal Mode
                $('#time_limit_container').hide(); 
                $('#time_limit').prop('required', false); 
                $('#points_container').show(); 
                $('#points').prop('required', true);
            }
        }

        // Handle question randomization changes
        const checkbox = document.getElementById('randomize-toggle');
        let checked;

        checkbox.addEventListener('change', function() {
            if (checkbox.checked) {
                console.log('Checkbox is checked');
                checked = 1;
            } else {
                console.log('Checkbox is not checked');
                checked = 0;
            }

            $.ajax({
                type: 'POST',
                url: 'randomize_questions.php',
                data: { 
                    assessment_id: <?php echo $assessment_id; ?>,
                    checked: checked
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (response.status === 'randomize') {
                        alert('Questions are randomized successfully');
                    } else if (response.status === 'undo randomize') {
                        alert('Undoed question randomization successfully')
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                    alert('An error occurred while randomizing questions. Please try again.');
                }
            });
        });

        // Function to check if randomized
        function checkRandomize() {
            $.ajax({
                type: 'GET',
                url: 'check_randomization.php',
                data: { 
                    assessment_id: <?php echo $assessment_id; ?>
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        let checked = response.checked;
                        if (checked === 1) {
                            $('#randomize-toggle').prop('checked', true); // Set to checked
                        } else {
                            $('#randomize-toggle').prop('checked', false); // Set to unchecked
                        }
                        console.log("Randomization successfully checked and set");
                    } else {
                        console.log("Error checking and setting randomization: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                }
            });
        }

        // Call the function on page load
        $(document).ready(function() {
            handleAssessmentModeChange();
            checkRandomize();
        });
    });
    </script>
</body>
</html>