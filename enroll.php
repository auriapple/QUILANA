<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Courses | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="container-fluid admin">
        <div class="add-course-container">
           <!-- Changed button text and ID back to "Join Class" -->
            <button class="btn btn-primary btn-sm join-btn" id="join_class"><i class="fa fa-plus"></i>Join Class</button>
            <div class="search-bar">
                <form action="#" method="GET">
                    <input type="text" name="query" placeholder="Search" required>
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>

        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="classes-tab">Classes</li>
                
            </ul>
        </div>

        <div id="classes-tab" class="tab-content active">
            <div class="course-container">
                
            </div>
        </div>

        <!-- Modal with updated text -->
        <div class="modal fade" id="manage_class" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Join Class</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id='code-frm' action="" method="POST">
                        <div class="modal-body">
                            <div id="msg"></div>
                            <div class="form-group">
                                <input type="text" name="get_code" required="required" class="form-control" placeholder="Class Code" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit "class="btn btn-primary" name="join_by_code"><span class="glyphicon glyphicon-save"></span>Join</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="row">
  <div class="col-sm-3 mb-3 mb-sm-0">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Special title treatment</h5>
        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Special title treatment</h5>
        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>
  </div>
  
</div>

</div>

        <script>
            $(document).ready(function() {
                // Show the appropriate button based on the active tab
                function updateButtons() {
                    var activeTab = $('.tab-link.active').data('tab');
                    $('.join-btn').hide(); // Hide all buttons initially

                    if (activeTab === 'classes-tab') {
                        $('#join_class').show();
                    } else if (activeTab === '') {
                        $('#add_class').show();
                    }
                }

                // Handle tab switching
                $('.tab-link').click(function() {
                    var tab_id = $(this).attr('data-tab');

                    // Remove active class from all tabs and content
                    $('.tab-link').removeClass('active');
                    $('.tab-content').removeClass('active');

                    // Add active class to the clicked tab and corresponding content
                    $(this).addClass('active');
                    $("#" + tab_id).addClass('active');

                    // Update buttons visibility
                    updateButtons();
                });

                // Show the correct button when the page loads
                updateButtons();

                // When "Join Class" button is clicked
                $('#join_class').click(function() {
                    $('#msg').html('');
                    $('#manage_class #code-frm').get(0).reset();
                    $('#manage_class').modal('show');
                });

                // Joining Class
                $('#code-frm').submit(function(e) {
                    e.preventDefault();
                    $('#code-frm [name="join"]').attr('disabled', true).html('Joining...');
                    $('#msg').html('');

                    $.ajax({
                        url: './join_class.php',
                        method: 'POST',
                        data: $(this).serialize(),
                        error: function(err) {
                            console.log(err);
                            alert('An error occurred');
                            $('#code-frm [name="join"]').removeAttr('disabled').html('Join');
                        },
                        success: function(resp) {
                            if (typeof resp != undefined) {
                                resp = JSON.parse(resp);
                                if (resp.status == 1) {
                                    alert('Data successfully saved');
                                    location.reload();
                                } else {
                                    $('#msg').html('<div class="alert alert-danger">' + resp.msg + '</div>');
                                }
                            }
                        }
                    });
                });

               
        });
        </script>
    </body>
</html>
