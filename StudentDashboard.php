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
	include_once "StudentHeader.php";
	include_once "checklogin.php";

	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("StudentDashboard");

	if(!$email)
	{
		return;
	}

	$verified = verifyStudent($email);

	if(!$verified)
	{
		//header("LOCATION:index.html");
		return;
	}

	echo createStudentHeader($email, "index.html");
	unset($_SESSION["Exam"]);
?>

<div class="container">

<div class="border w-100 border-bottom-0" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">
	<h2>Exams</h2>
</div>

<div class="list-group">

<script type="text/javascript">
	function selectExam(id)
	{
		var form = $('<form action="TakeExamCheck" method="post">' +
					  '<input type="hidden" name="Exam" value="' + id + '" />' +
					  '</form>');
		$('body').append(form);
		form.submit();
	}

	function createDate(mySQLDate)
	{
		var t = mySQLDate.split(/[- :]/);
		// Apply each element to the Date function
		var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
		//console.log(d);
		var hours = (d.getHours() > 12 ? d.getHours() - 12 : d.getHours());
		d = "<strong>Due</strong> " + d.toLocaleString('default', {month: 'short'}) + " " + d.getDate() +
		" at " + hours + ":" + ("00" + d.getMinutes()).substr(-2, 2) + (d.getHours() > 12 ? "pm" : "am");

		return d;
	}

	function setupPage()
	{
		var rowCount = $("#RowCount")[0].value;
		var i = 0;

		while (i < rowCount)
		{
			$("#DateToParse" + i)[0].innerHTML = createDate($("#DateToParse" + i)[0].innerHTML);
			i = i + 1;
		}
	}

	$(document).ready(function() {setupPage();});
</script>

<?php
	$i = 0;

	foreach($dbh->query("select Exam.ExamName, TotalPoints, TsRelease, TsClose, QuestionCount from
						Exam left join
						(select ExamName, Count(*) as QuestionCount from Question) as a
						on Exam.ExamName = a.ExamName") as $row)
	{
		date_default_timezone_set("EST");
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
		//$expire = date_format($expire, "M d g:i A");
		echo '<button class="list-group-item list-group-item-action text-left" style="border-radius:0" onclick="selectExam(\''.$row[0].'\')" ';
		if($about == "Locked")
		{
			echo 'disabled';
		}
		echo ' >';

		echo '<div class="d-flex align-items-center">';
		echo '<img src="BadRocket.png" class="mr-3"/>';
		echo '<div class="d-inline-block">';
		echo '<div class="row no-gutters"><div class="col"><h5 ';

		if($about == "Locked")
		{
			echo 'class="text-muted"';
		}

		echo ' style="color:black;">'.$row[0].'</h5></div></div>';
		echo '<div class="row no-gutters"><div class="col" style="font-size:.75rem;">';
		echo '<strong>'.$about.'</strong> | <div class="d-inline-block" id="DateToParse'.$i.'">'.$expire.'</div> | '.$row[1].' pts | 40 Questions';
		echo '</div></div></div></button>';
		$i = $i + 1;
	}

?>
<input type="hidden" value="<?php echo $i; ?>" id="RowCount" />

</div>
</div>

</body>
</html>
