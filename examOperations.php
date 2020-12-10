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
			$statement = $dbh->prepare("INSERT INTO Exam (ExamName, TsCreated, TsClose, TotalPoints, TsRelease) Values(:ExamName, :TsCreated, :TsClose, :TotalPoints, :TsRelease)");
			$result = $statement->execute(array(":ExamName"    => $_POST["ExamName"],
							    ":TsCreated"   => $_POST["TsCreated"],
							    ":TsClose"     => $_POST["TsClose"],
							    ":TotalPoints" => $_POST["TotalPoints"],
							    ":TsRelease"   => $_POST["TsRelease"]));
		}
		else if ($_POST["Operation"] == 'edit')
		{
			$statement = $dbh->prepare("UPDATE Exam set ExamName=:ExamName, TsCreated=:TsCreated, TsClose=:TsClose, TotalPoints=:TotalPoints, TsRelease=:TsRelease WHERE ExamName=:OldExamName");
			$result = $statement->execute(array(":OldExamName" => %_POST["OldExamName"],
						  	    ":ExamName"    => $_POST["ExamName"],
							    ":TsCreated"   => $_POST["TsCreated"],
                                                            ":TsClose"     => $_POST["TsClose"],
                                                            ":TotalPoints" => $_POST["TotalPoints"],
							    ":TsRelease"   => $_POST["TsRelease"]));
		}
		else if ($_POST["Operation"] == 'edit')
		{
			$statement = $dbh->prepare("DELETE FROM Exam where ExamName=:ExamName");
			$result = $statement->execute(array(":ExamName" => $_POST["ExamName"]));
		}

		return;
	} catch (PDOException $e)
	{
		return "ERROR!".$e->getMessage()."<br/>";
		die();
	}

?>
