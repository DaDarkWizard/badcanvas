<html>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<body>

<?php
	//Setup Login
	include_once "InstructorHeader.php";
	include_once "checklogin.php";

	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("InstructorExamList.php");

	if(!$email)
	{
		return;
	}

	$verified = verifyStudent($email);

	if(!$verified)
	{
		header("LOCATION:index.html");
	}

	//Print header
	echo createInstructorHeader($email, "InstructorExamList.php");

	if(!isset($_SESSION["Exam"]))
	{
		if(!isset($_POST["Exam"]))
		{
			header("LOCATION:InstructorDashboard");
			return;
		}
		else
		{
			$_SESSION["Exam"] = $_POST["Exam"];
		}
	}
?>

<div class="container" >

<h2><?php echo $_SESSION["Exam"] ?></h2>

<table class="table">
<thead>
<tr>
<th scope="col">Id</th><th scope="col">Score</th>
</tr>
</thead>

<tbody>

<?php
	//Select statement for getting the student grade for a given exam
	$statement = $dbh->prepare("SELECT TotalPoints, StudentId, TotalScore
								from Exam left join TakenExams
								on Exam.ExamName = TakenExams.ExamName
								where Exam.ExamName=:ExamName and
								Complete=true
								order by StudentId");
	$statement->execute(array(":ExamName" => $_SESSION["Exam"]));

	//print in a table the student score next to their student email (id)
	$row = $statement->fetch();
	while($row != null)
	{
		echo '<tr>';
		echo '<td>'.$row["StudentId"].'</td>';
		echo '<td>'.$row["TotalScore"]." / ".$row["TotalPoints"]."</td>";


		echo '</tr>';
		
		$row = $statement->fetch();
	}

?>
</tbody>
</table>

</div>
</body>
</html>
