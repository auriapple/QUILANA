<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Landing Page With Light/Dark Mode</title>
    <link rel="stylesheet" href="assets/css/landingpage.css" />
  </head>
  <body>
    <main>
      <div class="big-wrapper light">
        <img src="image/shape.png" alt="" class="shape" />

        <header>
          <div class="container">
            <div class="logo">
              <img src="image/lana.png" alt="Logo" />
              <h3>Quilana</h3>
            </div>

            <div class="links">
              <ul>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Privacy</a></li>
                <li><a href="register.php" class="btn">Sign up</a></li>
              </ul>
            </div>

            <div class="overlay"></div>

            <div class="hamburger-menu">
              <div class="bar"></div>
            </div>
          </div>
        </header>

        <div class="showcase-area">
          <div class="container">
            <div class="left">
              <div class="big-title">
                <h1>Streamline Your,</h1>
                <h1>Exam Administration with Quilana.</h1>
              </div>
              <p class="text">
                Combining the best of traditional and online exam systems for efficient and secure assessments
              </p>
              <div class="cta">
                <a href="login.php" class="btn">Get started</a>
              </div>
            </div>

            <div class="right">
              <img src="image/lana.png" alt="Person Image" class="person" />
            </div>
          </div>
        </div>

        <div class="bottom-area">
          <div class="container">
            <button class="toggle-btn">
              <i class="far fa-moon"></i>
              <i class="far fa-sun"></i>
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- JavaScript Files -->

    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <script>
                // Select The Elements
        var toggle_btn;
        var big_wrapper;
        var hamburger_menu;

        function declare() {
        toggle_btn = document.querySelector(".toggle-btn");
        big_wrapper = document.querySelector(".big-wrapper");
        hamburger_menu = document.querySelector(".hamburger-menu");
        }

        const main = document.querySelector("main");

        declare();

        let dark = false;

        function toggleAnimation() {
        // Clone the wrapper
        dark = !dark;
        let clone = big_wrapper.cloneNode(true);
        if (dark) {
            clone.classList.remove("light");
            clone.classList.add("dark");
        } else {
            clone.classList.remove("dark");
            clone.classList.add("light");
        }
        clone.classList.add("copy");
        main.appendChild(clone);

        document.body.classList.add("stop-scrolling");

        clone.addEventListener("animationend", () => {
            document.body.classList.remove("stop-scrolling");
            big_wrapper.remove();
            clone.classList.remove("copy");
            // Reset Variables
            declare();
            events();
        });
        }

        function events() {
        toggle_btn.addEventListener("click", toggleAnimation);
        hamburger_menu.addEventListener("click", () => {
            big_wrapper.classList.toggle("active");
        });
        }

        events();

    </script>
  </body>
</html>
