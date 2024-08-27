<!-- New Sidebar -->
<div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="bi bi-router-fill"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="#">Quilana</a>
                </div>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="home.php" class="sidebar-link">
                        <i class="bi bi-bar-chart-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="class_list.php" class="sidebar-link">
                        <i class="bi bi-person-workspace"></i>
                        <span>Classes</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-pencil-square"></i>
                        <span>Assessments</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" class="sidebar-link">
										<i class="bi bi-sign-turn-left-fill"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        <div class="main">
            <nav class="navbar navbar-expand px-4 py-3">
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-expanded="false" class="nav-icon pe-md-0">
                                <img src="image/account.png" class="avatar img-fluid" alt="">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end rounded">
                                <a href="#" class="dropdown-item">
                                    <i class="bi bi-person-circle"></i>
                                    <span>Profile</span>
                                </a>
                                <a href="#" class="dropdown-item">
																		<i class="bi bi-sign-turn-left-fill"></i>
                                    <span>Logout</span>
                                </a>       
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="content px-3 py-4">
                <div class="container-fluid">
                    <div class="mb-3">
                    </div>
                </div>
            </main>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-body-secondary">
                        <div class="col-6 text-start ">
                            <a class="text-body-secondary" href=" #">
                                <strong>Quilana</strong>
                            </a>
                        </div>
                        <div class="col-6 text-end text-body-secondary d-none d-md-block">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a class="text-body-secondary" href="#">Contact</a>
                                </li>
                                <li class="list-inline-item">
                                    <a class="text-body-secondary" href="#">About Us</a>
                                </li>
                                <li class="list-inline-item">
                                    <a class="text-body-secondary" href="#">Terms & Conditions</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
	const hamBurger = document.querySelector(".toggle-btn");
	hamBurger.addEventListener("click", function () 
	{
  document.querySelector("#sidebar").classList.toggle("expand");
	});
</script>


<!-- 
<nav class = "navbar navbar-header navbar-light bg-primary">
			<div class = "container-fluid">
				<div class = "navbar-header">
					<p class = "navbar-text pull-right"><h3 style="color:white">Quilana</h3></p>
				</div>
				<div class = "nav navbar-nav navbar-right">
					<a href="logout.php" style="color:white"><?php echo $firstname ?> <i class="fa fa-power-off"></i></a>
				</div>
			</div>
		</nav>
		<style>
            .bg-primary{
                background-color: #1E1A43!important;
            }
        </style>
		<div id="sidebar" class="bg-light">
			<div id="sidebar-field">
				<a href="home.php" class="sidebar-item text-dark">
						<div class="sidebar-icon"><i class="fa fa-home"> </i></div>  Dashboard
				</a>
			</div>
		<?php if($_SESSION['login_user_type'] != 3): ?>
			<div id="sidebar-field">
        		<a href="class_list.php" class="sidebar-item text-dark">
            			<div class="sidebar-icon"><i class="fa fa-list-alt"></i></div>  Classes
       			 </a>
   			 </div>
			<div id="sidebar-field">
				<a href="quiz.php" class="sidebar-item text-dark">
						<div class="sidebar-icon"><i class="fa fa-list"> </i></div>  Assessments
				</a>
			</div>

			<?php else: ?>
			<div id="sidebar-field">
				<a href="enroll.php" class="sidebar-item text-dark">
						<div class="sidebar-icon"><i class="fa fa-sign-in"></i></div> Classes
				</a>
			</div>
			<div id="sidebar-field">
				<a href="student_quiz_list.php" class="sidebar-item text-dark">
						<div class="sidebar-icon"><i class="fa fa-list"> </i></div> Results
				</a>
			</div>

		<?php endif; ?>

		</div> -->
		<script>
			$(document).ready(function(){
				var loc = window.location.href;
				loc.split('{/}')
				$('#sidebar a').each(function(){
				// console.log(loc.substr(loc.lastIndexOf("/") + 1),$(this).attr('href'))
					if($(this).attr('href') == loc.substr(loc.lastIndexOf("/") + 1)){
						$(this).addClass('active')
					}
				})
			})
			
		</script>