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
function getStudentResults($conn, $student_id, $administer_id) {
    $stmt = $conn->prepare("SELECT ss.date_taken, sr.score, sr.total_score, sr.remarks, sr.rank
                            FROM student_results sr
                            JOIN student_submission ss ON ss.submission_id = sr.submission_id
                            WHERE sr.student_id = ? AND ss.administer_id = ?");
    $stmt->bind_param("ii", $student_id, $administer_id);
    $stmt->execute();
    return $stmt->get_result();
}

if (isset($_GET['assessment_id']) && filter_var($_GET['assessment_id'], FILTER_VALIDATE_INT)) {
    $assessment_id = $_GET['assessment_id'];
    $student_id = $_SESSION['login_id'];
    $administer_id = $_GET['administer_id'];

    // Fetch assessment details
    $assessment_data = getAssessmentDetails($conn, $assessment_id);
    if (!$assessment_data) {
        echo json_encode(['error' => 'Assessment not found']);
        exit();
    }

    // Fetch student results
    $results_query = getStudentResults($conn, $student_id, $administer_id);
    $details = [];
    while ($row = $results_query->fetch_assoc()) {
        $detail = [
            'date' => $row['date_taken'],
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
        if ($assessment_data['assessment_mode'] == 1) {
            $mode = '(Normal Mode)';
        } elseif ($assessment_data['assessment_mode'] == 2) {
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