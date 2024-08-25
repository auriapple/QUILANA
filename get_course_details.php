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

    if (isset($_GET['course_id'])) {  // Use course_id instead of class_id
        $course_id = $_GET['course_id'];

        // Fetch the course details
        $qry_course = $conn->query("SELECT course_name FROM course WHERE course_id = '$course_id'");
        if ($qry_course->num_rows > 0) {
            $course = $qry_course->fetch_assoc();
            echo "<p><strong>{$course['course_name']}</strong></p>";
        } else {
            echo "<p><br><strong>Course not found.</strong></p>";
        }

        // Fetch the class details associated with the course
        $qry_class = $conn->query("SELECT * FROM class WHERE course_id = '$course_id'");

        // Display the table
        echo '<div class="course-details-table">
                <table border="1">
                    <tr>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Class Name</th>
                        <th>Course Subject</th>
                        <th>Action</th>
                    </tr>';

        if ($qry_class->num_rows > 0) {
            while ($class = $qry_class->fetch_assoc()) {
                echo '<tr>
                        <td>' . $class['year'] . '</td>
                        <td>' . $class['section'] . '</td>
                        <td>' . $class['class_name'] . '</td>
                        <td>' . $class['subject'] . '</td>
                        <td><!-- Action buttons here --></td>
                    </tr>';
            }
        } else {
            // Show an empty row if no class data is available
            echo '<tr>
                    <td colspan="5" style="text-align: center;">No classes found.</td>
                </tr>';
        }

        echo '</table></div>';
    } else {
        echo "<p>No course ID provided.</p>";
    }
    ?>
</body>
</html>
