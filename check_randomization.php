<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['assessment_id'])) {
        $assessment_id = $_GET['assessment_id'];

        $get_randomization_query = $conn->prepare("SELECT randomize_questions FROM assessment WHERE assessment_id = ?");
        $get_randomization_query->bind_param("i", $assessment_id);
        $get_randomization_query->execute();
        $get_randomization_query->bind_result($randomize_status);
        $get_randomization_query->fetch();
        $get_randomization_query->close();

        if ($randomize_status !== null) {
            echo json_encode(['status' => 'success', 'checked' => $randomize_status]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Assessment not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameter']);
    }

    $conn->close();
}
?>