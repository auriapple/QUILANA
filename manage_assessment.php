<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <?php include('auth.php'); ?>
    <?php include('db_connect.php'); ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Manage Assessment | Quilana</title>

    <style>
        .assessment-details {
            margin-top: -15px;
            margin-left: 50px;
        }
        .assessment-details h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .assessment-details p {
            margin-bottom: -0.5px;
            font-size: 1em;
            color: #666;
        }
        .card-full-width {
            width: 100%;
            margin-left: 20px;
            margin-top: -28px;
        }
        .card-body{
            margin-top: -16px;
            height: 355px;
        }
        .list-group-item{
            margin-top: 15px;
        }
        .question-type-options {
            display: none;
        }
        .scrollable-list {
            max-height: 310px; 
            overflow-y: auto;
        }        
        body {
            overflow: hidden;
        }
        .back-arrow {
            position: absolute;
            font-size: 30px; 
            top: 70px;
            font-weight: bold;
        }
        .back-arrow a {
            color: #4A4CA6; 
        }
        .back-arrow a:hover {
            color: #0056b3; 
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="container-fluid admin">
        <div class="back-arrow">
            <a href="assessment.php"> 
                <i class="fa fa-arrow-circle-left"></i>
            </a>
        </div>

        <?php
        if (isset($_GET['assessment_id'])) {
            $assessment_id = intval($_GET['assessment_id']);
            $query = "SELECT a.assessment_name, a.assessment_type, a.assessment_mode, c.course_name, a.subject, a.time_limit 
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
                }

                $stmt->close();
            } else {
                echo "<p>Error preparing the SQL query.</p>";
            }
        } else {
            echo "<p>Assessment ID not provided.</p>";
        }
        ?>

        <div class="assessment-details">
            <h2><?php echo $assessment_name;?></h2>
            <?php if (isset($assessment_name)): ?>
                <p><strong>Assessment Type:</strong> <?php echo $assessment_type; ?></p>
                <p><strong>Assessment Mode:</strong> <?php echo $assessment_mode; ?></p>
                <p><strong>Course:</strong> <?php echo $course_name; ?></p>
                <p><strong>Subject:</strong> <?php echo $subject_name; ?></p>
                <?php if ($assessment_mode_code == 1): // Normal Mode ?>
                    <p><strong>Time Limit:</strong> 
                        <span id="current-time-limit"><?php echo isset($assessment_time_limit) && $assessment_time_limit > 0 ? $assessment_time_limit : 'Not set'; ?></span> minutes
                    </p>
                <?php endif; ?>
            <?php endif; ?>
                    <br>
            <div class="d-flex justify-content-start">
                <?php if ($assessment_mode_code == 1): ?>
                    <button class="btn btn-secondary btn-sm" id="edit_time_limit_btn" style="margin-right: 15px;">Edit Time Limit</button>
                <?php endif; ?>
                
                <button class="btn btn-primary btn-sm" id="add_question_btn">
                    <i class="fa fa-plus"></i> Add Question
                </button>
            </div>
        </div>

        <br>
        <br>

        <?php
        $questions_query = "SELECT * FROM questions WHERE assessment_id = ? ORDER BY order_by ASC";
        if ($stmt = $conn->prepare($questions_query)) {
            $stmt->bind_param("i", $assessment_id);
            $stmt->execute();
            $questions_result = $stmt->get_result();

            if ($questions_result->num_rows > 0) {
                echo '<div class="card card-full-width">';
                echo '<div class="card-header">Questions</div>';
                echo '<div class="card-body">';
                echo '<ul class="list-group scrollable-list">';
                
                while ($row = $questions_result->fetch_assoc()) {
                    echo '<li class="list-group-item">';
                    echo htmlspecialchars($row['question']);
                    echo '<p><strong>Points:</strong> ' . htmlspecialchars($row['total_points']) . '</p>';
                    if ($assessment_mode_code == 2 || $assessment_mode_code == 3) {
                        echo '<p><strong>Time Limit:</strong> ' . htmlspecialchars($row['time_limit']) . ' seconds</p>';
                    }
                    echo '<div class="float-right">';
                    echo '<button class="btn btn-sm btn-outline-primary edit_question" data-id="' . htmlspecialchars($row['question_id']) . '" type="button"><i class="fa fa-edit"></i></button>';
                    echo '<button class="btn btn-sm btn-outline-danger remove_question" data-id="' . htmlspecialchars($row['question_id']) . '" type="button"><i class="fa fa-trash"></i></button>';
                    echo '</div>';
                    echo '</li>';
                }
                
                echo '</ul>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<p>No questions found for this assessment.</p>';
            }

            $stmt->close();
        } else {
            echo '<p>Error preparing the SQL query for questions.</p>';
        }
        ?>
    </div>

    <!-- Modal for Adding/Editing Questions -->
    <div class="modal fade" id="manage_question" tabindex="-1" aria-labelledby="manageQuestionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="manageQuestionLabel">Add New Question</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <div class="form-group">
                            <label for="points">Points:</label>
                            <input type="number" id="points" name="points" class="form-control" required>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="save_question_btn" type="submit" class="btn btn-primary">Save Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for editing the time limit -->
    <div class="modal fade" id="edit_time_limit_modal" tabindex="-1" aria-labelledby="editTimeLimitLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTimeLimitLabel">Edit Time Limit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-time-limit-form">
                        <div class="form-group">
                            <label for="assessment_time_limit">Time Limit (minutes):</label>
                            <input type="number" class="form-control" id="assessment_time_limit" name="assessment_time_limit" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save_time_limit">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        // Show/hide question type options based on selection
        $('#question_type').change(function() {
            $('.question-type-options').hide();
            $('#' + $(this).val() + '_options').show();
            
            // Remove 'required' attribute from hidden fields
            $('.question-type-options:hidden').find('[required]').prop('required', false);
            
            // Add 'required' attribute to visible fields
            $('#' + $(this).val() + '_options').find('input, textarea').prop('required', true);

            // Initialize options for multiple choice and checkbox
            if ($(this).val() === 'multiple_choice' || $(this).val() === 'checkbox') {
                initializeOptions($(this).val());
            }
        });

        // Function to initialize options for multiple choice and checkbox
        function initializeOptions(type) {
            var optionsContainer = $('#' + type + '_options');
            if (optionsContainer.find('.option-group').length === 0) {
                // Add one initial option if none exist
                addOption(type);
            }
        }

        // Function to add a new option
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

        // Add option buttons
        $(document).on('click', '#add_mc_option, #add_cb_option', function() {
            var type = $(this).attr('id').includes('mc') ? 'multiple_choice' : 'checkbox';
            addOption(type);
        });

        // Remove option button
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
            
            // Validation logic
            var isValid = true;
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
                return;
            }
            
            // Additional validation for specific question types
            switch(questionType) {
                case 'multiple_choice':
                    if ($('#' + questionType + '_options .option-group').length < 2) {
                        $('#msg').html('<div class="alert alert-danger">Please add at least two options.</div>');
                        return;
                    }
                    if ($('#' + questionType + '_options input[name="is_right"]:checked').length === 0) {
                        $('#msg').html('<div class="alert alert-danger">Please select the correct answer.</div>');
                        return;
                    }
                    break;
                case 'checkbox':
                    if ($('#' + questionType + '_options .option-group').length < 2) {
                        $('#msg').html('<div class="alert alert-danger">Please add at least two options.</div>');
                        return;
                    }
                    // Check if at least one checkbox is selected
                    if ($('#' + questionType + '_options input[name="is_right[]"]:checked').length === 0) {
                        $('#msg').html('<div class="alert alert-danger">Please select at least one correct answer.</div>');
                        return;
                    }
                    break;
                case 'true_false':
                    if (!$('input[name="tf_answer"]:checked').val()) {
                        $('#msg').html('<div class="alert alert-danger">Please select True or False.</div>');
                        return;
                    }
                    break;
            }
    
            // Add time limit validation for Quiz Bee and Speed Mode
            var mode = '<?php echo $assessment_mode_code; ?>';
            if ((mode == '2' || mode == '3') && !$('#time_limit').val()) {
                $('#msg').html('<div class="alert alert-danger">Please enter a time limit for this question.</div>');
                return;
            }
            
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
                        $('#save_question_btn').prop('disabled', true).text('Saved'); // Disable the button
                        setTimeout(function() {
                            $('#manage_question').modal('hide');
                            location.reload();
                        }, 1000);
                    } else {
                        $('#msg').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                    $('#msg').html('<div class="alert alert-danger">An error occurred while saving the question. Please try again.</div>');
                }
            });
        });

        // Add Question Button
        $(document).on('click', '#add_question_btn', function() {
            // Clear the form
            $('#question-frm')[0].reset();
            
            // Reset question type and trigger change event
            $('#question_type').val('').trigger('change');
            
            // Clear any existing messages
            $('#msg').html('');
            
            // Clear any existing options for multiple choice and checkbox questions
            $('#multiple_choice_options .form-group, #checkbox_options .form-group').empty();
            
            // Show the modal
            $('#manage_question').modal('show');
        });

        // Edit question button
        $(document).on('click', '.edit_question', function() {
            var questionId = $(this).data('id');
            
            // Fetch question details
            $.ajax({
                type: 'GET',
                url: 'get_question.php',
                data: { question_id: questionId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        populateQuestionForm(response.data);
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

        // Delete question button
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

        // Edit time limit button
        $('#edit_time_limit_btn').click(function() {
            var currentTimeLimit = $('#current-time-limit').text();
            $('#assessment_time_limit').val(currentTimeLimit === 'Not set' ? '' : currentTimeLimit);
            $('#edit_time_limit_modal').modal('show');
        });

        // Save time limit button
        $('#save_time_limit').click(function() {
            var newTimeLimit = $('#assessment_time_limit').val();
            if (newTimeLimit === '') {
                alert('Please enter a valid time limit.');
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'update_assessment_time_limit.php',
                data: {
                    assessment_id: <?php echo $assessment_id; ?>,
                    time_limit: newTimeLimit
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#current-time-limit').text(newTimeLimit === '0' ? 'Not set' : newTimeLimit);
                        $('#edit_time_limit_modal').modal('hide');
                        alert('Time limit updated successfully.');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + ": " + error);
                    alert('An error occurred while updating the time limit. Please try again.');
                }
            });
        });

        // Function to populate the question form
        function populateQuestionForm(data) {
            $('#question-frm')[0].reset();
            $('#question_type').val(data.question_type).trigger('change');
            $('input[name="id"]').val(data.question_id);
            $('#question').val(data.question);
            $('#points').val(data.total_points);

            // Add time limit handling
            if (data.time_limit) {
                $('#time_limit').val(data.time_limit);
            }

            switch(data.question_type) {
                case 'multiple_choice':
                case 'checkbox':
                    $('#' + data.question_type + '_options .form-group').empty();
                    data.options.forEach(function(option, index) {
                        var newOption = `
                            <div class="option-group d-flex align-items-center mb-2">
                                <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" required>${option.option_txt}</textarea>
                                <label><input type="${data.question_type === 'multiple_choice' ? 'radio' : 'checkbox'}" name="${data.question_type === 'multiple_choice' ? 'is_right' : 'is_right[]'}" value="${index}" ${option.is_right ? 'checked' : ''} required></label>
                                <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                            </div>
                        `;
                        $('#' + data.question_type + '_options .form-group').append(newOption);
                    });
                    break;
                case 'true_false':
                    $(`input[name="tf_answer"][value="${data.options[0].option_txt}"]`).prop('checked', true);
                    break;
                case 'identification':
                    $('#identification_answer').val(data.options[0].option_txt);
                    break;
                case 'fill_blank':
                    $('#fill_blank_answer').val(data.options[0].option_txt);
                    break;
            }
        }

        // Function to handle assessment mode change
        function handleAssessmentModeChange() {
            var mode = '<?php echo $assessment_mode_code; ?>';
            if (mode == '2' || mode == '3') { // Quiz Bee Mode or Speed Mode
                $('#time_limit_container').show();
                $('#time_limit').prop('required', true);
            } else {
                $('#time_limit_container').hide();
                $('#time_limit').prop('required', false);
            }
        }

        // Call the function on page load
        handleAssessmentModeChange();
    });
    </script>
</body>
</html>