<?php
	try {
		// Get email
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

		// add question to database.
		if($_POST["Operation"] == 'add')
		{
			$statement = $dbh->prepare("INSERT INTO Question (ExamName, QuestionNumber, Text, Points, CorrectChoice) 
										Select :ExamName, IF(Max(QuestionNumber) is NULL, 1, Max(QuestionNumber) + 1), '', 0, '' from Question where ExamName=:ExamName");
			echo $result = $statement->execute(array(":ExamName"    => $_POST["ExamName"]));
			echo "hi";
			echo $result;
		}
		// edit question in database.
		else if ($_POST["Operation"] == 'edit')
		{
			$statement = $dbh->prepare("CALL editQuestion(:ExamName, :QuestionNumber, :Text, :Points, :CorrectChoice, @returnResult)");
			$statement->bindParam(':ExamName', $_POST["ExamName"]);
			$statement->bindParam(':QuestionNumber', $_POST["QuestionNumber"]);
			$statement->bindParam(':Text', $_POST["Text"]);
			$statement->bindParam(':Points', $_POST["Points"]);
			$statement->bindParam(':CorrectChoice', $_POST["CorrectChoice"]);
			$statement->execute();

			$statement = $dbh->query("SELECT @returnResult");
			
			echo $statement->fetchAll()[0][0];
			return;
		}
		// Remove a question from the exam.
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