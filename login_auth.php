<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password && $user_type) {
        if ($user_type == 2) {
            // Faculty
            $stmt = $conn->prepare("SELECT * FROM faculty WHERE username = ?");
        } elseif ($user_type == 3) {
            // Student
            $stmt = $conn->prepare("SELECT * FROM student WHERE username = ?");
        } else {
            echo 2; // Invalid user type
            exit();
        }

        if (!$stmt) {
            die("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        
        if (!$stmt->execute()) {
            die("Query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables based on user type
                $_SESSION['login_user_type'] = $user_type;
                $_SESSION['login_id'] = $user['faculty_id'] ?? $user['student_id'];
                $_SESSION['login_username'] = $username;

                // Store other user data as needed
                foreach ($user as $key => $value) {
                    if ($key != 'password') {
                        $_SESSION['login_' . $key] = $value;
                    }
                }
                echo 1;
            } else {
                echo 2; // Incorrect password
            }
        } else {
            echo 2; // No such user found
        }

        $stmt->close();
    } else {
        echo 2; // Missing username, password, or user type
    }
    $conn->close();
} else {
    echo 2; // Invalid request method
}
?>
