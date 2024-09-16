<?php
include('db_connect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve POST data
    $reviewer_type = $conn->real_escape_string($_POST['reviewer_type']);
    $reviewer_name = $conn->real_escape_string($_POST['reviewer_name']);
    $topic = $conn->real_escape_string($_POST['topic']);
    $student_id = $_SESSION['login_id'];

    // Function to generate a 6-character code with letters, numbers, and symbols
    function generateCode($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
        $charactersLength = strlen($characters);
        $randomCode = '';
        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomCode;
    }

    // Generate the unique 6-character reviewer code
    $reviewer_code = generateCode(6);

    // Check reviewer type and insert accordingly
    if ($reviewer_type == 'test') {
        // Insert into rw_reviewer for test reviewer
        $sql = "INSERT INTO rw_reviewer (student_id, reviewer_code, reviewer_name, topic, reviewer_type) 
                VALUES ('$student_id', '$reviewer_code', '$reviewer_name', '$topic', 1)";
    } elseif ($reviewer_type == 'flashcard') {
        // Insert into rw_reviewer for flashcard reviewer
        $sql = "INSERT INTO rw_reviewer (student_id, reviewer_code, reviewer_name, topic, reviewer_type) 
                VALUES ('$student_id', '$reviewer_code', '$reviewer_name', '$topic', 2)";
    }

    // Execute query and check for errors
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit();
}
?>
