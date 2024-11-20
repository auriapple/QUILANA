<?php
include 'db_connect.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['administer_id'])) {
    $administer_id = $conn->real_escape_string($data['administer_id']);
    $method = $conn->real_escape_string($data['method']);
    
    if (isset($_SESSION['login_id'])) {
        $student_id = $conn->real_escape_string($_SESSION['login_id']);

        // Start transaction
        $conn->begin_transaction();

        try {
            // current suspicious_act count
            $check_stmt = $conn->prepare("
                SELECT suspicious_act 
                FROM join_assessment 
                WHERE administer_id = ? AND student_id = ?
            ");
            
            if (!$check_stmt) {
                throw new Exception("Failed to prepare check query: " . $conn->error);
            }

            $check_stmt->bind_param("ii", $administer_id, $student_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $current_count = 0;
            
            if ($row = $result->fetch_assoc()) {
                $current_count = (int)$row['suspicious_act'];
            }
            $check_stmt->close();

            // Increment the count
            $new_count = $current_count + 1;

            // Update with incremented count
            $update_stmt = $conn->prepare("
                UPDATE join_assessment
                SET suspicious_act = ?, 
                    method = ?, 
                    if_display = true,
                    time_updated = CURRENT_TIMESTAMP
                WHERE administer_id = ? AND student_id = ?
            ");
            
            if (!$update_stmt) {
                throw new Exception("Failed to prepare update query: " . $conn->error);
            }

            $update_stmt->bind_param("isii", $new_count, $method, $administer_id, $student_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to execute update: " . $update_stmt->error);
            }

            $update_stmt->close();
            $conn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Warning triggered via ' . $method . '.',
                'new_count' => $new_count
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Student ID is missing from the session.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'administer_id is missing.'
    ]);
}

$conn->close();
?>