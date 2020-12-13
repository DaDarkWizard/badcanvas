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

<script type="text/javascript">

//Extend jquery to make easy post redirects.
$.extend(
{
    redirectPost: function(location, args)
    {
        var form = $('<form></form>');
        form.attr("method", "post");
        form.attr("action", location);

        $.each( args, function( key, value ) {
            var field = $('<input></input>');

            field.attr("type", "hidden");
            field.attr("name", key);
            field.attr("value", value);

            form.append(field);
        });
        $(form).appendTo('body').submit();
    }
});

</script>

<?php

	include_once "checklogin.php";
	include_once "InstructorHeader.php";
	
	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("InstructorExamList");
	$verified = verifyProfessor($email);
	if(!$verified)
	{
		header("LOCATION:index.html");
		return;
	}

	echo createInstructorHeader($email, "InstructorExamList");

?>
<div class="container">

<script type="text/javascript">
	function test()
	{
		
	}
	function createExam()
	{
		var ExamName = $("#ExamName")[0].value;
		var DateRelease = new Date($("#DateRelease")[0].value).toISOString().slice(0,19).replace('T', ' ');
		var DateClose = new Date($("#DateClose")[0].value).toISOString().slice(0, 19).replace('T', ' ');

		//console.log(idNode.children);
		console.log(ExamName);
		console.log(DateRelease);
		console.log(DateClose);
		return;
		$.post("examOperations.php",
			{
				Operation: "add",
				ExamName: ExamName,
				TsRelease: DateRelease,
				TsClose: DateClose
			},
			function(data, status){
				console.log(data);
				console.log(status);
				if(data.indexOf("1") == -1)
				{
					console.log("FAILURE");
					var alert = $("<div></div>").attr({
						"class":"alert alert-danger alert-dismissable fade show"
					});
					
					alert[0].innerHTML = "<button type='button' class='close' data-dismiss='alert'>&times;</button><strong>Warning</strong> Failed to create exam... ID already exists.";
					$("#beginQ").prepend(alert);
				}
				else
				{
					console.log("SUCCESS!");
					$.redirectPost('EditExam.php', {'ExamName': ExamName});
				}
		});
	}
</script>

<div class="form-group" id="beginQ">
	<label for="ExamName">Exam Name:</label>
	<input type="text" class="form-control" placeholder="Enter Exam Name" id="ExamName">
</div>
<div class="form-group">
	<label for="DateRelease">Release Date:</label>
	<input type="datetime-local" class="form-control" id="DateRelease"/>
</div>
<div class="form-group">
	<label for="DateClose">Close Date:</label>
	<input type="datetime-local" class="form-control" id="DateClose"/>
</div>
<button type="button" class="btn btn-primary" onclick="createExam()">Submit</button>
</div>

<button type="button" class="btn btn-danger" onclick="test()">TEST</button>

</body>
</html>