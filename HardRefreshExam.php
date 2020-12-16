<?php
	// Called when we need to reset the exam session variable.
	include_once "checklogin.php";
	session_start();
	$config = parse_ini_file("db.ini");

	$email = checklogin("InstructorDashboard");

    $verified = verifyProfessor($email);

    if(!$verified)
    {
        return;
	}
	
	$_SESSION['Exam'] = $_POST['Exam'];
	header("LOCATION:EditExam.php");
	return;
?>