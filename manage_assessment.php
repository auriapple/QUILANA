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
            $query = "SELECT a.assessment_name, a.assessment_type, a.assessment_mode, c.course_name, cl.subject 
                      FROM assessment a
                      JOIN class cl ON a.class_id = cl.class_id
                      JOIN course c ON cl.course_id = c.course_id
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
            <?php endif; ?>
        

        <br><button class="btn btn-primary btn-sm" id="add_question_btn" data-bs-toggle="modal" data-bs-target="#manage_question"><i class="fa fa-plus"></i> Add Question</button>
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
                                <label for="identification_answer" >Correct Answer: </label>
                                <input type="text" id="identification_answer" name="identification_answer" required>
                            </div>
                        </div>

                        <!-- Fill in the Blank Options -->
                        <div id="fill_blank_options" class="question-type-options">
                            <div class="form-group">
                                <label for="fill_blank_answer" class="mb-0 me-2">Correct Answer:</label>
                                <input type="text" id="fill_blank_answer" name="fill_blank_answer" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Question</button>
                    </div>
                </form>
                <div id="msg"></div>
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
        });

        $('#add_mc_option, #add_cb_option').click(function() {
            var optionType = $(this).attr('id').includes('mc') ? 'mc' : 'cb';
            var optionsContainer = $('#' + optionType + '_options');
            var optionCount = optionsContainer.find('.option-group').length + 1;
            
            var newOption = `
                <div class="option-group d-flex align-items-center mb-2">
                    <textarea rows="2" name="question_opt[]" id="${optionType}_option_${optionCount}" class="form-control flex-grow-1 mr-2"></textarea>
                    <label><input type="${optionType === 'mc' ? 'radio' : 'checkbox'}" name="${optionType === 'mc' ? 'is_right' : 'is_right[]'}" value="${optionCount - 1}"></label>
                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                </div>
            `;
            optionsContainer.append(newOption);
        });

        $(document).on('click', '.remove-option', function() {
            $(this).closest('.option-group').remove();
        });

        $('#question-frm').submit(function(e) {
            e.preventDefault();
            
            var questionType = $('#question_type').val();
            var formData = new FormData(this);
            
            // Validate form
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
                case 'checkbox':
                    if ($('#' + questionType + '_options .option-group').length === 0) {
                        $('#msg').html('<div class="alert alert-danger">Please add at least one option.</div>');
                        return;
                    }
                    if ($('#' + questionType + '_options input[type="' + (questionType === 'multiple_choice' ? 'radio' : 'checkbox') + '"]:checked').length === 0) {
                        $('#msg').html('<div class="alert alert-danger">Please select the correct answer(s).</div>');
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
                        setTimeout(function() {
                            $('#manage_question').modal('hide');
                            location.reload();
                        }, 2000);
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
        $('#question_type').trigger('change');

        // Handle edit question button click
        $('.edit_question').click(function() {
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

        function populateQuestionForm(data) {
            $('#question-frm')[0].reset();
            $('#question_type').val(data.question_type).trigger('change');
            $('input[name="id"]').val(data.question_id);
            $('#question').val(data.question);
            $('#points').val(data.total_points);

            switch(data.question_type) {
                case 'multiple_choice':
                case 'checkbox':
                    $('#' + data.question_type + '_options').empty();
                    data.options.forEach(function(option, index) {
                        var newOption = `
                            <div class="option-group d-flex align-items-center mb-2">
                                <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2">${option.option_txt}</textarea>
                                <label><input type="${data.question_type === 'multiple_choice' ? 'radio' : 'checkbox'}" name="${data.question_type === 'multiple_choice' ? 'is_right' : 'is_right[]'}" value="${index}" ${option.is_right ? 'checked' : ''}></label>
                                <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                            </div>
                        `;
                        $('#' + data.question_type + '_options').append(newOption);
                    });
                    break;
                case 'true_false':
                    $(`input[name="tf_answer"][value="${data.options[0].option_txt}"]`).prop('checked', true);
                    break;
                case 'identification':
                case 'fill_blank':
                    $(`#${data.question_type}_answer`).val(data.answer);
                    break;
            }
        }

        // Handle delete question button click
        $('.remove_question').click(function() {
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

        // Add Question Button
        $('#add_question_btn').click(function() {
            // Clear the form
            $('#question-frm')[0].reset();
            
            // Reset question type and trigger change event
            $('#question_type').val('multiple_choice').trigger('change');
            
            // Clear any existing messages
            $('#msg').html('');
            
            // Clear any existing options for multiple choice and checkbox questions
            $('#multiple_choice_options, #checkbox_options').empty();
            
            // Show the modal
            $('#manage_question').modal('show');
        });
    });
    </script>
</body>
</html>