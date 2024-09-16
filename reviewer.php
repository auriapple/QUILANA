<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Reviewer | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="content-wrapper">
        <div class="scrollable-content">
            <!-- Header Container -->
            <div class="add-course-container">
                <button class="secondary-button" id="add_reviewer_button">Add Reviewer</button>
                <form class="search-bar" action="#" method="GET">
                    <input type="text" name="query" placeholder="Search" required>
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>

            <div class="tabs-container">
                <ul class="tabs">
                    <li class="tab-link active" data-tab="courses-tab">Reviewer</li>
                    <li class="tab-link" id="classes-tab-link" style="display: none;" data-tab="classes-tab">Reviewer Details</li>
                </ul>
            </div>

            <div id="courses-tab" class="tab-content active">
                <div class="course-container">
                    <?php
                    $qry = $conn->query("SELECT * FROM rw_reviewer WHERE student_id = '".$_SESSION['login_id']."' ORDER BY topic ASC");
                    if ($qry->num_rows > 0) {
                        while ($row = $qry->fetch_assoc()) {
                            $reviewer_id =  $row['reviewer_id'];
                    ?>
                    <div class="course-card">
                        <div class="course-card-body">
                            <div class="meatball-menu-container">
                                <button class="meatball-menu-btn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="meatball-menu">
                                    <a href="#" class="edit_reviewer" data-id="<?php echo $reviewer_id ?>">Edit</a>
                                    <a href="#" class="share_reviewer" data-id="<?php echo $reviewer_id ?>">Share Code</a>
                                </div>
                            </div>
                            <div class="course-card-title"><?php echo $row['reviewer_name'] ?></div>
                            <div class="course-card-text">Topic: <br><?php echo $row['topic'] ?></div>
                            <div class="course-actions">
                                <a class="tertiary-button" id="view_reviewer_details" 
                                href="manage_reviewer.php?reviewer_id=<?php echo $reviewer_id ?>" type="button"> Manage</a>                                
                                <button class="main-button" id="take_reviewer" data-id="<?php echo $row['reviewer_id']; ?>" data-type="<?php echo $row['reviewer_type']; ?>" type="button">Take Reviewer</button>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Modal for adding reviewer -->
            <div class="modal fade" id="addReviewerModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="addReviewerModalLabel">Add Reviewer</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="addReviewerForm" action="" method="POST">
                            <div class="modal-body">
                                <div id="msg"></div>
                                <div class="form-group">
                                    <label for="reviewer_type">Select Reviewer Type</label>
                                    <select id="reviewer_type" name="reviewer_type" class="form-control" required>
                                        <option value="test">Test</option>
                                        <option value="flashcard">Flashcard</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="reviewer_name">Reviewer Name</label>
                                    <input type="text" id="reviewer_name" name="reviewer_name" class="form-control" placeholder="Enter Reviewer Name" required>
                                </div>
                                <div class="form-group">
                                    <label for="topic">Topic</label>
                                    <input type="text" id="topic" name="topic" class="form-control" placeholder="Enter Topic" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Create Reviewer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <script>
        // To trigger the modal when clicking "Add Reviewer" button
        document.getElementById('add_reviewer_button').onclick = function() {
            $('#addReviewerModal').modal('show');
        };

        document.getElementById('addReviewerForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // Fetch the form data
            var formData = new FormData(this);

            fetch('save_reviewer.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                // Handle success, show a message, or close the modal
                alert('Reviewer added successfully');
                $('#addReviewerModal').modal('hide'); // Close the modal
                location.reload(); // Optionally reload the page
            })
            .catch(error => {
                // Handle error
                console.error('Error:', error);
            });
        });
        </script>
</body>
</html>
