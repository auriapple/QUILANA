<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
		body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .navbar {
            background-image: linear-gradient(to right, #1E1A43, #4A4CA6);
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            z-index: 1000;
        }
        .navbar h3 {
            margin: 0;
        }
        #sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #f8f9fa;
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
            z-index: 999;
        }
        #sidebar.active {
            left: 0;
        }
        .sidebar-item {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #818181;
            display: block;
            transition: 0.3s;
        }
        .sidebar-item:hover {
            color: #4A4CA6;
        }
        .sidebar-icon {
            margin-right: 10px;
        }
        .content-wrapper {
            transition: margin-left 0.3s;
            margin-left: 20px;
            padding-top: 60px;
        }
        .content-wrapper.active {
            margin-left: 270px;
        }
        #sidebarCollapse {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        @media screen and (max-width: 768px) {
            #sidebar {
                width: 100%;
                left: -100%;
            }
            #sidebar.active {
                width: 100%;
            }
            .content-wrapper.active {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <button id="sidebarCollapse">
            <i class="fa fa-bars"></i>
        </button>
        <h3>QUILANA</h3>
        <a href="logout.php" style="color:white">
            <?php echo $firstname ?> <i class="fa fa-power-off"></i>
        </a>
    </nav>

    <div id="sidebar">
        <?php if($_SESSION['login_user_type'] != 3): ?>
            <a href="faculty_dashboard.php" class="sidebar-item">
                <i class="fa fa-home sidebar-icon"></i> Dashboard
            </a>
            <a href="classes.php" class="sidebar-item">
                <i class="fa fa-list-alt sidebar-icon"></i> Classes
            </a>
        <?php else: ?>
            <a href="student_dashboard.php" class="sidebar-item">
                <i class="fa fa-home sidebar-icon"></i> Dashboard
            </a>
            <a href="class_enrolled.php" class="sidebar-item">
                <i class="fa fa-book sidebar-icon"></i> Classes
            </a>
            <a href="reviewer.php" class="sidebar-item">
                <i class="fa-solid fa-folder sidebar-icon"></i> Reviewer
            </a>
			<a href="shared.php" class="sidebar-item">
                <i class="fa-solid fa-share-from-square sidebar-icon"></i> Shared
            </a>
        <?php endif; ?>
    </div>

	<script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function(e) {
                e.stopPropagation(); // Prevent event from bubbling up
                $('#sidebar, .content-wrapper').toggleClass('active');
            });

            // Close sidebar when clicking outside of it on mobile
            $(document).on('click', function(event) {
                var windowWidth = $(window).width();
                if (windowWidth <= 768 && !$(event.target).closest('#sidebar, #sidebarCollapse').length) {
                    $('#sidebar, .content-wrapper').removeClass('active');
                }
            });

            // Prevent sidebar from closing when clicking on menu items
            $('#sidebar').on('click', function(e) {
                e.stopPropagation(); // Prevent event from bubbling up
            });

            // Highlight active menu item
            var loc = window.location.href;
            $('#sidebar a').each(function() {
                if ($(this).attr('href') == loc.substr(loc.lastIndexOf("/") + 1)) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>