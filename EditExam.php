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

<!-- Removes the spinner from number inputs. -->
<style>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

</style>
<body>

<?php
	// Setup the login.
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
	echo createInstructorHeader($email, "InstructorExamList");
	
	// Check the exam variable.
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

	// Get the exam.
	$examName = $_SESSION["Exam"];
	$statement = $dbh->prepare("SELECT ExamName, TotalPoints, TsRelease, TsClose from Exam where ExamName=:ExamName");
	$statement->execute(array(":ExamName" => $_SESSION["Exam"]));
	

	$results = $statement->fetch();

	// Store quick and easy variables for the exam.
	$examName = $results[0];
	$tsRelease = $results[2];
	$tsClose = $results[3];
?>

<script type="text/javascript">

	$.extend(
	{
		// Redirects with a post request to the given location.
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

	// Parses an sql datetime into something readable by datetime-local inputs.
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

	// Called when the page is first loaded.
	function setupPage()
	{
		//Parse the datetimes.
		$("#TsRelease")[0].value = parseDateTime("<?php echo $tsRelease;?>");
		$("#TsClose")[0].value = parseDateTime("<?php echo $tsClose;?>");
	}

	// Enable our document load function.
	$(document).ready(function() {setupPage();});

	// Called when the exam entry needs to tbe updated.
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
			
			// Check returned data.
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
				
			}
		});
	}

	// Add a question to the exam.
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

	// Remove a question from the exam.
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

	// Add a choice to a question.
	function addChoice(questionNumber)
	{	
		// store the values from and empty the new choice inputs.
		var newChoiceId = $("#q" + questionNumber + "addid")[0].value;
		$("#q" + questionNumber + "addid")[0].value = "";
		var newChoiceText = $("#q" + questionNumber + "addtext")[0].value;
		$("#q" + questionNumber + "addtext")[0].value = "";

		// Check that the id is valid.
		if(newChoiceId == "")
		{
			return;
		}
		// Post the attempt.
		$.post("choiceOperations.php",
		{
			Operation: "add",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: questionNumber,
			ChoiceId: newChoiceId,
			Text: newChoiceText
		},
		function(data, success)
		{	
			// Check the return result.
			if(data.trim() != "1")
			{
				// Something went wrong, reload the page.
				location.reload();
			}
			else
			{
				// Everythings's ok, create the new choice element and add it to the question.

				// Create the wrapper
				var newChoice = $("<div></div>");
				newChoice.attr("class", "list-group-item");
				newChoice[0].id = "q" + questionNumber + "c" + newChoiceId + "wrapper";

				// Make the radio button.
				var radio = $('<input type="radio" class="form-check-input" name="radio' + questionNumber + '" />');
				radio.attr('onclick', 'setCorrectAnswer(' + questionNumber + ', "' + newChoiceId + '")');
				if(newChoiceId == $("#q" + questionNumber + "correctChoice")[0].value)
				{
					radio.attr("checked", true);
				}

				// Make the id input.
				var idInput = $('<input type="text" class="form-control" style="width:100px;" value="' + newChoiceId + '" id="q' + questionNumber + 'c' + newChoiceId + 'id" />');
				idInput.attr("onchange", "editChoice(" + questionNumber + ", '" + newChoiceId + "')");

				// Make the text input.
				var textInput = $('<input type="text" class="form-control" value="' + newChoiceText + '" id="q' + questionNumber + 'c' + newChoiceId + 'text" />');
				textInput.attr("onchange", "editChoice(" + questionNumber + ", '" + newChoiceId + "')");

				// Create the delete button.
				var deleteButton = $('<button class="btn btn-danger" style="margin-top:32px;" id="q' + questionNumber + 'c' + newChoiceId + 'delete" >Remove</button>');
				deleteButton.attr("onclick", 'removeChoice(' + questionNumber + ', "' + newChoiceId + '")');

				// Create the outer row div.
				var rowDiv = $('<div class="row no-gutters" ></div>');
				newChoice.prepend(rowDiv);
				
				// Create first column and add all elements to it.
				var colDiv = $('<div class="col-2" ></div>');
				colDiv.prepend(idInput);
				colDiv.prepend($('<label for="q' + questionNumber + 'c' + newChoiceId + 'id" >Id:</label>'));
				colDiv.prepend(radio);
				rowDiv.append(colDiv);

				// Create second column and add all elements to it.
				colDiv = $('<div class="col-8" ></div>');
				colDiv.prepend(textInput);
				colDiv.prepend($('<label for="q' + questionNumber + 'c' + newChoiceId + 'text" >Text:</label>'));
				rowDiv.append(colDiv);

				// Create third column and add all elements to it.
				colDiv = $('<div class="col-2 text-right" ></div>');
				colDiv.prepend(deleteButton);
				rowDiv.append(colDiv);

				// Add the row to the newChoice element.
				newChoice.prepend(rowDiv);

				// Put newChoice before the addChoice input.
				$("#q" + questionNumber + "addwrapper").before(newChoice);
				
			}
		});
	}

	// Set the correct answer.
	function setCorrectAnswer(questionNumber, choiceId)
	{
		// Verify the radio button wasn't already selected.
		if(choiceId == $("#q" + questionNumber + "correctChoice")[0].value)
		{
			return;
		}
		// Post the update operation.
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
			// Verify the return result.
			if(data.trim() != "1")
			{
				// Something went wrong, reload the page.
				location.reload();
			}
		});
	}

	// Remove a choice from the question.
	function removeChoice(questionNumber, choiceId)
	{
		// Post the update operation.
		$.post("choiceOperations.php",
		{
			Operation: "remove",
			ExamName: "<?php echo $_SESSION['Exam'];?>",
			QuestionNumber: questionNumber,
			ChoiceId: choiceId
		},
		function(data, success)
		{
			// Verify the returned result.
			if(data.trim() != "1")
			{
				// Something went wrong, reload the page.
				location.reload();
			}
			else
			{
				// Remove the choice from the question in the UI.
				$("#q" + questionNumber + "c" + choiceId + "wrapper").remove();
			}
		});
	}

	// Edits a question.
	function editQuestion(questionNumber, correctChoiceId)
	{
		// Post the operation.
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
			// Verify the return result.
			if(data.trim() != "1")
			{
				// Something went wrong, reload the page.
				location.reload();
			}
		});
	}

	// Edit a choice.
	function editChoice(questionNumber, oldChoiceId)
	{
		// Post the operation.
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
			// Verify the returned result.
			if(data.trim() != "1")
			{
				// Something went wrong, reload the page.
				location.reload();
			}
			else{
				// Get the new choice id.
				var newChoiceId = $("#q" + questionNumber + "c" + oldChoiceId + "id")[0].value;

				// If the choice id changed, we need to update all elements in the choice.
				if(oldChoiceId != newChoiceId)
				{
					// If this is the correct answer, update the id on the stored correct answer value.
					if($("#q" + questionNumber + "correctChoice")[0].value == oldChoiceId)
					{
						$("#q" + questionNumber + "correctChoice")[0].value = newChoiceId;
					}

					// Update the id input.
					$("#q" + questionNumber + "c" + oldChoiceId + "id").removeAttr('onchange');
					$("#q" + questionNumber + "c" + oldChoiceId + "id").attr('onchange', 'editChoice(' + questionNumber + ', "' + newChoiceId + '")');
					$("#q" + questionNumber + "c" + oldChoiceId + "id")[0].id = "q" + questionNumber + "c" + newChoiceId + "id";

					// Update the text input.
					$("#q" + questionNumber + "c" + oldChoiceId + "text").removeAttr('onchange');
					$("#q" + questionNumber + "c" + oldChoiceId + "text").attr('onchange', 'editChoice(' + questionNumber + ', "' + newChoiceId + '")');
					$("#q" + questionNumber + "c" + oldChoiceId + "text")[0].id = "q" + questionNumber + "c" + newChoiceId + "text";

					// Update the radio input.
					$("#q" + questionNumber + "c" + oldChoiceId + "radio").removeAttr('onclick');
					$("#q" + questionNumber + "c" + oldChoiceId + "radio").attr('onclick', 'setCorrectAnswer(' + questionNumber + ', "' + newChoiceId + '")');
					$("#q" + questionNumber + "c" + oldChoiceId + "radio")[0].id = "q" + questionNumber + "c" + newChoiceId + "radio";

					// Update the delete button.
					$("#q" + questionNumber + "c" + oldChoiceId + "delete").removeAttr('onclick');
					$("#q" + questionNumber + "c" + oldChoiceId + "delete").attr('onclick', 'removeChoice(' + questionNumber + ', "' + newChoiceId + '")');
					$("#q" + questionNumber + "c" + oldChoiceId + "delete")[0].id = "q" + questionNumber + "c" + newChoiceId + "delete";

					// Update the wrapper.
					$("#q" + questionNumber + "c" + oldChoiceId + "wrapper")[0].id = "q" + questionNumber + "c" + newChoiceId + "wrapper";
				}
				
			}
		});
	}

	// Deletes the exam.
	function removeExam()
	{
		// Confirm they want the exam deleted.
		if(confirm("Are you sure you want to delete this Exam?"))
		{
			// Post the requests.
			$.post("examOperations.php",
			{
				Operation: "remove",
				ExamName: "<?php echo $_SESSION['Exam'];?>"
			},
			function (data, success)
			{
				// Go back to the exam list.
				window.location.href="InstructorExamList";
			});
		}
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
	
	$nextQuestion = 1;
	$questionStatement = $dbh->prepare("SELECT ExamName, QuestionNumber, Text, Points, CorrectChoice from Question where ExamName=:ExamName order by QuestionNumber");
	$questionStatement->execute(array(":ExamName" => $_SESSION["Exam"]));
	$qrow = $questionStatement->fetch();
	while($qrow != null)
	{
		// Adds everything needed to edit/view a question.
		echo '<div id="q'.$qrow["QuestionNumber"].'wrapper" >';
		echo '<div class="border border-bottom-0 w-100" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">';
		echo '<h5 class="mr-3 d-inline-block">Question '.$qrow[1].'</h5>';
		echo '<button class="btn btn-danger" onclick="removeQuestion('.$qrow[1].')">Delete Question</button>';
		echo '<span class="float-right" ><input type="number" class="form-control d-inline-block" style="width:70px;" value='.$qrow[3].' id="q'.$qrow[1].'points" onchange="editQuestion('.$qrow[1].', \''.$qrow[4].'\')" /> Points</span>';
		echo '</div>';

		echo '<div class="border w-100" >';
		echo '<textarea type="text-area" class="p-3 mb-1 form-control d-inline-block" style="" id="q'.$qrow[1].'text" onchange="editQuestion('.$qrow[1].', \''.$qrow[4].'\')" >'.$qrow[2].'</textarea>';

		echo '<div class="list-group list-group-flush ml-3 mr-3 mb-3">';

		echo '<input type="hidden" value="'.$qrow["CorrectChoice"].'" id="q'.$qrow["QuestionNumber"].'correctChoice" />';

		$choiceStatement = $dbh->prepare("SELECT ExamName, QuestionNumber, ChoiceId, Text from Choice where ExamName=:ExamName and QuestionNumber=:QuestionNumber order by ChoiceId");
		$choiceStatement->execute(array(":ExamName" => $_SESSION["Exam"], ":QuestionNumber" => $qrow["QuestionNumber"]));
		$crow = $choiceStatement->fetch();

		while($crow != null)
		{
			// Adds everything to edit/view a choice.
			$cid = 'q'.$qrow["QuestionNumber"].'c'.$crow["ChoiceId"];
			echo '<div class="list-group-item" id="q'.$qrow["QuestionNumber"].'c'.$crow["ChoiceId"].'wrapper"><div class="row no-gutters" >';
			echo '<div class="col-2" >';
			echo '<input type="radio" class="form-check-input" name="radio'.$qrow["QuestionNumber"].'" '.($crow["ChoiceId"] == $qrow["CorrectChoice"] ? "checked" : "").(" onclick=\"setCorrectAnswer(".$qrow[1].", '".$crow["ChoiceId"]."')\"");
			echo 'id="q'.$qrow["QuestionNumber"].'c'.$crow["ChoiceId"].'radio" />';
			echo '<label for="'.$cid.'id" >Id:</label>';
			echo '<input type="text" class="form-control" style="width:100px;" value="'.$crow["ChoiceId"].'" id="'.$cid.'id" onchange="editChoice('.$qrow[1].', \''.$crow["ChoiceId"].'\')" /></div>';
			echo '<div class="col-8">';
			echo '<label class="d-inline-block" for="'.$cid.'text" >Text:</label>';
			echo '<input type="text" class="form-control" value="'.$crow["Text"].'" id="'.$cid.'text" onchange="editChoice('.$qrow[1].', \''.$crow["ChoiceId"].'\')" /></div>';
			echo '<div class="col-2 text-right">';
			echo '<button class="btn btn-danger" style="margin-top:32px;" onclick="removeChoice('.$qrow[1].', \''.$crow["ChoiceId"].'\')" ';
			echo 'id="q'.$qrow["QuestionNumber"].'c'.$crow["ChoiceId"].'delete" >Remove</button>';
			echo '</div>';

			echo '</div></div>';
			$crow = $choiceStatement->fetch();
		}
		// adds the ability to add a choice
		echo '<div class="list-group-item" id="q'.$qrow["QuestionNumber"].'addwrapper" ><div class="row no-gutters">';
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
		echo '</div>';


		$nextQuestion = $nextQuestion + 1;
		$qrow = $questionStatement->fetch();
	}
?>

<!-- Data and buttons to delete exam and add questions -->
<input type="hidden" id="maxQuestionValue" value="<?php echo $nextQuestion; ?>" />

<button class="btn btn-danger mt-3 mb-5 float-left" onclick="removeExam()">DELETE Exam</button> 

<button class="btn btn-success mt-3 mb-5  float-right" onclick="addQuestion()">Add Question</button>

</div>


</body>
</html>