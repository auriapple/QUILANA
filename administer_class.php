<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $subject = $_POST['subject'];  // Assuming you're sending 'subject' from the client side

    if ($course_id && $subject) {
        $stmt = $conn->prepare("SELECT class_id, class_name FROM class WHERE course_id = ? AND subject = ?");
        $stmt->bind_param("is", $course_id, $subject);  // 'i' for integer, 's' for string
        $stmt->execute();
        $result = $stmt->get_result();

        $options = '<option value="">Select Class</option>';
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['class_name'], ENT_QUOTES, 'UTF-8') . "</option>";
        }
        echo $options;
        $stmt->close();
    } else {
        echo '<option value="">No classes available</option>';
    }
    $conn->close();
}
