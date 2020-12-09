<?php
	session_start();
	$config = parse_ini_file("db.ini");
	$professor = parse_ini_file("website.ini")['professor'];

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if(!isset($_SESSION["TOKEN_DATA"]))
	{
		$_SESSION["redirect"] = "__DIR__login.php";
		header("LOCATION:", $redirect);
	}

	if(TOKEN_DATA["exp"] >= TOKEN_DATA["iat"])
	{
		$_SESSION["redirect"] = "__DIR__login.php";
		header("LOCATION:", $redirect);
	}

	if(!(TOKEN_DATA["email_verified"]))
	{
		$_SESSION["redirect"] = "LOCATION:__DIR__login.php";
		header("LOCATION:" $redirect);
	}

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
