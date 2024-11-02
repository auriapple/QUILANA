
        const inputs = document.querySelectorAll(".input-field");
        const toggle_btn = document.querySelectorAll(".toggle");
        const main = document.querySelector("main");
        const bullets = document.querySelectorAll(".bullets span");
        const images = document.querySelectorAll(".image");

        inputs.forEach((inp) => {
        inp.addEventListener("focus", () => {
            inp.classList.add("active");
        });
        inp.addEventListener("blur", () => {
            if (inp.value != "") return;
            inp.classList.remove("active");
        });
        });

        toggle_btn.forEach((btn) => {
        btn.addEventListener("click", () => {
            main.classList.toggle("sign-up-mode");
        });
        });

        function moveSlider() {
        let index = this.dataset.value;

        let currentImage = document.querySelector(`.img-${index}`);
        images.forEach((img) => img.classList.remove("show"));
        currentImage.classList.add("show");

        const textSlider = document.querySelector(".text-group");
        textSlider.style.transform = `translateY(${-(index - 1) * 2.2}rem)`;

        bullets.forEach((bull) => bull.classList.remove("active"));
        this.classList.add("active");
        }

        bullets.forEach((bullet) => {
        bullet.addEventListener("click", moveSlider);
        });

        function toggleFormFields() {
            const userType = document.getElementById("userType").value;
            document.getElementById("studentFields").style.display = userType === "student" ? "block" : "none";
            document.getElementById("facultyFields").style.display = userType === "faculty" ? "block" : "none";
        }

        $(document).ready(function(){
            $('#signin-form').submit(function(e){
                e.preventDefault();
                $('#signin-form input[type="submit"]').attr('disabled', true).val('Please wait...');

                $.ajax({
                    url: 'login_auth.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    error: function(err) {
                        console.log(err);
                        alert('An error occurred');
                        $('#signin-form input[type="submit"]').removeAttr('disabled').val('Sign In');
                    },
                    success: function(resp) {
                        if (resp == 1) {
                            var userType = $('#user_type').val();
                            if (userType == '2') {
                                location.replace('faculty_dashboard.php');
                            } else {
                                location.replace('student_dashboard.php');
                            }
                        } else {
                            alert("Incorrect username or password.");
                            $('#signin-form input[type="submit"]').removeAttr('disabled').val('Sign In');
                        }
                    }
                });
            });
        });

        