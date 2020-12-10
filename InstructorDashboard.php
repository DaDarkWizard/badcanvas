<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"/>

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<?php
	include_once "checklogin.php";
	
	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("InstructorDashboard");
	$verified = verifyProfessor($email);
	
	if(!$email)
	{
		return;
	}
	//if(!($_SESSION['TOKEN_DATA']["email_verified"]))
	//{
	//	$_SESSION["login_task"] = "login";
	//	$_SESSION["redirect"] = "InstructorDashboard";
	//	header("LOCATION:" . "login.php");
	//	return;
	//}

	#__DIR__
	#$_SESSION["redirect"] = "LOCATION:login.php";
	#$_SESSION["login_task"] = "LOCATION:InstructorDashboard.php";
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-1">
			<img src="BadCanvas.png" class="img-fluid d-block" alt="CANVAS_LOGO" width="75" height="75">
		</div>
  		<div class="col-sm-10" style="background-color:lightgray;">		
			<h1>Instructor Dashboard</h1>
		</div>
   		<div class="col-sm-1">
			<a href="login.php" class="btn btn-default btn-center" role="button">Logout</a>
		</div>
  	</div>
	<div class="row">
		<div class="col-sm">
			<a href="StudentList.php" class="btn btn-primary btn-block" role="button">Student List</a>
		</div>
	</div>
	<div class="row">
		<div class="col-sm">
			<a href="#" class="btn btn-primary btn-block" role="button">Exam List</a>
		</div>
	</div>
</div>

