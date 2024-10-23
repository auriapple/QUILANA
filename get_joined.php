<?php
header('Content-Type: application/json'); // Set content type to JSON

include('db_connect.php'); // Include your database connection file

// Check if the POST request contains 'assessment_id' and 'class_id'
if (isset($_POST['assessment_id']) && isset($_POST['class_id'])) {
    // Escape and sanitize the input
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $class_id = $conn->real_escape_string($_POST['class_id']);

    // SQL query to fetch data
    $qry2 = "
        SELECT s.student_id, s.student_number, CONCAT(lastname, ', ', firstname) as student_name, ja.*, aa.administer_id, sr.score, sr.total_score
        FROM join_assessment ja
        LEFT JOIN student s ON ja.student_id = s.student_id
        LEFT JOIN administer_assessment aa ON ja.administer_id = aa.administer_id
        LEFT JOIN assessment a ON aa.assessment_id = a.assessment_id
        LEFT JOIN student_results sr ON s.student_id = sr.student_id
        WHERE aa.assessment_id = ? AND aa.class_id = ?
        ORDER BY ja.status, student_name
    ";

    // Prepare and execute the statement
    if ($stmt2 = $conn->prepare($qry2)) {
        $stmt2->bind_param("ii", $assessment_id, $class_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $data = array();
        while ($row = $result2->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data);

        $stmt2->close();
    } else {
        echo json_encode(array("error" => "Failed to prepare the SQL statement."));
    }
} else {
    echo json_encode(array("error" => "Assessment ID or Class ID is missing."));
}

$conn->close();
?>