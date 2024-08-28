<?php
// Include database connection
include('db_connect.php');

// Check if course_id is set
if (isset($_POST['course_id'])) {
    $course_id = $conn->real_escape_string($_POST['course_id']);

    // Fetch classes associated with the course
    $sql = "SELECT * FROM class WHERE course_id = '$course_id' ORDER BY class_name ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="course-card">';
            echo '<div class="course-card-body">';
            echo '<div class="course-card-title">' . htmlspecialchars($row['class_name']) . '</div>';
            echo '<div class="course-card-text"><br>Course Subject: <br> ' . htmlspecialchars($row['subject']) . '</div>';
            echo '<div class="class-actions">';
            echo '<button class="btn btn-primary btn-sm view_course_details" data-id="'. $row['course_id'].' "type="button">View Details</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-info">No classes found for this course.</div>';
    }

    // Close the connection
    $conn->close();
} else {
    echo '<div class="alert alert-danger">Course ID is missing.</div>';
}
?>
