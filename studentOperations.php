<?php
try {
    //This sets up the operations and verifies
    include_once "checklogin.php";
	
	session_start();

    $config = parse_ini_file("db.ini");
    $dbh = new PDO($config['dsn'], $config['username'],$config['password']);

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = checklogin("InstructorDashboard");

	$verified = verifyProfessor($email);

    if(!$verified)
    {
        return;
    }

    //operation for addeding a student into the student table
    if($_POST["Operation"] == 'add')
    {
        $statement = $dbh->prepare("INSERT INTO Student (StudentId, Name, Major) Values(:StudentId, :Name, :Major)");

        $result = $statement->execute(array(":StudentId" => $_POST["StudentId"],
                                            ":Name" => $_POST["Name"],
                                            ":Major" => $_POST["Major"]));
    }
    else if ($_POST["Operation"] == 'edit') //operation for editing a student in the student table
    {
        $statement = $dbh->prepare("UPDATE Student set Name=:Name, Major=:Major where StudentId=:StudentId");

        $result = $statement->execute(array(":StudentId" => $_POST["StudentId"],
                                            ":Name" => $_POST["Name"],
                                            ":Major" => $_POST["Major"]));
    }
    else if ($_POST["Operation"] == 'remove') //operation for removing a student
    {
        $statement = $dbh->prepare("CALL removeStudent(:StudentId)");
        $result = $statement->execute(array(":StudentId" => $_POST["StudentId"]));
    }

    return;
} catch (PDOException $e) {
    return "ERROR!".$e->getMessage()."<br/>";
    die();

}
?>
