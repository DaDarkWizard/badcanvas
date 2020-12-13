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
<style>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

</style>
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

	echo createInstructorHeader($email, "InstructorExamList");
	
	if(!isset($_SESSION["Exam"]))
	{
		if(!isset($_POST["Exam"]))
		{
			header("LOCATION:InstructorExamList");
			return;
		}
		else
		{
			$_SESSION["Exam"] = $_POST["Exam"];
		}
	}

	$examName = $_SESSION["Exam"];
	$statement = $dbh->prepare("SELECT ExamName, TotalPoints, TsRelease, TsClose from Exam where ExamName=:ExamName");
	$statement->execute(array(":ExamName" => $_SESSION["Exam"]));
	$results = $statement->fetch();

	$examName = $results[0];
	//$totalPoints = $results[1];
	$tsRelease = $results[2];
	$tsClose = $results[3];
?>

<script type="text/javascript">

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


	function parseDateTime(timeString)
	{
		//console.log(timeString);
		var t = timeString.split(/[- :]/);

		// Apply each element to the Date function
		var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
		//console.log(d);
		d = "" + d.getFullYear() + "-" + 
				("00" + (d.getMonth() + 1)).substr(-2, 2) +
				"-" + ("00" + d.getDate()).substr(-2, 2) + "T"
				+ ("00" + d.getHours()).substr(-2, 2) + ":"
				+ ("00" + d.getMinutes()).substr(-2, 2);
		return d;
	}

	function setupPage()
	{
		$("#TsRelease")[0].value = parseDateTime("<?php echo $tsRelease;?>");
		$("#TsClose")[0].value = parseDateTime("<?php echo $tsClose;?>");
	}

	$(document).ready(function() {setupPage();});

	
	function updateExam()
	{		
		$.post("examOperations.php",
		{
			Operation: "edit",
			OldExamName: "<?php echo $_SESSION['Exam'];?>",
			ExamName: $("#ExamName")[0].value,
			TsClose: new Date($("#TsClose")[0].value).toISOString().slice(0,19).replace('T', ' '),
			TsRelease: new Date($("#TsRelease")[0].value).toISOString().slice(0,19).replace('T', ' ')
		},
		function(data, success){
			console.log(data);
			console.log(success);
			if(data.indexOf("1") == -1)
			{
				setupPage();
				$("#ExamName")[0].value = "<?php echo $_SESSION['Exam'];?>";

				var alert = $("<div></div>").attr({
					"class":"alert alert-danger alert-dismissable fade show"
				});
					
				alert[0].innerHTML = "<button type='button' class='close' data-dismiss='alert'>&times;</button><strong>Warning</strong> Failed to rename Exam... ID already exists.";
				$("#start")[0].prepend(alert[0]);
			}
			else
			{
				$.post("HardRefreshExam.php", {Exam: $("#ExamName")[0].value}, function(data, success){location.reload();});
				
				//});
			}
		});
	}

	function addQuestion()
	{
		$.post("questionOperations.php",
		{
			Operation: "add",
			ExamName: "<?php echo $_SESSION['Exam'];?>"
		},
		function (data, success)
		{
			location.reload();
		});
	}

	function removeQuestion(id)
	{
		$.post("questionOperations.php",
		{
			Operation: "remove",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: id
		},
		function (data, success)
		{
			location.reload();
		});
	}

	function addChoice(questionNumber)
	{	
		$.post("choiceOperations.php",
		{
			Operation: "add",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: questionNumber,
			ChoiceId: $("#q" + questionNumber + "addid")[0].value,
			Text:	$("#q" + questionNumber + "addtext")[0].value
		},
		function(data, success)
		{
			location.reload();
		});
	}

	function setCorrectAnswer(questionNumber, choiceId)
	{
		$.post("questionOperations.php",
		{
			Operation: "edit",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: questionNumber,
			Text:	$("#q" + questionNumber + "text")[0].innerHTML,
			Points: $("#q" + questionNumber + "points")[0].value,
			CorrectChoice: choiceId
		},
		function(data, success)
		{
			location.reload();
		});
	}

	function removeChoice(questionNumber, choiceId)
	{
		$.post("choiceOperations.php",
		{
			Operation: "remove",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: questionNumber,
			ChoiceId: choiceId
		},
		function(data, success)
		{
			location.reload();
		});
	}

	function editQuestion(questionNumber, correctChoiceId)
	{
		$.post("questionOperations.php",
		{
			Operation: "edit",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: questionNumber,
			Text:	$("#q" + questionNumber + "text")[0].value,
			Points: $("#q" + questionNumber + "points")[0].value,
			CorrectChoice: correctChoiceId
		},
		function(data, success)
		{
			location.reload();
		});
	}

	function editChoice(questionNumber, oldChoiceId)
	{
		$.post("choiceOperations.php",
		{
			Operation: "edit",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: questionNumber,
			ChoiceId: oldChoiceId,
			NewChoiceId: $("#q" + questionNumber + "c" + oldChoiceId + "id")[0].value,
			Text: $("#q" + questionNumber + "c" + oldChoiceId + "text")[0].value
		},
		function(data, success)
		{
			location.reload();
		});
	}

