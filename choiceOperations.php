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
			$statement = $dbh->prepare("INSERT INTO Choice (ExamName, QuestionNumber, ChoiceId, Text) 
										values( :ExamName, :QuestionNumber, :ChoiceId, :Text)");
			$statement->execute(array(":ExamName"    => $_POST["ExamName"],
												":QuestionNumber" => $_POST["QuestionNumber"],
												":ChoiceId" => $_POST["ChoiceId"],
												":Text" => $_POST["Text"]));
			echo $statement->rowCount();
		}
		else if ($_POST["Operation"] == 'edit')
		{
			$statement = $dbh->prepare("CALL modifyChoice(:ExamName, :QuestionNumber, :ChoiceId, :NewChoiceId, :Text, @result)");
			$statement->execute(array(":ExamName"    => $_POST["ExamName"],
                                ":QuestionNumber"     => $_POST["QuestionNumber"],
							    ":ChoiceId"   => $_POST["ChoiceId"],
								":NewChoiceId" => $_POST["NewChoiceId"],
								":Text" => $_POST["Text"]));

			$statement = $dbh->query("Select @result");
			echo $statement->fetchAll()[0][0];
		}
		else if ($_POST["Operation"] == 'remove')
		{	
			$statement = $dbh->prepare("DELETE from Choice where ExamName=:ExamName and QuestionNumber=:QuestionNumber and ChoiceId=:ChoiceId");
			$statement->execute(array(":QuestionNumber" => $_POST["QuestionNumber"],
												":ExamName" => $_POST["ExamName"],
												":ChoiceId" => $_POST["ChoiceId"]));
			echo $statement->rowCount();
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