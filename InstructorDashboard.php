<?php
	include_once "checklogin.php";
	
	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("InstructorDashboard");
	$verified = verifyProfessor($email);

	
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

<body>
	<form method="post" action="login.php">
		<input type="hidden" name="login_task" value="logout">
		<input type="submit" name="logout" value="Logout">
	</form>
</body>
