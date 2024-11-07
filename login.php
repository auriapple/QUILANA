<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign in & Sign up Form</title>
    <link rel="stylesheet" href="assets/css/login_signup.css">
</head>
<body>
    <main>
        <div class="box">
            <div class="inner-box">
                <div class="forms-wrap">
                    <!-- Sign In Form -->
                    <form id="signin-form" autocomplete="off" class="sign-in-form">
                        <div class="logo">
                            <img src="image/Lana.png" alt="quilana" />
                            <h4>Quilana</h4>
                        </div>

                        <div class="heading">
                            <h2>Welcome Back</h2>
                            <h6>Not registered yet?</h6>
                            <a href="#" class="toggle">Sign up</a>
                        </div>

                        <div class="actual-form">
                            <!-- Sign As Selection -->
                            <div class="input-wrap">
                                <select id="user_type" class="input-field" name="user_type" required>
                                    <option value="" disabled selected>Sign in as</option>
                                    <option value="2">Faculty</option>
                                    <option value="3">Student</option>
                                </select>
                            </div>

                            <!-- Username Input -->
                            <div class="input-wrap">
                                <input type="text" name="username" class="input-field" autocomplete="off" required />
                                <label for="username">Username</label>
                            </div>

                            <!-- Password Input -->
                            <div class="input-wrap">
                                <input type="password" name="password" class="input-field" autocomplete="off" required />
                                <label>Password</label>
                            </div>

                            <input type="submit" value="Sign In" class="sign-btn" />
                            <p class="text">
                                Forgotten your password or login details?
                                <a href="#">Get help</a> signing in
                            </p>
                        </div>
                    </form>

                    <!-- Sign Up Form -->
                    <form id="signup-form" autocomplete="off" class="sign-up-form">
                        <div class="logo">
                            <img src="image/Lana.png" alt="quilana" />
                            <h4>Quilana</h4>
                        </div>

                        <div class="heading">
                            <h2>Get Started</h2>
                            <h6>Already have an account?</h6>
                            <a href="#" class="toggle">Sign in</a>
                        </div>

                        <div class="actual-form">
                            <div class="input-wrap">
                                <select id="userType" name="user_type" class="input-field" onchange="toggleFormFields()" required>
                                    <option value="" disabled selected>Sign up as</option>
                                    <option value="3">Student</option>
                                    <option value="2">Faculty</option>
                                </select>
                            </div>

                            <!-- Student/Faculty Form Fields -->
                            <div id="registrationFields" style="display: none;">
                                <div class="input-wrap">
                                    <input type="text" id="firstname" name="first_name" class="input-field" required />
                                    <label>First Name</label>
                                </div>

                                <div class="input-wrap">
                                    <input type="text" id="lastname" name="last_name" class="input-field" required />
                                    <label>Last Name</label>
                                </div>

                                <div class="input-wrap">
                                    <input type="email" id="webmail" name="webmail" class="input-field" required />
                                    <label>Webmail</label>
                                </div>
                                <div class="validation-note webmail-note" id="facultywebmail-validation" style="display: none;">Faculty webmail must be xxxxxx@pup.edu.ph</div>
                                <div class="validation-note webmail-note" id="studentwebmail-validation" style="display: none;">Student webmail must be xxxxxxxxx@iskolarngbayan.pup.edu.ph</div>

                                <div class="input-wrap" id="faculty_number_container" style="display:none;">
                                    <input type="text" id="faculty_number" name="faculty_number" class="input-field" />
                                    <label>Faculty Number</label>
                                </div>
                                <div class="validation-note faculty-number-note" id="facultynumber-validation" style="display: none;">Faculty number must be xxxx-xxxxx-MN-0</div>
                                
                                <div class="input-wrap" id="student_number_container" style="display:none;">
                                    <input type="text" id="student_number" name="student_number" class="input-field" />
                                    <label>Student Number</label>
                                </div>
                                <div class="validation-note student-number-note" id="studentnumber-validation" style="display: none;">Student number must be xxxx-xxxxx-MN-0</div>

                                <div class="input-wrap">
                                    <input type="text" id="username" name="username" class="input-field" required />
                                    <label>Username</label>
                                </div>

                                <div class="input-wrap">
                                    <input type="password" id="password" name="password" class="input-field" required />
                                    <label>Password</label>
                                </div>
                                <div class="validation-note password-note" id="password-validation" style="display: none;">Password must be at least 8 characters long and include uppercase, lowercase letters, numbers, and special characters.</div>

                                <div class="input-wrap">
                                    <input type="password" id="confirm_password" name="confirm_password" class="input-field" required />
                                    <label for="password">Confirm Password</label>
                                </div>
                                <div class="validation-note confirm-password-note" id="confirmpassword-validation" style="display: none;">Passwords do not match!</div>
                            </div>

                            <button type="submit" id="signUpButton" value="Sign Up" class="sign-btn">Sign Up</button>

                            <p class="text">
                                By signing up, I agree to the
                                <a href="#">Terms of Services</a> and
                                <a href="#">Privacy Policy</a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Carousel Section -->
                <div class="carousel">
                    <div class="images-wrapper">
                        <img src="image/FloatingLana.gif" class="image img-1 show" alt="Lana" />
                        <img src="image/User.png" class="image img-2" alt="" />
                        <img src="image/image3.png" class="image img-3" alt="" />
                    </div>

                    <div class="text-slider">
                        <div class="text-wrap">
                            <div class="text-group">
                                <h2>Create your own courses</h2>
                                <h2>Customize as you like</h2>
                                <h2>Invite students to your class</h2>
                            </div>
                        </div>

                        <div class="bullets">
                            <span class="active" data-value="1"></span>
                            <span data-value="2"></span>
                            <span data-value="3"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/jquery-3.6.0.min.js"> </script>
    <script src="assets/js/sign_signup.js"></script>
</body>
</html>