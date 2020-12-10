<html>
<body>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<script type="text/javascript">
	function editRow(rowId)
	{
		var row = document.getElementById(rowId);

		console.log($("#" + rowId));
		console.log($("#" + rowId)[0].childNodes[0]);

		var idNode = $("#" + rowId)[0].children[0];
		var nameNode = idNode.nextSibling;
		var majorNode = nameNode.nextSibling;
		var buttonNode = majorNode.nextSibling;

		console.log(idNode.innerHTML);
		console.log(nameNode.innerHTML);
		console.log(majorNode.innerHTML);
		console.log(buttonNode.innerHTML);

		//idNode.innerHTML = "<input class='form-control' type='text' value='" + idNode.innerHTML + "'/>";
		nameNode.innerHTML = "<input class='form-control' type='text' value='" + nameNode.innerHTML + "'/>";
		majorNode.innerHTML = "<input class='form-control' type='text' value='" + majorNode.innerHTML + "'/>";
		buttonNode.innerHTML = "<button type='button' class='btn btn-success' onclick='submitChanges(\"" + rowId + "\")'>Update</button>" +
								"<button type='button' class='btn btn-danger' onclick='cancelChanges()'>Cancel</button>";
	}

	function submitChanges(rowId)
	{
		var idNode = $("#" + rowId)[0].children[0];
		var nameNode = idNode.nextSibling;
		var majorNode = nameNode.nextSibling;

		//console.log(idNode.children);

		$.post("studentOperations.php",
			{
				Operation: "edit",
				StudentId: idNode.innerHTML,
				Name: nameNode.children[0].value,
				Major: majorNode.children[0].value
			},
			function(data, status){
				location.reload();
		});
	}

	function cancelChanges()
	{
		location.reload();
	}

	function deleteStudent(rowId)
	{
		


		var idNode = $("#" + rowId)[0].children[0];

		if(!confirm("Are you sure you want to delete " + idNode.innerHTML + "?"))
		{
			console.log("canceled.");
			return;
		}

		$.post("studentOperations.php",
			{
				Operation: "remove",
				StudentId: idNode.innerHTML
			},
			function(data, status){
				location.reload();
		});
	}

	function addStudent(rowId)
	{
		var idNode = $("#" + rowId)[0].children[0];
		var nameNode = idNode.nextSibling;
		var majorNode = nameNode.nextSibling;

		//console.log(idNode.children);

		$.post("studentOperations.php",
			{
				Operation: "add",
				StudentId: idNode.children[0].value,
				Name: nameNode.children[0].value,
				Major: majorNode.children[0].value
			},
			function(data, status){
				location.reload();
		});
	}

</script>

<div class="container">

<?php
	include_once "checklogin.php";
	include_once "InstructorHeader.php";
	session_start();
	$config = parse_ini_file("db.ini");
	

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$email = checklogin("StudentList");

	$verified = verifyProfessor($email);

	echo createInstructorHeader($email, "InstructorDashboard.php");
	
	echo "<table class='table table-striped table-hover'>";
	echo "<tr><td>Email</td><td>Name</td><td>Major</td><td></td></tr>";

	$i = 0;

	foreach($dbh->query("SELECT StudentId, Name, Major FROM Student") as $row)
	{
		echo "<tr id='row".$i."'><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td><button class='btn btn-default' type='button' onclick='editRow(\"row".$i."\")'>Edit</button>";
		echo "<button class='btn btn-danger' type='button' onclick='deleteStudent(\"row".$i."\")'>Delete</button></td></tr>";
		$i = $i + 1;
	}
	echo "<tr id='row".$i."'><td><input class='form-control' type='text' /></td><td><input class='form-control' type='text' /></td><td><input class='form-control' type='text' /></td>";
	echo "<td><button type='button' class='btn btn-default' onclick='addStudent(\"row".$i."\")'>Add</button></td></tr>";

	echo "</table>";
?>
</div>



</body>
</html>
