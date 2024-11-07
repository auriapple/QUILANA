<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $user_type = $_POST['user_type'];
    $firstname = trim($_POST['first_name']);
    $lastname = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $webmail = trim($_POST['webmail']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare response array
    $response = ['status' => 'success', 'message' => ''];

    // AJAX input checks
    if ($response['status'] === 'success') {
        if ($user_type == '2') {
            // Faculty
            $faculty_number = trim($_POST['faculty_number']);
            $stmt = $conn->prepare("SELECT firstname, lastname, webmail, faculty_number, username FROM faculty WHERE (firstname=? AND lastname=?) OR webmail=? OR faculty_number=? OR username=?");
            $stmt->bind_param("sssss", $firstname, $lastname, $webmail, $faculty_number, $username);
        } elseif ($user_type == '3') {
            // Student
            $student_number = trim($_POST['student_number']);
            $stmt = $conn->prepare("SELECT firstname, lastname, webmail, student_number, username FROM student WHERE (firstname=? AND lastname=?) OR webmail=? OR student_number=? OR username=?");
            $stmt->bind_param("sssss", $firstname, $lastname, $webmail, $student_number, $username);
        }

        if (!$stmt->execute()) {
            die("Select query failed: " . $stmt->error);
        }
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response['status'] = 'error';

            // Check for name
            while ($row = $result->fetch_assoc()) {
                if ($row['firstname'] == $firstname && $row['lastname'] == $lastname) {
                    $response['message'] = "This name is already registered: " . $row['firstname'] . " " . $row['lastname'] . " (Webmail: " . $row['webmail'] . ", Username: " . $row['username'] . ")!";
                    echo json_encode($response);
                    exit();
                }
            }

            // Reset result pointer to the beginning
            $result->data_seek(0);

            // Check for webmail
            while ($row = $result->fetch_assoc()) {
                if ($row['webmail'] == $webmail) {
                    $response['message'] = "This webmail is already registered!";
                    echo json_encode($response);
                    exit();
                }
            }

            // Reset result pointer again
            $result->data_seek(0);

            // Check for faculty or student number
            while ($row = $result->fetch_assoc()) {
                if ($user_type == '3' && isset($row['student_number']) && $row['student_number'] == $student_number) {
                    $response['message'] = "This student number is already registered!";
                    echo json_encode($response);
                    exit();
                } elseif ($user_type == '2' && isset($row['faculty_number']) && $row['faculty_number'] == $faculty_number) {
                    $response['message'] = "This faculty number is already registered!";
                    echo json_encode($response);
                    exit();
                }
            }

            // Reset result pointer one last time
            $result->data_seek(0);

            // Check for username
            while ($row = $result->fetch_assoc()) {
                if ($row['username'] == $username) {
                    $response['message'] = "This username is already taken!";
                    echo json_encode($response);
                    exit();
                }
            }
        } else {
            // Proceed with registration
            if ($user_type == '2') {
                // Insert faculty
                $stmt = $conn->prepare("INSERT INTO faculty (firstname, lastname, webmail, faculty_number, username, password, user_type) VALUES (?, ?, ?, ?, ?, ?, 2)");
                $stmt->bind_param("ssssss", $firstname, $lastname, $webmail, $faculty_number, $username, $hashed_password);
            } elseif ($user_type == '3') {
                // Insert student
                $stmt = $conn->prepare("INSERT INTO student (firstname, lastname, webmail, student_number, username, password, user_type) VALUES (?, ?, ?, ?, ?, ?, 3)");
                $stmt->bind_param("ssssss", $firstname, $lastname, $webmail, $student_number, $username, $hashed_password);
            }

            if ($stmt->execute()) {
                $response['message'] = 'You successfully registered as ' . ($user_type == 2 ? 'faculty' : 'student') . '!';
            } else {
                $response['status'] = 'error';
                $response['message'] = "Insert query failed: " . $stmt->error;
            }
        }

        $stmt->close();
    }

    echo json_encode($response);
    exit();
}
?>