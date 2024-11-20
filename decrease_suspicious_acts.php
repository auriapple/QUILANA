<?php
include('db_connect.php');
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['student_id']) && isset($data['administer_id'])) {
    $student_id = $conn->real_escape_string($data['student_id']);
    $administer_id = $conn->real_escape_string($data['administer_id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get current suspicious act count
        $check_query = "SELECT suspicious_act FROM join_assessment 
                       WHERE student_id = ? AND administer_id = ?";
        
        $stmt = $conn->prepare($check_query);
        if (!$stmt) {
            throw new Exception("Failed to prepare check query: " . $conn->error);
        }

        $stmt->bind_param("ii", $student_id, $administer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            $current_count = (int)$row['suspicious_act'];
            
            if ($current_count > 0) {
                // Prepare update query
                $update_query = "UPDATE join_assessment 
                               SET suspicious_act = suspicious_act - 1
                               WHERE student_id = ? AND administer_id = ?";
                
                $update_stmt = $conn->prepare($update_query);
                if (!$update_stmt) {
                    throw new Exception("Failed to prepare update query: " . $conn->error);
                }

                $update_stmt->bind_param("ii", $student_id, $administer_id);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to execute update: " . $update_stmt->error);
                }

                $display_query = "UPDATE join_assessment 
                                SET if_display = FALSE 
                                WHERE student_id = ? AND administer_id = ?";
                
                $display_stmt = $conn->prepare($display_query);
                if (!$display_stmt) {
                    throw new Exception("Failed to prepare display update: " . $conn->error);
                }

                $display_stmt->bind_param("ii", $student_id, $administer_id);
                
                if (!$display_stmt->execute()) {
                    throw new Exception("Failed to update display flag: " . $display_stmt->error);
                }

                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully decreased suspicious activities',
                    'new_count' => $current_count - 1
                ]);

                $display_stmt->close();
                $update_stmt->close();
            } else {
                throw new Exception("Suspicious act count is already 0");
            }
        } else {
            throw new Exception("Failed to fetch current suspicious act count");
        }
        $stmt->close();
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
        'message' => 'Missing required parameters'
    ]);
}

$conn->close();
?>