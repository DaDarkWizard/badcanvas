<?php

	// Get google libraries.
	include_once __DIR__ . '/vendor/autoload.php';

	// Begin the session
	session_start();

	// Default redirect.
	$redirect = null;
	$loginTask = null;

	// Sets up the login task.
	if(isset($_POST['login_task']))
	{
		$loginTask = $_POST['login_task'];
	}
	else if (isset($_SESSION['login_task']))
	{
		$loginTask = $_SESSION['login_task'];
	}
	else 
	{
		$loginTask = 'logout';
	}

	// Sets up the redirect.
	if(isset($_POST['redirect']))
	{
		$redirect = $_POST['redirect'];
	}
	else if(isset($_SESSION['redirect']))
	{
		$redirect = $_SESSION['redirect'];
	}
	else
	{
		$redirect = "InstructorDashboard.php";
	}

	$_SESSION['login_task'] = $loginTask;
	$_SESSION['redirect'] = $redirect;
	
	// Logging out is super easy.
	if($loginTask == 'logout')
	{
		session_destroy();
		header("LOCATION:index.html");
		return;
	}

	// Get our client secret.
	$oauth_credentials = "client-secret.json";

	// This shouldn't happen.
	if(!file_exists($oauth_credentials))
	{
		echo "No authentication file could be found!";
		return;
	}

	// Setup our redirect uri to work on any url as long as it ends with the badcanvas directory.
	$splitServerUri = explode("badcanvas", $_SERVER["REQUEST_URI"]);
	$redirect_uri = "https://".$_SERVER["HTTP_HOST"].$splitServerUri[0]."badcanvas/login.php";

	// Create the OAuth client.
	$client = new Google\Client();
	$client->setAuthConfig($oauth_credentials);
	$client->setRedirectUri($redirect_uri);
	$client->setScopes('email');

	// We've been sent back from google.
	if (isset($_GET['code'])) {
		$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

		// store in the session also
		$_SESSION['id_token_token'] = $token;
		var_dump($token);
		echo("hi<br/>");
		$client->setAccessToken($token);
		echo("hi<br/>");
		// redirect back to the example
		header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
		return;
	}

	// We need to get a token
	if (
		!empty($_SESSION['id_token_token'])
		&& isset($_SESSION['id_token_token']['id_token'])
	) 
	{
		$client->setAccessToken($_SESSION['id_token_token']);
	} 
	else 
	{
		$authUrl = $client->createAuthUrl();
		header('Location: '.$authUrl);
		return;
	}

	// Get the token
	if ($client->getAccessToken()) {
		$_SESSION['TOKEN_DATA'] = $client->verifyIdToken();

		if(!$_SESSION['TOKEN_DATA'])
		{
			unset($_SESSION['id_token_token']);
			header('Location:login.php');
			return;
		}

		header('Location:'.$redirect);
		return;
	}

?>

