<?php
include('db_connect.php');

if (isset($_POST['assessment_id'])) {
    $assessment_id = $_POST['assessment_id'];

    // Fetch assessment details and results
    $result_query = $conn->query("SELECT * FROM assessment_results WHERE assessment_id = '$assessment_id'");

    if ($result_query->num_rows > 0) {
        while ($row = $result_query->fetch_assoc()) {
            // Example: output result details
            echo 'Score: ' . htmlspecialchars($row['score']) . '<br>';
            echo 'Remarks: ' . htmlspecialchars($row['remarks']) . '<br>';
        }
    } else {
        echo 'No results found for this assessment.';
    }
} else {
    echo 'Assessment ID not provided.';
}
?>
