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
	// Get the login.
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

	// Print the header.
	echo createInstructorHeader($email, "InstructorDashboard");

	// Reset the exam session variable.
	unset($_SESSION["Exam"]);
?>

<div class="container">
<!--Exams header-->
<div class="border border-bottom-0 w-100" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">
	<h2>Exams</h2>
</div>

<div class="list-group">

<script type="text/javascript">
	// takes the instructor to the edit page for an exam.
	function selectExam(id)
	{
		var form = $('<form action="EditExam" method="post">' +
					  '<input type="hidden" name="Exam" value="' + id + '" />' +
					  '</form>');
		$('body').append(form);
		form.submit();
	}

	// Parses a mySql date to the canvasy way.
	function createDate(mySQLDate)
	{
		var t = mySQLDate.split(/[- :]/);
		// Apply each element to the Date function
		var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
		var hours = (d.getHours() > 12 ? d.getHours() - 12 : d.getHours());
		d = "<strong>Due</strong> " + d.toLocaleString('default', {month: 'short'}) + " " + d.getDate() +
		" at " + hours + ":" + ("00" + d.getMinutes()).substr(-2, 2) + (d.getHours() > 12 ? "pm" : "am");

		return d;
	}

	// Initializes the page.
	function setupPage()
	{
		var rowCount = $("#RowCount")[0].value;
		var i = 0;
		while(i < rowCount)
		{
			// Parse every date.
			$("#DateToParse" + i)[0].innerHTML = createDate($("#DateToParse" + i)[0].innerHTML);
			i = i + 1;
		}
	}

	// Takes the instructor to view student scores.
	function viewScores(id)
	{
		var form = $('<form action="ViewScores" method="post">' +
					  '<input type="hidden" name="Exam" value="' + id + '" />' +
					  '</form>');
		$('body').append(form);
		form.submit();
	}

	$(document).ready(function() {setupPage();});

</script>


<?php
	$i = 0;

	// Gets all exams.
	foreach($dbh->query("select Exam.ExamName, TotalPoints, TsRelease, TsClose, QuestionCount from
						Exam left join
						(select ExamName, Count(*) as QuestionCount from Question) as a
						on Exam.ExamName = a.ExamName") as $row)
	{
		// Checks the datetime
		date_default_timezone_set("UTC");
		$expire = $row[3];
		$release = date_create($row[2]);
		$about = "Locked";
		if($release < date_create())
		{
			$about = "Open";
		}
		if(date_create($expire) < date_create())
		{
			$about = "Closed";
		}
		
		// Prints out one exam.
		echo '<div class="row no-gutters">';
		echo '<div class="col-10" >';
		echo '<button class="list-group-item list-group-item-action text-left border-bottom-0" style="border-radius:0" onclick="selectExam(\''.$row[0].'\')">';

		echo '<div class="d-flex align-items-center">';
		echo '<img src="BadRocket.png" class="mr-3"/>';
		echo '<div class="d-inline-block">';
		echo '<div class="row no-gutters"><div class="col"><h5 style="color:black;">'.$row[0].'</h5></div></div>';
		echo '<div class="row no-gutters"><div class="col" style="font-size:.75rem;">';
		echo '<strong>'.$about.'</strong> | <div class="d-inline-block" id="DateToParse'.$i.'">'.$expire.'</div> | '.$row[1].' pts | '.($row["QuestionCount"] == null ? 0 : $row["QuestionCount"]).' Questions';
		echo '</div></div></div></button>';
		echo '</div>';
		echo '<div class="col-2" >';
		echo '<button class="list-group-item list-group-item-action text-center border-bottom-0 border-left-0" style="border-radius:0;padding-bottom:13px;" onclick="viewScores(\''.$row[0].'\')" >';
		echo '<h5 class="mb-0">View</h5><h5 class="mb-0">Scores</h5>';
		echo '</button>';
		echo '</div>';
		echo '</div>';
		$i = $i + 1;
	}

?>

<!--Button to create new exam.-->
<input type="hidden" value="<?php echo $i; ?>" id="RowCount" />
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