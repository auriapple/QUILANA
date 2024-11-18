<?php
include('db_connect.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['administer_id']) && isset($data['student_id']) && isset($data['if_display'])) {
    try {
        $administer_id = $conn->real_escape_string($data['administer_id']);
        $student_id = $conn->real_escape_string($data['student_id']);
        $if_display = $data['if_display'] ? 1 : 0;

        $stmt = $conn->prepare("
            UPDATE join_assessment
            SET if_display = ?
            WHERE administer_id = ? AND student_id = ?
        ");

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("iii", $if_display, $administer_id, $student_id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute update: " . $stmt->error);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Display status updated successfully'
        ]);

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    $missingFields = [];
    if (!isset($data['administer_id'])) $missingFields[] = 'administer_id';
    if (!isset($data['student_id'])) $missingFields[] = 'student_id';
    if (!isset($data['if_display'])) $missingFields[] = 'if_display';
    
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missingFields)
    ]);
}

$conn->close();
?>