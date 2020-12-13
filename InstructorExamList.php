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

	include_once "checklogin.php";
	include_once "InstructorHeader.php";
	
	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("InstructorExamList");
	if(!$email)
    {
        return;
    }
	$verified = verifyProfessor($email);
	if(!$verified)
	{
		header("LOCATION:index.html");
		return;
	}

	echo createInstructorHeader($email, "InstructorDashboard");
	unset($_SESSION["Exam"]);
?>

<div class="container">

<div class="border border-bottom-0 w-100" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">
	<h2>Exams</h2>
</div>

<div class="list-group">

<script type="text/javascript">
	function selectExam(id)
	{
		var form = $('<form action="EditExam" method="post">' +
					  '<input type="hidden" name="Exam" value="' + id + '" />' +
					  '</form>');
		$('body').append(form);
		form.submit();
	}
</script>


<?php
	foreach($dbh->query("select Exam.ExamName, TotalPoints, TsRelease, TsClose, QuestionCount from
						Exam left join
						(select ExamName, Count(*) as QuestionCount from Question) as a
						on Exam.ExamName = a.ExamName") as $row)
	{
		date_default_timezone_set("EST");
		$expire = date_create($row[3]);
		$release = date_create($row[2]);
		$about = "Locked";
		if($release < date_create())
		{
			$about = "Open";
		}
		if($expire < date_create())
		{
			$about = "Closed";
		}
		//$expire = date_format($expire, "M d g:i A");
		echo '<button class="list-group-item list-group-item-action text-left" style="border-radius:0" onclick="selectExam(\''.$row[0].'\')">';

		echo '<div class="d-flex align-items-center">';
		echo '<img src="BadRocket.png" class="mr-3"/>';
		echo '<div class="d-inline-block">';
		echo '<div class="row no-gutters"><div class="col"><h5 style="color:black;">'.$row[0].'</h5></div></div>';
		echo '<div class="row no-gutters"><div class="col" style="font-size:.75rem;">';
		echo '<strong>'.$about.'</strong> | <strong>Due</strong> '.date_format($expire, "M d").' at '.date_format($expire, "g:ia").' | '.$row[1].' pts | 40 Questions';
		echo '</div></div></div></button>';
	}

?>

<button class="list-group-item list-group-item-action text-left" style="border-radius:0px 0px 3px 3px;" onclick="window.location.href='CreateExam.php'">
	<div class="d-flex align-items-center">
	<img src="BadRocket.png" class="mr-3"/>
	<div class="d-inline-block">
		<div class="row no-gutters"><div class="col"><h5 style="color:black;">New Exam</h5></div></div>
		<div class="row no-gutters"><div class="col" style="font-size:.75rem;">
			Create new Exam
		</div></div>
	</div>
	</div>
</button>

</div>

</div>

</body>
</html>