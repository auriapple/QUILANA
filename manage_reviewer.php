<?php
include('header.php');
include('auth.php');
include('db_connect.php');

if (!isset($_GET['reviewer_id'])) {
    echo "<p>Reviewer ID not provided.</p>";
    exit();
}

$reviewer_id = intval($_GET['reviewer_id']);
$student_id = $_SESSION['login_id'];

// Fetch reviewer details
$query = "SELECT * FROM rw_reviewer WHERE reviewer_id = ? AND student_id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("ii", $reviewer_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reviewer = $result->fetch_assoc();
        $reviewer_name = htmlspecialchars($reviewer['reviewer_name']);
        $topic = htmlspecialchars($reviewer['topic']);
        $reviewer_type = $reviewer['reviewer_type'];
        $reviewer_type_name = ($reviewer_type == 1) ? 'Test' : 'Flashcard';
    } else {
        echo "<p>No reviewer found with the provided ID.</p>";
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
    <title>Manage Reviewer | Quilana</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f4f4;
        }
        .reviewer-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            margin-right: 26px;
        }
        .reviewer-details h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .reviewer-details p {
            margin-bottom: 5px;
            font-size: 1em;
            color: #666;
        }
        .card-full-width {
            width: 98%;
            margin-bottom: 20px;
        }
        .card-body {
            max-height: 400px;
            overflow-y: auto;
        }
        .list-group-item {
            margin-top: 10px;
            border-left: 4px solid #4A4CA6;
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
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="content-wrapper">
        <div class="back-arrow">
            <a href="reviewer.php"> 
                <i class="fa fa-arrow-left"></i> 
            </a>
        </div>

        <div class="reviewer-details">
            <h2><?php echo $reviewer_name; ?></h2>
            <p><strong>Reviewer Type:</strong> <?php echo $reviewer_type_name; ?></p>
            <p><strong>Topic:</strong> <?php echo $topic; ?></p>
            <button class="btn btn-primary mt-3" id="add_item_btn">
                <i class="fa fa-plus"></i> Add <?php echo ($reviewer_type == 1) ? 'Question' : 'Flashcard'; ?>
            </button>
        </div>

        <?php if ($reviewer_type == 1): // Test type ?>
            <?php
            $questions_query = "SELECT * FROM rw_questions WHERE reviewer_id = ? ORDER BY order_by ASC";
            if ($stmt = $conn->prepare($questions_query)) {
                $stmt->bind_param("i", $reviewer_id);
                $stmt->execute();
                $questions_result = $stmt->get_result();

                if ($questions_result->num_rows > 0) {
                    echo '<div class="card card-full-width">';
                    echo '<div class="card-header">Questions</div>';
                    echo '<div class="card-body">';
                    echo '<ul class="list-group">';
                    
                    while ($row = $questions_result->fetch_assoc()) {
                        echo '<li class="list-group-item">';
                        echo htmlspecialchars($row['question']);
                        echo '<div class="float-right">';
                        echo '<button class="btn btn-sm btn-outline-primary edit_question me-2" data-id="' . htmlspecialchars($row['rw_question_id']) . '"><i class="fa fa-edit"></i></button>';
                        echo '<button class="btn btn-sm btn-outline-danger remove_question" data-id="' . htmlspecialchars($row['rw_question_id']) . '"><i class="fa fa-trash"></i></button>';
                        echo '</div>';
                        echo '</li>';
                    }
                    
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p class="alert alert-info" style="margin-right: 20px;">No questions found for this reviewer. Start by adding some questions!</p>';
                }

                $stmt->close();
            } else {
                echo '<p class="alert alert-danger" style="margin-right: 20px;">Error preparing the SQL query for questions.</p>';
            }
            ?>
        <?php else: // Flashcard type ?>
            <?php
            $flashcards_query = "SELECT * FROM flashcard WHERE reviewer_id = ? ORDER BY flashcard_id ASC";
            if ($stmt = $conn->prepare($flashcards_query)) {
                $stmt->bind_param("i", $reviewer_id);
                $stmt->execute();
                $flashcards_result = $stmt->get_result();

                if ($flashcards_result->num_rows > 0) {
                    echo '<div class="card card-full-width">';
                    echo '<div class="card-header">Flashcards</div>';
                    echo '<div class="card-body">';
                    echo '<ul class="list-group">';
                    
                    while ($row = $flashcards_result->fetch_assoc()) {
                        echo '<li class="list-group-item">';
                        echo '<strong>Term:</strong> ' . htmlspecialchars($row['term']);
                        echo '<br><strong>Definition:</strong> ' . htmlspecialchars($row['definition']);
                        echo '<div class="float-right">';
                        echo '<button class="btn btn-sm btn-outline-primary edit_flashcard me-2" data-id="' . htmlspecialchars($row['flashcard_id']) . '"><i class="fa fa-edit"></i></button>';
                        echo '<button class="btn btn-sm btn-outline-danger remove_flashcard" data-id="' . htmlspecialchars($row['flashcard_id']) . '"><i class="fa fa-trash"></i></button>';
                        echo '</div>';
                        echo '</li>';
                    }
                    
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p class="alert alert-info" style="margin-right: 20px;">No flashcards found for this reviewer. Start by adding some flashcards!</p>';
                }

                $stmt->close();
            } else {
                echo '<p class="alert alert-danger" style="margin-right: 20px;">Error preparing the SQL query for flashcards.</p>';
            }
            ?>
        <?php endif; ?>
    </div>

<!-- Modal for Adding/Editing Questions -->
<div class="modal fade" id="manage_question" tabindex="-1" aria-labelledby="manageQuestionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="manageQuestionLabel">Add New Question</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                        <input type="hidden" name="reviewer_id" value="<?php echo $reviewer_id; ?>" />
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
                    <div id="multiple_choice_options" class="question-type-options" style="display: none;">
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
                    <div id="checkbox_options" class="question-type-options" style="display: none;">
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
                    <div id="true_false_options" class="question-type-options" style="display: none;">
                        <div class="form-group text-center">
                            <label>Correct Answer:</label>
                            <div class="d-inline-flex align-items-center">
                                <label class="mr-3"><input type="radio" name="tf_answer" value="true" required> True</label>
                                <label><input type="radio" name="tf_answer" value="false" required> False</label>
                            </div>
                        </div>
                    </div>

                    <!-- Identification Options -->
                    <div id="identification_options" class="question-type-options" style="display: none;">
                        <div class="form-group">
                            <label for="identification_answer">Correct Answer:</label>
                            <input type="text" id="identification_answer" name="identification_answer" class="form-control" required>
                        </div>
                    </div>

                    <!-- Fill in the Blank Options -->
                    <div id="fill_blank_options" class="question-type-options" style="display: none;">
                        <div class="form-group">
                            <label for="fill_blank_answer">Correct Answer:</label>
                            <input type="text" id="fill_blank_answer" name="fill_blank_answer" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="save_question_btn" type="submit" class="btn btn-primary">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Adding/Editing Flashcards -->
<div class="modal fade" id="manage_flashcard" tabindex="-1" aria-labelledby="manageFlashcardLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageFlashcardLabel">Add New Flashcard</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="flashcard-frm">
                <div class="modal-body">
                    <div id="flashcard_msg"></div>
                    <div class="mb-3">
                        <label for="term" class="form-label">Term</label>
                        <input type="hidden" name="reviewer_id" value="<?php echo $reviewer_id; ?>" />
                        <input type="hidden" name="id" />
                        <input type="text" id="term" name="term" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="definition" class="form-label">Definition</label>
                        <textarea id="definition" rows="3" name="definition" required class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Flashcard</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Function to reset the question form
    function resetQuestionForm() {
        $('#question-frm')[0].reset();
        $('input[name="id"]').val(''); // Clear the hidden id field
        $('#question_type').val('').trigger('change');
        $('.question-type-options').hide();
        $('#multiple_choice_options .form-group, #checkbox_options .form-group').empty();
        $('#msg').empty();
        $('#save_question_btn').prop('disabled', false).text('Save Question');
        $('#manageQuestionLabel').text('Add New Question');
    }

    // Function to initialize options for multiple choice and checkbox
    function initializeOptions(type) {
        var optionsContainer = $('#' + type + '_options');
        if (optionsContainer.find('.option-group').length === 0) {
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

    // Show/hide question type options based on selection
    $('#question_type').change(function() {
        $('.question-type-options').hide();
        $('#' + $(this).val() + '_options').show();
        
        $('.question-type-options:hidden').find('[required]').prop('required', false);
        $('#' + $(this).val() + '_options').find('input, textarea').prop('required', true);

        if ($(this).val() === 'multiple_choice' || $(this).val() === 'checkbox') {
            initializeOptions($(this).val());
        }
    });

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
        
        var formData = new FormData(this);
        var questionType = $('#question_type').val();
        
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
            case 'checkbox':
                if ($('#' + questionType + '_options .option-group').length < 2) {
                    $('#msg').html('<div class="alert alert-danger">Please add at least two options.</div>');
                    return;
                }
                if ($('#' + questionType + '_options input[name="' + (questionType === 'multiple_choice' ? 'is_right' : 'is_right[]') + '"]:checked').length === 0) {
                    $('#msg').html('<div class="alert alert-danger">Please select ' + (questionType === 'multiple_choice' ? 'the correct answer.' : 'at least one correct answer.') + '</div>');
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
            url: 'save_reviewer_question.php',
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
                console.error("AJAX Error: " + status + ": " + error);
                $('#msg').html('<div class="alert alert-danger">An error occurred while saving the question. Please try again.</div>');
            }
        });
    });

    // Add Question/Flashcard Button
    $(document).on('click', '#add_item_btn', function() {
        var reviewerType = '<?php echo $reviewer_type; ?>';
        if (reviewerType == '1') {
            resetQuestionForm();
            $('#manage_question').modal('show');
        } else {
            clearFlashcardForm();
            $('#manage_flashcard').modal('show');
        }
    });

    // Flashcard form submission
    $('#flashcard-frm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: 'save_reviewer_flashcard.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#flashcard_msg').html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(function() {
                        $('#manage_flashcard').modal('hide');
                        location.reload();
                    }, 1500);
                } else {
                    $('#flashcard_msg').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + ": " + error);
                $('#flashcard_msg').html('<div class="alert alert-danger">An error occurred while saving the flashcard. Please try again.</div>');
            }
        });
    });

    // Edit question button click handler
    $(document).on('click', '.edit_question', function() {
        const questionId = $(this).data('id');
        
        $.ajax({
            type: 'GET',
            url: 'get_reviewer_question.php',
            data: { rw_question_id: questionId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateQuestionForm(response.data);
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

    // Edit flashcard button click handler
    $(document).on('click', '.edit_flashcard', function() {
        const flashcardId = $(this).data('id');
        
        $.ajax({
            type: 'GET',
            url: 'get_reviewer_flashcard.php',
            data: { flashcard_id: flashcardId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateFlashcardForm(response.data);
                    $('#manage_flashcard').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + ": " + error);
                alert('An error occurred while fetching the flashcard details. Please try again.');
            }
        });
    });

    // Remove question button click handler
    $(document).on('click', '.remove_question', function() {
        const questionId = $(this).data('id');
        if (confirm('Are you sure you want to delete this question?')) {
            $.ajax({
                type: 'POST',
                url: 'delete_reviewer_question.php',
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

    // Remove flashcard button click handler
    $(document).on('click', '.remove_flashcard', function() {
        const flashcardId = $(this).data('id');
        if (confirm('Are you sure you want to delete this flashcard?')) {
            $.ajax({
                type: 'POST',
                url: 'delete_reviewer_flashcard.php',
                data: { flashcard_id: flashcardId },
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
                    alert('An error occurred while deleting the flashcard. Please try again.');
                }
            });
        }
    });

    function populateQuestionForm(data) {
        resetQuestionForm(); 
        
        $('#question_type').val(data.question_type).trigger('change');
        $('input[name="id"]').val(data.rw_question_id);
        $('#question').val(data.question);
        $('#points').val(data.total_points);

        switch(data.question_type) {
            case 'multiple_choice':
            case 'checkbox':
                $('#' + data.question_type + '_options .form-group').empty();
                if (Array.isArray(data.options)) {
                    data.options.forEach(function(option, index) {
                        if (option.option_txt !== undefined) { 
                            var newOption = `
                                <div class="option-group d-flex align-items-center mb-2">
                                    <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" required>${option.option_txt}</textarea>
                                    <label><input type="${data.question_type === 'multiple_choice' ? 'radio' : 'checkbox'}" name="${data.question_type === 'multiple_choice' ? 'is_right' : 'is_right[]'}" value="${index}" ${option.is_right ? 'checked' : ''} required></label>
                                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                                </div>
                            `;
                            $('#' + data.question_type + '_options .form-group').append(newOption);
                        } else {
                            console.warn('Option is missing option_txt:', option);
                        }
                    });
                } else {
                    console.warn('Options is not an array:', data.options);
                }
                break;
            case 'true_false':
                if (Array.isArray(data.options) && data.options[0] && data.options[0].option_txt !== undefined) {
                    $(`input[name="tf_answer"][value="${data.options[0].option_txt}"]`).prop('checked', true);
                } else {
                    console.warn('Options is not valid for true_false:', data.options);
                }
                break;
            case 'identification':
                if (data.answer !== undefined) {
                    $('#identification_answer').val(data.answer);
                } else {
                    console.warn('Answer is not defined for identification:', data.answer);
                }
                break;
            case 'fill_blank':
                if (data.answer !== undefined) {
                    $('#' + data.question_type + '_answer').val(data.answer);
                } else {
                    console.warn('Answer is not defined for ' + data.question_type + ':', data.answer);
                }
                break;
        }
    }

    // Function to populate flashcard form for editing
    function populateFlashcardForm(data) {
        $('#flashcard-frm')[0].reset();
        $('#flashcard-frm [name="id"]').val(data.flashcard_id);
        $('#flashcard-frm [name="term"]').val(data.term);
        $('#flashcard-frm [name="definition"]').val(data.definition);
        $('#manageFlashcardLabel').text('Edit Flashcard');
    }

    // Function to clear the flashcard form
    function clearFlashcardForm() {
        $('#flashcard-frm')[0].reset();
        $('#manageFlashcardLabel').text('Add New Flashcard');
    }
});
</script>
</body>
</html>