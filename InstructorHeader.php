<?php
	function createInstructorHeader($email, $link)
	{
		$output = "<div class='container-fluid' style='background-color:LightGray;'>";

                $output = $output."<div class='row'>";
		
		$output = $output."<div class='col-sm-1'><button class='btn btn-light' onclick='window.location.href=\"".$link."\";'>Back</button></div>";
                $output = $output."<div class='col-sm-2'><img src='BadCanvas.png'/></div>";
                $output = $output."<div class='col-sm-5'><h1>Bad Canvas</h1></div>";
                $output = $output."<div class='col-sm-4 text-right'><form method='post' action='login.php'><input type='hidden' name='login_task' value='logout'/><h3 style='display:inline-block;margin-right:20px;'>Welcome, ".$email."!</h3>";
                $output = $output."<button type='submit' class='btn btn-success'>Logout</button></form></div>";
                $output = $output."</div>"; // row
                $output = $output."</div>"; // container

                return $output;
	}
?>
