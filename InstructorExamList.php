<html>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<?php

	include_once "checklogin.php";
	//include_once "InstructorHeader.php";
	
	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("InstructorExamList");
	if(!$email)
    {
        return;
    }
	$verified = verifyProfessor($email);
	if(!$verified)
	{
		header("LOCATION:index.html");
		return;
	}

	//echo createInstructorHeader("Exams", "InstructorDashboard", $email);

?>

<div class="container">

<div class="border border-bottom-0 w-100" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">
	<h2>Exams</h2>
</div>

<div class="list-group">

<button class="list-group-item list-group-item-action text-left" style="border-radius:0">
	<div class="d-flex align-items-center">
	<img src="BadRocket.png" class="mr-3"/>
	<div class="d-inline-block">
		<div class="row no-gutters"><div class="col"><h5 style="color:black;">Exam name</h5></div></div>
		<div class="row no-gutters"><div class="col" style="font-size:.75rem;">
			Closed | Due Sep 15 at 12:30pm Sep 15 at 12:30pm | 40 pts | 40 Questions
		</div></div>
	</div>
	</div>
<button class="">
</button>
	
	
</button>

</div>

</div>

</body>
</html>