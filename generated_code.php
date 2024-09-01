<?php
include('db_connect.php');

// Check if class_id is set
if (isset($_POST['class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']);

    // Generate a random code of 6 alphanumeric characters
    function generateCode($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    // Check if the class already has a code
    $check_sql = "SELECT class_name, subject, code FROM class WHERE class_id = '$class_id'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();

        if (!empty($row['code'])) {
            // Code already exists, return it
            echo json_encode([
                'success' => true,
                'class_name' => $row['class_name'],
                'subject' => $row['subject'],
                'code' => $row['code']
            ]);
        } else {
            // Generate a new code
            $new_code = generateCode();

            // Check for code uniqueness
            while ($conn->query("SELECT * FROM class WHERE code = '$new_code'")->num_rows > 0) {
                $new_code = generateCode();
            }

            // Update the database with the new code
            $update_sql = "UPDATE class SET code = '$new_code' WHERE class_id = '$class_id'";
            if ($conn->query($update_sql) === TRUE) {
                echo json_encode([
                    'success' => true,
                    'class_name' => $row['class_name'],
                    'subject' => $row['subject'],
                    'code' => $new_code
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save the code.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Class not found.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Class ID is missing.']);
}

$conn->close();
?>
