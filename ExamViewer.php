<?php
	session_start();
	$config = parse_ini_file("db.ini");
	$professor = parse_ini_file("website.ini")['professor'];

	$dbh = new PDO($config['dsn'], $config['username'],$config['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if(isset($_SESSSION["TOKEN_DATA"]))
	{
	
	}

	$_SESSION["redirect"];
	$_SESSION["login_task"];

?>