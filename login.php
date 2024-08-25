<!DOCTYPE html>
<html>
<head>
    <?php include('header.php') ?>
    <title>Login | Quilana</title>
</head>
<body>
    <div id="login-container">
        <div id="left-section">
            <div class="logo">QUILANA</div>
            <div class="illustration">
            </div>
        </div>
        <div id="right-section">
            <div class="sign-in-form">
                <h2>SIGN IN</h2>
                <form id="login-frm" method="POST" action="login_auth.php">
                    <div class="form-group">
                        <label for="user_type">Login as:</label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="">Select User Type</option>
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
                    </div>
                    <button type="submit" class="btn-sign-in">Sign in</button>
                    <div class="form-group text-center">
                        <span class="text-muted">Don't have an account? </span> <a href="register.php">Sign Up Here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#login-frm').submit(function(e){
                e.preventDefault();
                $('#login-frm button').attr('disabled', true);
                $('#login-frm button').html('Please wait...');

                $.ajax({
                    url: './login_auth.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    error: function(err) {
                        console.log(err);
                        alert('An error occurred');
                        $('#login-frm button').removeAttr('disabled');
                        $('#login-frm button').html('Sign in');
                    },
                    success: function(resp) {
                        if (resp == 1) {
                            location.replace('home.php');
                        } else {
                            alert("Incorrect username or password.");
                            $('#login-frm button').removeAttr('disabled');
                            $('#login-frm button').html('Sign in');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
