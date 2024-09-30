<?php
include('db_connect.php');
include('auth.php');

header('Content-Type: application/json');

// Function to fetch assessment details
function getAssessmentDetails($conn, $assessment_id) {
    $stmt = $conn->prepare("SELECT assessment_name, topic, assessment_mode
                            FROM assessment
                            WHERE assessment_id = ?");
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to fetch student results
function getStudentResults($conn, $assessment_id, $student_id) {
    $stmt = $conn->prepare("SELECT date_updated, score, total_score, remarks, rank
                            FROM student_results 
                            WHERE assessment_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $assessment_id, $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

if (isset($_GET['assessment_id']) && filter_var($_GET['assessment_id'], FILTER_VALIDATE_INT)) {
    $assessment_id = $_GET['assessment_id'];
    $student_id = $_SESSION['login_id'];

    // Fetch assessment details
    $assessment_data = getAssessmentDetails($conn, $assessment_id);
    if (!$assessment_data) {
        echo json_encode(['error' => 'Assessment not found']);
        exit();
    }

    // Fetch student results
    $results_query = getStudentResults($conn, $assessment_id, $student_id);
    $details = [];
    while ($row = $results_query->fetch_assoc()) {
        $detail = [
            'date' => $row['date_updated'],
            'score' => $row['score'],
            'total_score' => $row['total_score'],
            'remarks' => $row['remarks']
        ];

        // Include rank only if assessment_mode is not 1
        if ($assessment_data['assessment_mode'] != 1) {
            $detail['rank'] = $row['rank'];
        }
        $details[] = $detail;
    }

    // Handle empty results
    if (empty($details)) {
        echo json_encode(['error' => 'No results found for this assessment and student']);
    } else {
        $mode = '';
        if ($assessment_data['assessment_mode'] == 2) {
            $mode = '(Quiz Bee Mode)';
        } elseif ($assessment_data['assessment_mode'] == 3) {
            $mode = '(Speed Mode)';
        }

        // Prepare the response
        $response = [
            'title' => $assessment_data['assessment_name'],
            'mode' => $mode,
            'topic' => $assessment_data['topic'],
            'assessment_mode' => $assessment_data['assessment_mode'],
            'details' => $details
        ];
        echo json_encode($response);
    }
} else {
    echo json_encode(['error' => 'Invalid or missing assessment ID']);
}
$conn->close();
?>