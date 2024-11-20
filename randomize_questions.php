<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['assessment_id']) && isset($_POST['checked'])) {
        $assessment_id = intval($_POST['assessment_id']);
        $checked = intval($_POST['checked']);

        $update_randomization_query = $conn->prepare("UPDATE assessment SET randomize_questions = ? WHERE assessment_id = ?");
        $update_randomization_query->bind_param("ii", $checked, $assessment_id);

        if ($update_randomization_query->execute()) {
            if ($checked === 1) {
                echo json_encode(['status' => 'randomize']);
            } elseif($checked === 0) {
                echo json_encode(['status' => 'undo randomize']);
            }
        } else {
            echo json_encode(['status' => 'Error', 'message' => 'Failed to randomize questions']);
        }

        $update_randomization_query->close();
    } else {
        echo json_encode(['status' => 'Error', 'message' => 'Missing Parameters']);
    }
    $conn->close();
}