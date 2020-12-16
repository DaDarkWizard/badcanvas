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
	//Setup Login/page
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

	//print header
	echo createStudentHeader($email, "StudentDashboard");

	if(!isset($_SESSION["Exam"]))
	{
		if(!isset($_POST["Exam"]))
		{
			header("LOCATION:StudentDashboard");
			return;
		}
		else
		{
			$_SESSION["Exam"] = $_POST["Exam"];
		}
	}

	//Joins Exam and TakenExams with Questions to get total questions
	$examName = $_SESSION["Exam"];
	$statement = $dbh->prepare("SELECT a.ExamName as ExamName, a.TotalPoints as TotalPoints, a.TsRelease as TsRelease, a.TsClose as TsClose,  b.TotalQuestions as TotalQuestions,
								a.TotalScore as TotalScore, a.Complete as Complete
								from (Select c.ExamName as ExamName, c.TotalPoints as TotalPoints, c.TsRelease as TsRelease, 
											c.TsClose as TsClose, d.TotalScore as TotalScore, d.Complete as Complete
									from Exam c
									left join TakenExams d
									on c.ExamName = d.ExamName
									where StudentId=:StudentId
								) a
								Left join (Select ExamName, Count(*) as TotalQuestions from Question where ExamName=:ExamName) as b
								on a.ExamName = b.ExamName
								where a.ExamName=:ExamName;");
	$statement->execute(array(":ExamName" => $_SESSION["Exam"],
							":StudentId" => $email));
	
	$results = $statement->fetch();
	
	//sets default timezone
	date_default_timezone_set("UTC");

	//sets all the selected values above to vars
	$examName = $results[0];
	$tsRelease = $results[2];
	$tsClose = $results[3];
	$totalPoints = $results[1];
	$totalQuestions = $results[4];
	$totalScore = $results["TotalScore"];
	$complete = $results["Complete"];
?>

<script type="text/javascript">
	
	//Takes a mySQL date and makes the date readable
	function parseDateTime(timeString)
	{
		var t = timeString.split(/[- :]/);

		// Apply each element to the Date function
		var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
		d = "" + d.getFullYear() + "-" + 
				("00" + (d.getMonth() + 1)).substr(-2, 2) +
				"-" + ("00" + d.getDate()).substr(-2, 2) + "T"
				+ ("00" + d.getHours()).substr(-2, 2) + ":"
				+ ("00" + d.getMinutes()).substr(-2, 2);
		return d;
	}

	//This fucntion submits the exam to the database
	function submitExam()
	{
		$.post("studentAnswerOperations.php",
		{
			Operation: "submit",
			ExamName: "<?php echo $_SESSION["Exam"]; ?>",
			StudentId: "<?php echo $email; ?>"
		},
		function (data, status){
			location.reload();
		});
	}

	//This function looks for a mySQL data and converts it, this function runs after the user loads the page
	function setupPage()
	{
		var mySqlCloseDate = "<?php echo $tsClose; ?>";

		var t = mySqlCloseDate.split(/[- :]/);

		// Apply each element to the Date function
		var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
		var hours = (d.getHours() > 12 ? d.getHours() - 12 : d.getHours());
		d = "<strong>Due</strong> " + d.toLocaleString('default', {month: 'short'}) + " " + d.getDate() +
		" at " + hours + ":" + ("00" + d.getMinutes()).substr(-2, 2) + (d.getHours() > 12 ? "pm" : "am");

		$("#displayTsClose")[0].innerHTML = d;

		$('[data-toggle="tooltip"]').tooltip({placement: 'left',trigger: 'manual'});  
		$('[data-toggle="tooltip"]').tooltip("show"); 
		

	}
	$(document).ready(function() {setupPage();});

	//This function handels choicing and a choice for a question
	function setAnswerChoice(questionNumber, choiceId)
	{
		if(choiceId == $("#q" + questionNumber +"storedAnswer")[0].value)
		{
			return;
		}

		$.post("studentAnswerOperations.php",
		{
			Operation: "edit",
			ExamName: "<?php echo $_SESSION["Exam"];?>",
			QuestionNumber: questionNumber,
			ChoiceId: choiceId,
			StudentId: "<?php echo $email; ?>"
		},
		function(data, status)
		{
			if(data.trim() != "1")
			{
				console.log(data);
				location.reload();
			}

			
		});
	}

	
</script>

<div class="container-flex" style="margin: 0px 50px 0px 150px;">



<!-- Header for taking an exam -->

<div class="row">

	<div class="col-10">

	<h2><strong><?php echo $examName; ?></strong></h2>
	<hr/>
	<div class="d-inline-flex">
	  <div class="ml-2" id="displayTsClose"></div>
	  <div class="ml-3"><strong>Points</strong> <?php echo $totalPoints;?></div>
	  <div class="ml-3"><strong>Questions</strong> <?php echo $totalQuestions;?></div>
	</div>
	<hr/>

<?php

	//left joins QuestionAnsswer and Question to get choices and correct anwsers 
	$questionStatement = $dbh->prepare("SELECT Question.ExamName as ExamName, Question.QuestionNumber as QuestionNumber, Text, Points, CorrectChoice, Point, ChosenAnswer
										from Question
										left join QuestionAnswer
										on Question.ExamName = QuestionAnswer.ExamName and Question.QuestionNumber = QuestionAnswer.QuestionNumber
										where Question.ExamName=:ExamName and QuestionAnswer.StudentId=:StudentId
										order by QuestionNumber;");
	$questionStatement->execute(array(":ExamName" => $_SESSION["Exam"],
									":StudentId" => $email));
	
	//Prints the Quiz to either be taken or to show the taken exam's anwsers
	$qrow = $questionStatement->fetch();
	while($qrow != null)
	{
		//Start of each question
		echo '<div class="border border-bottom-0 w-100" style="margin-top:15px;padding:12px 6px 12px 6px;background-color:rgb(245,245,245);">';
		echo '<h5 class="mr-3 d-inline-block">Question '.$qrow[1].'</h5>';
		echo '<span class="float-right" >';

		if($complete)//if the user has completed the exam show the points scored
		{
			echo $qrow["Point"].' / '.$qrow["Points"];
		}
		else//if the user has not completed the exam shows the possible points
		{
			echo $qrow[3];
		}
		

		echo ' Points</span>';
		echo '</div>';

		//The question text
		echo '<div class="border w-100" >';
		echo '<p class="p-3 mb-1" >'.$qrow[2].'</p>';

		//Start of each choice
		echo '<div class="list-group list-group-flush ml-3 mr-3 mb-3">';

		//gets all the choices in order
		$choiceStatement = $dbh->prepare("SELECT ExamName, QuestionNumber, ChoiceId, Text from Choice where ExamName=:ExamName and QuestionNumber=:QuestionNumber order by ChoiceId");
		$choiceStatement->execute(array(":ExamName" => $_SESSION["Exam"], ":QuestionNumber" => $qrow["QuestionNumber"]));
		$crow = $choiceStatement->fetch();

		//Prints all the choices
		while($crow != null)
		{
			//Choice ID Var
			$cid = 'q'.$qrow["QuestionNumber"].'c'.$crow["ChoiceId"];
			echo '<div class="list-group-item" >';

			//If the user has completed the exam start the spans for tool tip grading arrows
			if($complete)
			{
				echo "<span style='margin:0px 10px 0px -10px;' ";
				if($crow["ChoiceId"] == $qrow["CorrectChoice"] && $qrow["CorrectChoice"] == $qrow["ChosenAnswer"])
				{
					echo 'data-toggle="tooltip" data-placement="left" title="Correct!" id="allTooltips" tabindex="0" ';
				}
				else if ($crow["ChoiceId"] == $qrow["CorrectChoice"] && $qrow["CorrectChoice"] != $qrow["ChosenAnswer"])
				{
					echo 'data-toggle="tooltip" data-placement="left" title="Correct Answer" id="allTooltips" tabindex="0" ';
				}
				else if ($crow["ChoiceId"] == $qrow["ChosenAnswer"] && $qrow["CorrectChoice"] != $qrow["ChosenAnswer"])
				{
					echo 'data-toggle="tooltip" data-placement="left" title="Incorrect" id="allTooltips" tabindex="0" ';
				}

				echo ">";
			}
			echo '<input type="hidden" value="'.$qrow["ChosenAnswer"].'" id="q'.$qrow["QuestionNumber"].'storedAnswer" />';
			
			//The radio buttons for choices
			echo '<input type="radio" ';
			if(!$complete) //If the user has not completed the exam let them choice a choice
			{
				echo 'class="form-check-input" ';
			}
			echo ' ';
			echo 'name="radio'.$qrow["QuestionNumber"].'" id="q'.$qrow["QuestionNumber"].'c'.$crow["ChoiceId"].'radio" ';

			if($crow["ChoiceId"] == $qrow["ChosenAnswer"]) //if the user had previously choosen this answer mark it
			{
				echo "checked ";
			}

			if(!$complete) //If the user is till working send the choice to the database
			{
				echo "onclick=\"setAnswerChoice(".$qrow[1].", '".$crow["ChoiceId"]."')\" ";
			}
			
			if($complete) //if the user has completed the exam don't let them choice anymore
			{
				echo ' disabled ';
			}

			echo '/>';
			
			//the end of the span if completed
			if($complete)
			{
				echo "</span>";
			}

			//ChoiceId and text
			echo '<strong>'.$crow["ChoiceId"].'</strong> '.$crow["Text"];

			echo '</div>';
			$crow = $choiceStatement->fetch();
		}
		echo '</div></div>';
		$qrow = $questionStatement->fetch();
		
	}

	
	//If still working add the submit button
	if(!$complete)
	{
		echo '<button type="button" class="btn btn-success float-right mt-2 mb-5" onclick="submitExam()">Submit</button>';
		echo '</div>';
	}
	else //show the score for the exam if they are completed
	{
		echo '</div>';
		echo '<div class="col-2">';
		echo '<h4>Submission Details:</h4>';
		echo '<hr/>';
		echo '<h5 class="d-inline-block">Score:</h5><div class="float-right">';
		echo $totalScore;
		echo ' out of ';
		echo $totalPoints;
		echo '</div><hr/></div>';
	}
?>


</div>

</body>
</html>
