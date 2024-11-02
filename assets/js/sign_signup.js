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

let currentIndex = 1; // Start from the first image
let autoSlideInterval;

// Function to move the slider
function moveSlider(index) {
    if (index < 1) index = images.length;
    if (index > images.length) index = 1;

    // Show the current image and update the text slider
    images.forEach((img) => img.classList.remove("show"));
    document.querySelector(`.img-${index}`).classList.add("show");

    const textSlider = document.querySelector(".text-group");
    textSlider.style.transform = `translateY(${-(index - 1) * 2.2}rem)`;

    // Update bullets' active state
    bullets.forEach((bull) => bull.classList.remove("active"));
    bullets[index - 1].classList.add("active");

    // Update the currentIndex
    currentIndex = index;
}

// Start automatic sliding
function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        moveSlider(currentIndex + 1);
    }, 10000);
}

// Stop automatic sliding
function stopAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
}

// Initial setup
startAutoSlide();

// Attach click event to bullets
bullets.forEach((bullet) => {
    bullet.addEventListener("click", function() {
        const index = parseInt(this.dataset.value);
        moveSlider(index);
        stopAutoSlide(); // Stop auto sliding on user interaction
    });
});

// Move slider initially
moveSlider(currentIndex);

let userType = '';
function toggleFormFields() {
    $('.validation-note').hide();
    userType = document.getElementById("userType").value;
    console.log('Selected user type:', userType);
    
    if (userType === '2') {
        $('#student_number_container').hide();
        $('#faculty_number_container').show();
    } else if (userType === '3') {
        $('#faculty_number_container').hide();
        $('#student_number_container').show();
    }

    document.getElementById("registrationFields").style.display = "block";
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

    $('#signup-form').submit(function(e) {
        if (!webmailInput.hasClass('valid') || 
            !studentNumberInput.hasClass('valid') || 
            !facultyNumberInput.hasClass('valid') || 
            !passwordInput.hasClass('valid') || 
            !confirmPasswordInput.hasClass('valid') || 
            firstnameInput.val().trim() === '' || 
            lastnameInput.val().trim() === '' || 
            usernameInput.val().trim() === '') {
            e.preventDefault(); // Prevent form submission
        }
    });

    $('#signup-form').submit(function(e) {
        e.preventDefault(); // Prevent default form submission
        $.ajax({
            type: 'POST',
            url: 'register.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                console.log('Raw response:', response);
                if (response.status === 'success') {
                    alert(response.message);
                    setTimeout(function() {
                        location.reload();
                    })                 
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status,error) {
                console.error("AJAX error: ", error);
                console.log("Response Text:", xhr.responseText);

            }
        });
    });

    const webmailInput = $('#webmail');
    const studentNumberInput = $('#student_number');
    const facultyNumberInput = $('#faculty_number');
    const passwordInput = $('#password');
    const confirmPasswordInput = $('#confirm_password');
    const signUpButton = $('#signUpButton');
    const firstnameInput = $('#firstname');
    const lastnameInput = $('#lastname');
    const usernameInput = $('#username');

    const userTypeDropdown = $('#userType');

    function validateEmail(email, userType) {
        let regex;
        if (userType == '2') { // Faculty
            regex = /^[a-zA-Z0-9._%+-]+@pup\.edu\.ph$/;
        } else if (userType == '3') { // Student
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
                        (studentNumberInput.hasClass('valid') ||
                        facultyNumberInput.hasClass('valid')) &&
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
        $('.webmail-note').hide();
        if (!isValid) {
            if (userType == '2') { // Faculty
                $('#facultywebmail-validation').show();
            } else if (userType == '3') { // Student
                $('#studentwebmail-validation').show();
            }
        }
        toggleSignUpButton();
    });

    studentNumberInput.on('input', function() {
        const isValid = validateStudentNumber(studentNumberInput.val());
        studentNumberInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
        $('.student-number-note').hide();
        if (!isValid) {
            $('#studentnumber-validation').show();
        }
        toggleSignUpButton();
    });

    facultyNumberInput.on('input', function() {
        const isValid = validateFacultyNumber(facultyNumberInput.val());
        facultyNumberInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
        $('.faculty-number-note').hide();
        if (!isValid) {
            $('#facultynumber-validation').show();
        }
        toggleSignUpButton();
    });

    passwordInput.on('input', function() {
        const isValid = validatePassword(passwordInput.val());
        passwordInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
        $('.password-note').hide();
        if (!isValid) {
            $('#password-validation').show();
        }
        toggleSignUpButton();
    });

    confirmPasswordInput.on('input', function() {
        const isValid = confirmPasswordInput.val() === passwordInput.val();
        confirmPasswordInput.toggleClass('valid', isValid).toggleClass('invalid', !isValid);
        $('.confirm-password-note').hide();
        if (!isValid) {
            $('#confirmpassword-validation').show();
        }
        toggleSignUpButton();
    });

    firstnameInput.on('input', toggleSignUpButton);
    lastnameInput.on('input', toggleSignUpButton);
    usernameInput.on('input', toggleSignUpButton);
});