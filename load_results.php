<?php
include('db_connect.php');

if (isset($_GET['assessment_id'])) {
    $assessment_id = $_GET['assessment_id'];

    // Fetch assessment details from the student_results table
    $result_query = $conn->query("SELECT * FROM student_results WHERE assessment_id = '$assessment_id'");

    $details = [];
    if ($result_query->num_rows > 0) {
        // Fetch each result and store it in the $details array
        while ($row = $result_query->fetch_assoc()) {
            $details[] = [
                'date' => htmlspecialchars($row['date_updated']),
                'score' => htmlspecialchars($row['score']),
                'total_score' => htmlspecialchars($row['total_score']),
                'remarks' => htmlspecialchars($row['remarks'])
            ];
        }
    } else {
        echo json_encode(['error' => 'No results found for this assessment.']);
        exit;
    }

    // Fetch assessment name and topic from the assessment table
    $assessment_query = $conn->query("SELECT assessment_name, topic FROM assessment WHERE assessment_id = '$assessment_id'");

    if ($assessment_query->num_rows > 0) {
        $assessment = $assessment_query->fetch_assoc();

        // Create response array with title, topic, and details
        $response = [
            'title' => htmlspecialchars($assessment['assessment_name']),
            'topic' => htmlspecialchars($assessment['topic']),
            'details' => $details
        ];

        // Return response as JSON
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Assessment details not found.']);
    }
} else {
    echo json_encode(['error' => 'Assessment ID not provided.']);
}
?>