<nav class = "navbar navbar-header navbar-light bg-primary">
	<div class = "container-fluid">
		<div class = "navbar-header">
			<p class = "navbar-text pull-right"><h3 style="color:white">QUILANA</h3></p>
		</div>
		<div class = "nav navbar-nav navbar-right">
			<a href="logout.php" style="color:white"><?php echo $firstname ?> <i class="fa fa-power-off"></i></a>
		</div>
	</div>
</nav>

<style>
    .bg-primary{
        background-image: linear-gradient(to right, #1E1A43, #4A4CA6);
    }
</style>

<div id="sidebar" class="bg-light">
	<?php if($_SESSION['login_user_type'] != 3): ?>
		<div id="sidebar-field">
			<a href="home.php" class="sidebar-item text-dark">
				<div class="sidebar-icon"><i class="fa fa-home"> </i></div>  Dashboard
			</a>
		</div>
		<div id="sidebar-field">
        	<a href="class_list.php" class="sidebar-item text-dark">
            	<div class="sidebar-icon"><i class="fa fa-list-alt"></i></div>  Classes
       		</a>
   		</div>
		<div id="sidebar-field">
			<a href="assessment.php" class="sidebar-item text-dark">
				<div class="sidebar-icon"><i class="fa fa-list"> </i></div>  Assessments
			</a>
		</div>

	<?php else: ?>
		<div id="sidebar-field">
			<!--student_dashboard.php-->
			<a href="home.php" class="sidebar-item text-dark">
				<div class="sidebar-icon"><i class="fa fa-home"> </i></div>  Dashboard
			</a>
		</div>
		<div id="sidebar-field">
			<a href="enroll.php" class="sidebar-item text-dark">
				<div class="sidebar-icon"><i class="fa fa-book"></i></div> Classes
			</a>
		</div>
		<div id="sidebar-field">
			<a href="results.php" class="sidebar-item text-dark">
				<div class="sidebar-icon"><i class="fa fa-graduation-cap"> </i></div> Results
			</a>
		</div>
	<?php endif; ?>
</div>
		
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