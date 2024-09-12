<?php
include('db_connect.php');
include('auth.php'); // Assuming this script handles authentication and sets the student_id

header('Content-Type: application/json');

// Check if assessment_id is provided
if (isset($_GET['assessment_id'])) {
    $assessment_id = $conn->real_escape_string($_GET['assessment_id']);
    $student_id = $_SESSION['login_id'];
    
    // Fetch assessment details
    $assessment_query = $conn->query("SELECT assessment_name, topic FROM assessment WHERE assessment_id = '$assessment_id'");
    $assessment_data = $assessment_query->fetch_assoc();

    if (!$assessment_data) {
        echo json_encode(array('error' => 'Assessment not found'));
        exit();
    }

    // Fetch results specifically for this student and assessment
    $results_query = $conn->query("SELECT date_updated, score, items, remarks 
                                   FROM student_results 
                                   WHERE assessment_id = '$assessment_id' 
                                   AND student_id = '$student_id'");

    $details = array();
    while ($row = $results_query->fetch_assoc()) {
        $details[] = array(
            'date' => $row['date_updated'],
            'score' => $row['score'],
            'total_score' => $row['items'],
            'remarks' => $row['remarks'] == 1 ? 'Passed' : 'Failed'
        );
    }

    // If no results found, handle it
    if (empty($details)) {
        echo json_encode(array('error' => 'No results found for this assessment and student'));
    } else {
        // Prepare the response
        $response = array(
            'title' => $assessment_data['assessment_name'],
            'topic' => $assessment_data['topic'],
            'details' => $details
        );

        // Return JSON response
        echo json_encode($response);
    }
} else {
    // Return error if no assessment_id provided
    echo json_encode(array('error' => 'No assessment ID provided'));
}

$conn->close();
?>
