<?php
	try {
		include_once "checklogin.php";
		session_start();
		$config = parse_ini_file("db.ini");
		$dbh = new PDO($config['dsn'], $config['username'], $config['password']);

		$email = checklogin("InstructorDashboard");

        $verified = verifyProfessor($email);

    	if(!$verified)
    	{
        	return;
		}

		if($_POST["Operation"] == 'add')
		{
			$statement = $dbh->prepare("INSERT INTO Question (ExamName, QuestionNumber, Text, Points, CorrectChoice) 
										Select :ExamName, Max(QuestionNumber) + 1, '', 0, '' from Question where ExamName=:ExamName");
			echo $result = $statement->execute(array(":ExamName"    => $_POST["ExamName"]));
			echo "hi";
			echo $result;
		}
		else if ($_POST["Operation"] == 'edit')
		{
			$statement = $dbh->prepare("CALL editQuestion(:ExamName, :QuestionNumber, :Text, :Points, :CorrectChoice)");
			$result = $statement->execute(array(":ExamName"    => $_POST["ExamName"],
												":QuestionNumber"     => $_POST["QuestionNumber"],
												":Text"   => $_POST["Text"],
												":Points"   => $_POST["Points"],
												":CorrectChoice"   => $_POST["CorrectChoice"]));
			echo $result;
		}
		else if ($_POST["Operation"] == 'remove')
		{
			$statement = $dbh->prepare("CALL removeQuestion(:QuestionNumber, :ExamName)");
			$result = $statement->execute(array(":QuestionNumber" => $_POST["QuestionNumber"],
												":ExamName" => $_POST["ExamName"]));
			echo $result;
		}

		return;
	} catch (PDOException $e)
	{
		echo "ERROR: ".$e->getMessage();
		//die();
		return;
	}
	return;

?>