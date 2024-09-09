<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['submit'])) {
    include('db_connect.php');

    if (!$conn) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_type = $_POST['user_type'];
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $webmail = trim($_POST['webmail']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
        echo "<script>window.location.href = 'register.php';</script>";
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($user_type == 2) {
        // Faculty
        $stmt = $conn->prepare("SELECT webmail FROM faculty WHERE webmail=?");
        if (!$stmt) {
            die("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("s", $webmail);
    } else {
        // Student
        $student_number = trim($_POST['student_number']);
        $stmt = $conn->prepare("SELECT student_number FROM student WHERE student_number=?");
        if (!$stmt) {
            die("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("s", $student_number);
    }

    if (!$stmt->execute()) {
        die("Select query failed: " . $stmt->error);
    }
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('This " . ($user_type == 2 ? "webmail" : "student number") . " is already registered!');</script>";
        echo "<script>window.location.href = 'register.php';</script>";
    } else {
        if ($user_type == 2) {
            // Insert faculty
            $stmt = $conn->prepare("INSERT INTO faculty (firstname, lastname, webmail, username, password, user_type) VALUES (?, ?, ?, ?, ?, 2)");
            if (!$stmt) {
                die("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("sssss", $firstname, $lastname, $webmail, $username, $hashed_password);
        } else {
            // Insert student
            $stmt = $conn->prepare("INSERT INTO student (firstname, lastname, webmail, student_number, username, password, user_type) VALUES (?, ?, ?, ?, ?, ?, 3)");
            if (!$stmt) {
                die("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("ssssss", $firstname, $lastname, $webmail, $student_number, $username, $hashed_password);
        }

        if ($stmt->execute()) {
            echo "<script>alert('You successfully registered as " . ($user_type == 2 ? "faculty" : "student") . "!');</script>";
            echo "<script>window.location.href = 'login.php?q=1';</script>";
        } else {
            die("Insert query failed: " . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php include('header.php') ?>
    <title>Register | Quilana</title>
</head>
<body>
    <div id="container">
        <div id="left-section">
            <div class="logo">QUILANA</div>
            <div class="illustration"></div>
        </div>
        <div id="right-section">
            <a href="login.php" class="return-button">
                <div class="return"></div>
            </a>
            <div class="form">
                <h2>SIGN UP</h2>
                <form method="post" action="register.php" id="signup-form">
                    <div class="form-group" id="user_type_container">
                        <label for="user_type">REGISTER AS:</label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="" disabled selected>Select User Type</option>
                            <option value="2">Faculty</option>
                            <option value="3">Student</option>
                        </select>
                    </div>

                    <div id="registration-fields" style="display: none;">
                        <div class="form-group row">
                            <div class="col-half">
                                <label for="firstname">FIRST NAME</label>
                                <input type="text" name="firstname" id="firstname" class="form-control" required />
                            </div>
                            <div class="col-half">
                                <label for="lastname">LAST NAME</label>
                                <input type="text" name="lastname" id="lastname" class="form-control" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="webmail">WEBMAIL</label>
                            <input type="email" name="webmail" id="webmail" class="form-control" required />
                        </div>
                        <div class="form-group" id="student_number_container" style="display:none;">
                            <label for="student_number">STUDENT NUMBER</label>
                            <input type="text" name="student_number" id="student_number" class="form-control" />
                        </div>
                        <div class="form-group" id="faculty_number_container" style="display:none;">
                            <label for="faculty_number">FACULTY NUMBER</label>
                            <input type="text" name="faculty_number" id="faculty_number" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="username">USERNAME</label>
                            <input type="text" name="username" id="username" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label for="password">PASSWORD</label>
                            <input type="password" name="password" id="password" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">CONFIRM PASSWORD</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required />
                        </div>
                        <button type="submit" id="signUpButton" class="main-button" name="submit">Register</button>
                    </div>

                    <div class="form-group text-center">
                        <span class="text-muted">Already have an account? </span> <a href="login.php">Sign In Here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#user_type').change(function() {
                var userType = $(this).val();
                
                if (userType === '2') { // Faculty
                    $('#student_number_container').hide();
                    $('#faculty_number_container').show();
                } else if (userType === '3') { // Student
                    $('#faculty_number_container').hide();
                    $('#student_number_container').show();
                } else {
                    $('#faculty_number_container').hide();
                    $('#student_number_container').hide();
                }

                if (userType !== '') {
                    $('#registration-fields').slideDown();
                    $('#user_type_container').slideUp();
                }
            });
        });
    </script>
</body>
</html>