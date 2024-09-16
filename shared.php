<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <?php include('auth.php'); ?>
    <?php include('db_connect.php'); ?>
    <title>Shared Reviewers | Quilana</title>
    <link rel="stylesheet" href="meatballMenuTest/meatball.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="content-wrapper">
            <div class="add-course-container">
                <button class="secondary-button" id="get_code"> Get Code </button>
                <form class="search-bar" action="#" method="GET">
                    <input type="text" name="query" placeholder="Search" required>
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>

            <div class="tabs-container">
                <ul class="tabs">
                    <li class="tab-link active" data-tab="assessment-tab">Reviewers</li>
                    <li class="tab-link" id="details-tab-link" style="display: none;" data-tab="details-tab">Assessment Details</li>
                </ul>
            </div>
    </div>
</body>
</html>