</script>

<div class='container'>

<!-- Exam title and date edits. -->

<div class="form-group" id="start">
<label for="ExamName">Exam Name</label>
<input type="text" class="form-control" onchange="updateExam()" id="ExamName" value="<?php echo $examName;?>"/>
</div>
<div class="row">
	<div class="col">
		<div class="form-group">
			<label for="TsRelease">Release Date</label>
			<input type="datetime-local" class="form-control" onchange="updateExam()" id="TsRelease" />
		</div>
	</div>
	<div class="col">
		<div class="form-group">
			<label for="TsRelease">Close Date</label>
			<input type="datetime-local" class="form-control" onchange="updateExam()" id="TsClose" />
		</div>
	</div>
</div>

<!-- Question Edits -->


<?php

	$questionStatement = $dbh->prepare("SELECT ExamName, QuestionNumber, Text, Points, CorrectChoice from Question where ExamName=:ExamName order by QuestionNumber");
	$questionStatement->execute(array(":ExamName" => $_SESSION["Exam"]));
	$qrow = $questionStatement->fetch();
	while($qrow != null)
	{
		echo '<div class="border border-bottom-0 w-100" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">';
		echo '<h5 class="mr-3 d-inline-block">Question '.$qrow[1].'</h5>';
		echo '<button class="btn btn-danger" onclick="removeQuestion('.$qrow[1].')">Delete Question</button>';
		echo '<span class="float-right" ><input type="number" class="form-control d-inline-block" style="width:70px;" value='.$qrow[3].' id="q'.$qrow[1].'points" onchange="editQuestion('.$qrow[1].', \''.$qrow[4].'\')" /> Points</span>';
		echo '</div>';

		echo '<div class="border w-100" >';
		echo '<textarea type="text-area" class="p-3 mb-1 form-control d-inline-block" style="" id="q'.$qrow[1].'text" onchange="editQuestion('.$qrow[1].', \''.$qrow[4].'\')" >'.$qrow[2].'</textarea>';

		echo '<div class="list-group list-group-flush ml-3 mr-3 mb-3">';

		$choiceStatement = $dbh->prepare("SELECT ExamName, QuestionNumber, ChoiceId, Text from Choice where ExamName=:ExamName and QuestionNumber=:QuestionNumber order by ChoiceId");
		$choiceStatement->execute(array(":ExamName" => $_SESSION["Exam"], ":QuestionNumber" => $qrow["QuestionNumber"]));
		$crow = $choiceStatement->fetch();

		while($crow != null)
		{
			$cid = 'q'.$qrow["QuestionNumber"].'c'.$crow["ChoiceId"];
			echo '<div class="list-group-item" ><div class="row no-gutters" >';
			echo '<div class="col-2">';
			echo '<input type="radio" class="form-check-input" name="radio1" '.($crow["ChoiceId"] == $qrow["CorrectChoice"] ? "checked" : ("onclick=\"setCorrectAnswer(".$qrow[1].", '".$crow["ChoiceId"]."')\"") ).' />';
			//echo '<div class="col-2">';
			echo '<label for="'.$cid.'id" >Id:</label>';
			echo '<input type="text" class="form-control" style="width:100px;" value="'.$crow["ChoiceId"].'" id="'.$cid.'id" onchange="editChoice('.$qrow[1].', \''.$crow["ChoiceId"].'\')" /></div>';
			echo '<div class="col-8">';
			echo '<label class="d-inline-block" for="'.$cid.'text" >Text:</label>';
			echo '<input type="text" class="form-control" value="'.$crow["Text"].'" id="'.$cid.'text" onchange="editChoice('.$qrow[1].', \''.$crow["ChoiceId"].'\')" /></div>';
			echo '<div class="col-2 text-right">';
			echo '<button class="btn btn-danger" style="margin-top:32px;" onclick="removeChoice('.$qrow[1].', \''.$crow["ChoiceId"].'\')">Remove</button>';
			echo '</div>';

			echo '</div></div>';
			$crow = $choiceStatement->fetch();
		}

		echo '<div class="list-group-item" ><div class="row no-gutters">';
		echo '<div class="col-2">';
		echo '<label for="q'.$qrow["QuestionNumber"].'addid" >Id:</label>';
		echo '<input type="text" class="form-control" style="width:100px;" id="q'.$qrow["QuestionNumber"].'addid" /></div>';
		echo '<div class="col-8">';
		echo '<label class="d-inline-block" for="q'.$qrow["QuestionNumber"].'addtext" >Text:</label>';
		echo '<input type="text" class="form-control" value="" id="q'.$qrow["QuestionNumber"].'addtext" /></div>';
		echo '<div class="col-2 text-right">';
		echo '<button class="btn btn-success" style="margin-top:32px;" onclick="addChoice('.$qrow["QuestionNumber"].')">Add</button>';
		echo '</div></div></div>';

		echo '</div>';
		echo '</div>';




		$qrow = $questionStatement->fetch();
	}
?>

<button class="btn btn-success mt-3 mb-5  float-right" onclick="addQuestion()">Add Question</button>

</div>


</body>
</html>