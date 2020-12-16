<?php
	try
	{
		include_once "checklogin.php";
		session_start();
		$config = parse_ini_file("db.ini");
		$dbh = new PDO($config['dsn'], $config['username'], $config['password']);

		$email = checklogin("InstructorDashboard");

		$verified = verifyStudent($email);

		if(!$verified)
		{
			return;
		}
		

		if ($_POST["Operation"] == 'edit')
		{
			//echo var_dump($_POST);
			$statement = $dbh->prepare("UPDATE QuestionAnswer set ChosenAnswer=:ChoiceId where ExamName=:ExamName and QuestionNumber=:QuestionNumber and StudentId=:StudentId");
			$statement->execute(array(":ExamName"    => $_POST["ExamName"],
                                ":QuestionNumber"     => $_POST["QuestionNumber"],
							    ":ChoiceId"   => $_POST["ChoiceId"],
								":StudentId" => $_POST["StudentId"]));
			echo $statement->rowCount();
		}
		else if ($_POST["Operation"] == "submit")
		{
			$statement = $dbh->prepare("CALL submitExam(:ExamName, :StudentId)");
			$statement->execute(array(":ExamName" => $_POST["ExamName"],
									":StudentId" => $_POST["StudentId"]));
			return;
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