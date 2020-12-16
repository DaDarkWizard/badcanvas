<?php

	include_once "StudentHeader.php";
	include_once "checklogin.php";

	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("StudentDashboard");

	if(!$email)
	{
		return;
	}

	$verified = verifyStudent($email);

	if(!$verified)
	{
		header("LOCATION:index.html");
	}

	echo createStudentHeader($email, "StudentDashboard");

	if(!isset($_SESSION["Exam"]))
	{
		if(!isset($_POST["Exam"]))
		{
			header("LOCATION:StudentDashboard");
			return;
		}
		else
		{
			$_SESSION["Exam"] = $_POST["Exam"];
		}
	}

	$statement = $dbh->prepare("SELECT * from TakenExams where StudentId=:StudentId and ExamName=:ExamName");
	$count = $statement->execute(array(":ExamName" => $_SESSION["Exam"],
								":StudentId" => $email));

	if($statement->rowCount() != 1)
	{
		$statement = $dbh->prepare("CALL initializeExamForStudent(:StudentId, :ExamName)");
		$statement->execute(array(":ExamName" => $_SESSION["Exam"],
									":StudentId" => $email));
	}

	header("LOCATION:TakeExam");
	return;

?>