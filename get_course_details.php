<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="assets/css/custom-tables.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Course Details</title>
</head>
<body>
    <?php
    include('db_connect.php');

    if (isset($_GET['course_id'])) {
        $course_id = $conn->real_escape_string($_GET['course_id']);

        // Fetch the course details
        $qry_course = $conn->query("SELECT course_name FROM course WHERE course_id = '$course_id'");
        if ($qry_course->num_rows > 0) {
            $course = $qry_course->fetch_assoc();
            echo "<h4><strong>{$course['course_name']}</strong></h4>";
        } else {
            echo "<p><strong>Course not found.</strong></p>";
        }

        // Fetch the class details associated with the course
        $qry_class = $conn->query("SELECT * FROM class WHERE course_id = '$course_id'");

        // Display the table
        echo '<div class="course-details-table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Course Subject</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

        if ($qry_class->num_rows > 0) {
            while ($class = $qry_class->fetch_assoc()) {

                echo '<tr>
                        <td>' . $class['class_name'] . '</td>
                        <td>' . $class['subject'] . '</td>
                        <td>' ?> <button class="btn btn-primary btn-sm view_class_details action_vcd" data-id="<?php echo $class['class_id']?>"  type="button">View Class</button> <?php '</td>
                    </tr>';
            }
        } else {
            // Show an empty row if no class data is available
            echo '<tr>
                    <td colspan="5" class="text-center">No classes found.</td>
                </tr>';
        }

        echo '</tbody></table></div>';
    } else {
        echo "<p>No course ID provided.</p>";
    }

    $conn->close();
    ?>

</body>
</html>
