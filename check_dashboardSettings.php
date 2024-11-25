<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the ID from the AJAX request
    $userId = $_POST['id'];
    $userType = $_POST['type'];

    // Check if a record exists
    $sql = "SELECT * FROM dashboard_settings WHERE user_id = ? AND user_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $userType);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Encode the data to JSON
        echo json_encode([
            'status' => 'success',
            'summary' => $row['summary'],
            'recent' => $row['recent'],
            'request' => $row['request'],
            'report' => $row['report'],
            'calendar' => $row['calendar'],
            'upcoming' => $row['upcoming']
        ]);
    } else {
        // Insert new record
        $insertSql = "INSERT INTO dashboard_settings (user_id, user_type) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $userId, $userType);
        
        if ($insertStmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'summary' => '1',
                'recent' => '1',
                'request' => '1',
                'report' => '0',
                'calendar' => '1',
                'upcoming' => '1'
            ]);
        } else {
            echo "Error: " . $conn->error;
        }

        $insertStmt->close();
    }

    $stmt->close();
    $conn->close();
}
?>