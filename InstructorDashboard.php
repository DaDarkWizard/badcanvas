<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"/>

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<?php
	// Get the login.
	include_once "InstructorHeader.php";
	include_once "checklogin.php";
	
	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("InstructorDashboard");
	if(!$email)
   	{
		return;
   	}
	$verified = verifyProfessor($email);
	if(!$verified)
	{
		return;
	}

	// Print the header.
	echo createInstructorHeader($email, "index.html");

?>
<!-- basic dashboard for instructor -->
<div class="container-fluid">
	<div class="row">
		<div class="col-sm">
			<h2 class="text-center">Instructor Dashboard</h2>
		</div>
	</div>
	<div class="row">
		<div class="col-sm">
			<a href="StudentList.php" class="btn btn-primary btn-block" role="button">Student List</a>
		</div>
	</div>
	<div class="row">
		<div class="col-sm">
			<a href="InstructorExamList.php" class="btn btn-primary btn-block" role="button">Exam List</a>
		</div>
	</div>
</div>

