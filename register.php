<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'];
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $webmail = trim($_POST['webmail']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare response array
    $response = ['status' => 'success', 'message' => ''];

    // AJAX input checks
    if ($response['status'] === 'success') {
        if ($user_type == 2) {
            // Faculty
            $stmt = $conn->prepare("SELECT firstname, lastname, webmai, faculty_number, username FROM faculty WHERE (firstname=? AND lastname=?) OR webmail=? OR faculty_number=? OR username=?");
            $stmt->bind_param("sssss", $firstname, $lastname, $webmail, $faculty_number, $username);
        } else {
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
                    $response['message'] = "This name is already registered!";
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
                if ($user_type == 2 && isset($row['faculty_number']) && $row['faculty_number'] == $faculty_number) {
                    $response['message'] = "This faculty number is already registered!";
                    echo json_encode($response);
                    exit();
                } elseif ($user_type == 3 && isset($row['student_number']) && $row['student_number'] == $student_number) {
                    $response['message'] = "This student number is already registered!";
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
            if ($user_type == 2) {
                // Insert faculty
                $stmt = $conn->prepare("INSERT INTO faculty (firstname, lastname, webmail, username, password, user_type) VALUES (?, ?, ?, ?, ?, 2)");
                $stmt->bind_param("sssss", $firstname, $lastname, $webmail, $username, $hashed_password);
            } else {
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

<!DOCTYPE html>
<html>
<head>
    <?php include('header.php') ?>
    <title>Register | Quilana</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="container">
        <div id="left-section">
            <div class="logo">QUILANA</div>
            <div class="illustration">
                <img src="/QUILANA/image/FloatingLana.gif" alt="Floating Lana">
            </div>
        </div>
        <div id="right-section">
            <a href="login.php" class="return-button">
                <div class="return">
                    <img src="/QUILANA/image/Return.png" alt="Return Button">
                </div>
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
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required />
                            </div>
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
            const webmailInput = $('#webmail');
            const studentNumberInput = $('#student_number');
            const facultyNumberInput = $('#faculty_number');
            const passwordInput = $('#password');
            const confirmPasswordInput = $('#confirm_password');
            const signUpButton = $('#signUpButton');
            const firstnameInput = $('#firstname');
            const lastnameInput = $('#lastname');
            const usernameInput = $('#username');

            const userTypeDropdown = $('#user_type');

            function validateEmail(email, userType) {
                let regex;
                if (userType === '2') { // Faculty
                    regex = /^[a-zA-Z0-9._%+-]+@pup\.edu\.ph$/;
                } else { // Student
                    regex = /^[a-zA-Z0-9._%+-]+@iskolarngbayan\.pup\.edu\.ph$/;
                }
                return regex.test(email);
            }

            function validateStudentNumber(studentNumber) {
                const regex = /^\d{4}-\d{5}-MN-0$/;
                return regex.test(studentNumber);
            }

            function validateFacultyNumber(facultyNumber) {
                const regex = /^\d{4}-\d{5}-MN-0$/; // Update with actual faculty number format
                return regex.test(facultyNumber);
            }

            function validatePassword(password) {
                const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/;
                return regex.test(password);
            }

            function toggleSignUpButton() {
                const isValid = webmailInput.hasClass('valid') && 
                                studentNumberInput.hasClass('valid') && 
                                passwordInput.hasClass('valid') && 
                                confirmPasswordInput.hasClass('valid') &&
                                firstnameInput.val().trim() !== '' &&
                                lastnameInput.val().trim() !== '' &&
                                usernameInput.val().trim() !== '';
                signUpButton.prop('disabled', !isValid);
            }

            webmailInput.on('input', function() {
                const userType = userTypeDropdown.val(); // Get the current user type
                const isValid = validateEmail(webmailInput.val(), userType);
                webmailInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
                $('.webmail-note').remove();
                if (!isValid) {
                    if (userType == 2) {
                        webmailInput.after('<div class="validation-note webmail-note">Invalid webmail format. Must be xxxxxx@pup.edu.ph</div>');
                    } else {
                        webmailInput.after('<div class="validation-note webmail-note">Invalid webmail format. Must be xxxxxxxxx@iskolarngbayan.pup.edu.ph</div>');
                    }
                }
                toggleSignUpButton();
            });

            studentNumberInput.on('input', function() {
                const isValid = validateStudentNumber(studentNumberInput.val());
                studentNumberInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
                $('.student-number-note').remove();
                if (!isValid) {
                    studentNumberInput.after('<div class="validation-note student-number-note">Invalid student number format. Must be xxxx-xxxxx-MN-0</div>');
                }
                toggleSignUpButton();
            });

            facultyNumberInput.on('input', function() {
                const isValid = validateFacultyNumber(facultyNumberInput.val());
                facultyNumberInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
                $('.faculty-number-note').remove();
                if (!isValid) {
                    facultyNumberInput.after('<div class="validation-note faculty-number-note">Invalid faculty number format. Must be xxxx-xxxxx-MN-0</div>'); // update alert as well
                }
                toggleSignUpButton();
            });

            passwordInput.on('input', function() {
                const isValid = validatePassword(passwordInput.val());
                passwordInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
                $('.password-note').remove();
                if (!isValid) {
                    passwordInput.after('<div class="validation-note password-note">Password must be at least 8 characters long and include uppercase letters, lowercase letters, numbers, and special characters.</div>');
                }
                toggleSignUpButton();
            });

            confirmPasswordInput.on('input', function() {
                const isValid = confirmPasswordInput.val() === passwordInput.val();
                confirmPasswordInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
                $('.confirm-password-note').remove();
                if (!isValid) {
                    confirmPasswordInput.after('<div class="validation-note confirm-password-note">Passwords do not match!</div>');
                }
                toggleSignUpButton();
            });

            firstnameInput.on('input', toggleSignUpButton);
            lastnameInput.on('input', toggleSignUpButton);
            usernameInput.on('input', toggleSignUpButton);

            $('#signup-form').submit(function(e) {
                if (!webmailInput.hasClass('valid') || 
                    !studentNumberInput.hasClass('valid') || 
                    !passwordInput.hasClass('valid') || 
                    !confirmPasswordInput.hasClass('valid') || 
                    firstnameInput.val().trim() === '' || 
                    lastnameInput.val().trim() === '' || 
                    usernameInput.val().trim() === '') {
                    e.preventDefault(); // Prevent form submission
                }
            });

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

            $('#signup-form').submit(function(e) {
                e.preventDefault(); // Prevent default form submission
                $.ajax({
                    type: 'POST',
                    url: 'register.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        var data = JSON.parse(response);
                        alert(data.message);
                        if (data.status === 'success') {
                            window.location.href = 'login.php?q=1'; // Redirect on success
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>