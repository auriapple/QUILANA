<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : null;
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';

    if ($course_id && $subject) {
        $stmt = $conn->prepare("SELECT class_id, class_name FROM class WHERE course_id = ? AND subject = ?");
        $stmt->bind_param("is", $course_id, $subject);  // 'i' for integer, 's' for string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $options = '<option value="">Select Class</option>';
            while ($row = $result->fetch_assoc()) {
                $options .= "<option value='" . htmlspecialchars($row['class_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['class_name'], ENT_QUOTES, 'UTF-8') . "</option>";
            }
            echo $options;
        } else {
            echo '<option value="">No classes available</option>';
        }
        $stmt->close();
    } else {
        echo '<option value="">Invalid parameters</option>';
    }
    $conn->close();
} else {
    echo '<option value="">Invalid request method</option>';
}
