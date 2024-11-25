<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['user_id'])) {
        $userId = intval($_POST['user_id']);
        $userType = intval($_POST['user_type']);
        $summary = intval($_POST['summary']);
        $recent = isset($_POST['recent']) ? intval($_POST['recent']) : 0;
        $request = isset($_POST['request']) ? intval($_POST['request']) : 0;
        $report = intval($_POST['report']);
        $calendar = intval($_POST['calendar']);
        $upcoming = intval($_POST['upcoming']);

        $qry = $conn->prepare("UPDATE dashboard_settings SET summary = ?, recent = ?, request = ?, report = ?, calendar = ?, upcoming = ? WHERE user_id = ? AND user_type = ?");
        if (!$qry) {
            echo json_encode(['status' => 'Error', 'message' => 'Failed to prepare query: ' . $conn->error]);
            exit;
        }
        $qry->bind_param("iiiiiiii", $summary, $recent, $request, $report, $calendar, $upcoming, $userId, $userType);

        if ($qry->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'Error', 'message' => 'Failed to update settings']);
        }

        $qry->close();
    } else {
        echo json_encode(['status' => 'Error', 'message' => 'Missing Parameters']);
    }
    $conn->close();
} else {
    echo json_encode(['status' => 'Error', 'message' => 'it did not work']);
}
?>