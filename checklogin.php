
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
			return false;
		}

		$professor = parse_ini_file("website.ini")['professor'];

		if($email == $professor)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
?>