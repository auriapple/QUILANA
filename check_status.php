<?php
include('db_connect.php');

if (!isset($_GET['administer_id'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$administer_id = $conn->real_escape_string($_GET['administer_id']);

$status_query = $conn->query("
    SELECT status 
    FROM administer_assessment 
    WHERE administer_id = '$administer_id'
");

if ($status_query && $status_query->num_rows > 0) {
    $status_data = $status_query->fetch_assoc();
    echo json_encode(['status' => $status_data['status']]);
} else {
    echo json_encode(['error' => 'No records found']);
}
?>
