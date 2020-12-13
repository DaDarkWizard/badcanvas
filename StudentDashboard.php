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
		header("LOCATION:index.html");
	}

	echo createStudentHeader($email, "index.html");
	unset($_SESSION["Exam"]);
?>

<div class="container">

<div class="border w-100" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">
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




</div>
</div>

</body>
</html>
