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
			$statement = $dbh->prepare("INSERT INTO Exam (ExamName, TsClose, TotalPoints, TsRelease) Values(:ExamName, :TsClose, 0, :TsRelease)");
			$result = $statement->execute(array(":ExamName"    => $_POST["ExamName"],
							    ":TsClose"     => $_POST["TsClose"],
							    ":TsRelease"   => $_POST["TsRelease"]));
			echo $result;
		}
		else if ($_POST["Operation"] == 'edit')
		{
			//echo var_dump($_POST);
			$statement = $dbh->prepare("UPDATE Exam set ExamName=:ExamName, TsClose=:TsClose, TsRelease=:TsRelease WHERE ExamName=:OldExamName");
			$result = $statement->execute(array(":OldExamName" => $_POST["OldExamName"],
						  	    ":ExamName"    => $_POST["ExamName"],
                                ":TsClose"     => $_POST["TsClose"],
							    ":TsRelease"   => $_POST["TsRelease"]));
			echo $result;
		}
		else if ($_POST["Operation"] == 'remove')
		{
			$statement = $dbh->prepare("DELETE FROM Exam where ExamName=:ExamName");
			$result = $statement->execute(array(":ExamName" => $_POST["ExamName"]));
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
