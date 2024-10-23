<?php
header('Content-Type: application/json'); // Set content type to JSON

include('db_connect.php'); // Include your database connection file

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['administer_id'])) {
    // Escape and sanitize the input
    $administer_id = $data['administer_id'];

    // SQL query to fetch data
    $qry1 = $conn->query("
        SELECT COUNT(*) as total_students FROM join_assessment
        WHERE administer_id = '$administer_id'
    ");

    $qry2 = $conn->query("
        SELECT COUNT(*) as total_finished FROM join_assessment
        WHERE status = 2 AND administer_id = '$administer_id'
    ");

    // Prepare and execute the statement
    if ($qry1 && $qry2) {
        // Fetch the results as associative arrays
        $row1 = $qry1->fetch_assoc();
        $row2 = $qry2->fetch_assoc();

        $total_students = $row1['total_students'];  // Total students who joined
        $total_finished = $row2['total_finished'];  // Total students who finished

        // Check if the total number of students is equal to the total number of finished students
        if ($total_students == $total_finished && $total_students != 0) {
            echo json_encode(array("status" => "success", "message" => "All students have finished."));
        } else if ($total_students == $total_finished && $total_students == 0) {
            echo json_encode(array("status" => "incomplete", "message" => "No students have joined."));
        } else {
            echo json_encode(array(
                "status" => "incomplete",
                "message" => "Not all students have finished.",
                "total_students" => $total_students,
                "total_finished" => $total_finished
            ));
        }
    } else {
        echo json_encode(array("error" => "Failed to execute the SQL queries."));
    }
} else {
    echo json_encode(array("error" => "Administer ID is missing."));
}

$conn->close();
?>