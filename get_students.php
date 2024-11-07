<?php
include('db_connect.php');

$class_id = $_POST['class_id'];

$qry_student = $conn->query("
    SELECT s.student_id, s.student_number, CONCAT(s.lastname, ', ', s.firstname) AS student_name, c.class_name, c.subject, se.status
    FROM student_enrollment se
    JOIN student s ON se.student_id = s.student_id
    JOIN class c ON se.class_id = c.class_id
    WHERE se.class_id = '$class_id' AND se.status != 2
    ORDER BY se.status ASC, student_name ASC
");

if (isset($qry_student) && $qry_student->num_rows > 0) {
    while ($student = $qry_student->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($student['student_number']) . '</td>
                <td>' . htmlspecialchars($student['student_name']) . '</td>
                <td class="status">' . (($student['status'] == 0) ? 'Pending' : 'Enrolled') . '</td>
                <td>';
        if ($student['status'] == 0) {
            echo '<div class="btn-container">
                    <button class="btn btn-success btn-sm accept-btn" 
                            data-class-id="' . $class_id . '" 
                            data-student-id="' . $student['student_id'] . '" 
                            data-status="1" 
                            data-student-name="' . $student['student_name'] . '"
                            data-class-sub="' . $student['class_name'] . ' (' . $student['subject'] . ')' . '"
                            type="button">Accept</button>
                    <button class="btn btn-danger btn-sm reject-btn" 
                            data-class-id="' . $class_id . '" 
                            data-student-id="' . $student['student_id'] . '" 
                            data-status="2" 
                            data-student-name="' . $student['student_name'] . '"
                            data-class-sub="' . $student['class_name'] . ' (' . $student['subject'] . ')' . '"
                            type="button">Reject</button>
                </div>';
        } else {
            echo '<div class="btn-container">
                    <button class="btn btn-primary btn-sm" 
                            onclick="showStudentScores(' . $student['student_id'] . ', \'' . $student['student_name'] . '\')" 
                            type="button">Scores</button>';
            ?>
                   <button class="btn btn-danger btn-sm" 
                        data-class-id="<?php echo $class_id; ?>" 
                        data-student-id="<?php echo $student['student_id']; ?>" 
                        data-status="3" 
                        type="button" 
                        onclick="confirmStudentRemoval('<?php echo $student['student_id']; ?>', '<?php echo $class_id; ?>', '<?php echo htmlspecialchars($student['student_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($student['class_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($student['subject'], ENT_QUOTES); ?>')">
                    Remove</button>
                    <?php 
                echo '</div>';
        }
        echo '</td></tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center">No students found.</td></tr>';
}
?>