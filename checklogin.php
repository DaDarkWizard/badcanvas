
<?php
	function checkLogin($redirect)
	{
		if(!isset($_SESSION['TOKEN_DATA']))
		{
			$_SESSION["redirect"] = $redirect;
			$_SESSION["login_task"] = "login";
			header("LOCATION:login.php");
			return false;
		}

		if($_SESSION['TOKEN_DATA']["exp"] <= time())
		{
			$_SESSION["redirect"] = $redirect;
			$_SESSION["login_task"] = "login";
			header("LOCATION:login.php");
			return false;
		}

		return $_SESSION['TOKEN_DATA']['email'];
	}

	function verifyProfessor($email)
	{
		if ($email == false)
		{
			header("LOCATION:index.html");
			return false;
		}
		$config = parse_ini_file("db.ini");

		$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		foreach($dbh->query("SELECT InstructorId FROM Instructor WHERE InstructorId='".$email."'") as $row)
		{
			if($row[0] == $email)
			{
				return true;
			}
		}

		header("LOCATION:index.html");
		return false;
	}

	function verifyStudent($email)
	{
		$config = parse_ini_file("db.ini");

		$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		foreach($dbh->query("SELECT StudentId FROM Student WHERE StudentId='".$email."'") as $row)
		{
			if($row[0] == $email)
			{
				return true;
			}
		}

		echo $email;
		return false;
	}


?>