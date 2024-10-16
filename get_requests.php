<?php
session_start(); // Ensure session is started
include 'db_connect.php'; // Include your database connection file

$qry = $conn->query("
    SELECT s.student_id, CONCAT(s.lastname, ', ', s.firstname) AS student_name, c.class_id, c.faculty_id, c.class_name, c.subject, se.status
    FROM student s
    JOIN student_enrollment se ON s.student_id = se.student_id
    JOIN class c ON se.class_id = c.class_id
    WHERE c.faculty_id = '".$_SESSION['login_id']."' AND se.status = '0'
    ORDER BY c.class_name, student_name
");

$current_class = '';

if ($qry->num_rows > 0) {
    while ($row = $qry->fetch_assoc()) {
        $student_id = htmlspecialchars($row['student_id']);
        $student_name = htmlspecialchars($row['student_name']);
        $class_id = htmlspecialchars($row['class_id']);
        $class_name = htmlspecialchars($row['class_name']);
        $subject = htmlspecialchars($row['subject']);
        $status = htmlspecialchars($row['status']);
        
        if ($class_name !== $current_class) {
            if ($current_class !== '') {
                echo "</div>"; // End of previous class section
            }
            $current_class = $class_name;
            echo '<div class="class-header">';
            echo '<span>' . $class_name . ' ( ' . $subject . ' )</span>';
            echo '<div class="line"></div>';
            echo '</div><div class="student-list">';
        }

        // Student Items 
        echo '<div class="student-item">';
        echo '<label>' . $student_name . '</label>';
        echo '<div class="btns">';
        echo '<button class="btn btn-primary btn-sm accept-btn accept" data-class-id="' . $class_id . '" data-student-id="' . $student_id . '" data-status="1" type="button">
            <span class="material-symbols-outlined btn-icon">check</span><span class="label">Accept</span></button>';
        echo '<button class="btn btn-primary btn-sm reject-btn reject" data-class-id="' . $class_id . '" data-student-id="' . $student_id . '" data-status="2" type="button">
            <span class="material-symbols-outlined btn-icon">close</span><span class="label">Reject</span></button>';
        echo '</div></div>';
    }

    if ($current_class !== '') {
        echo '</div>'; // Close the last student-list div
    }
} else {
    echo '<div class="no-records"> No pending requests. </div>';
}

?>
