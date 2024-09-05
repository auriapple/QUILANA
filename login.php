<!DOCTYPE html>
<html>
<head>
    <?php include('header.php') ?>
    <title>Login | Quilana</title>
</head>
<body>
    <div id="container">
        <div id="left-section">
            <div class="logo">QUILANA</div>
            <div class="illustration"></div>
        </div>
        <div id="right-section">
            <a href="welcome.php" class="return-button">
                <div class="return"></div>
            </a>
            <div class="form">
                <h2>SIGN IN</h2>
                <form id="signin-form" method="POST" action="login_auth.php">
                    <div class="form-group">
                        <label for="user_type">SIGN IN AS:</label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="" disabled selected>Select User Type</option>
                            <option value="2">Faculty</option>
                            <option value="3">Student</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">USERNAME</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">PASSWORD</label>
                        <input type="password" id="password" name="password" required>
                        <a class="fp">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" id="signInButton" class="main-button">Sign in</button>

                    <div class="form-group text-center">
                        <span class="text-muted">Don't have an account? </span>
                        <a href="register.php">Sign Up Here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#signin-form').submit(function(e){
                e.preventDefault();
                $('#signin-form button').attr('disabled', true);
                $('#signin-form button').html('Please wait...');

                $.ajax({
                    url: './login_auth.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    error: function(err) {
                        console.log(err);
                        alert('An error occurred');
                        $('#signin-form button').removeAttr('disabled');
                        $('#signin-form button').html('Sign in');
                    },
                    success: function(resp) {
                        if (resp == 1) {
                            location.replace('home.php');
                        } else {
                            alert("Incorrect username or password.");
                            $('#signin-form button').removeAttr('disabled');
                            $('#signin-form button').html('Sign in');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
