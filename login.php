<?php
	//echo pageHeader("Logging you in....");
	
	include_once __DIR__ . '/vendor/autoload.php';
	include_once "base.php";

	session_start();

	// Default redirect.
	$redirect = null;
	$loginTask = null;

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
		$redirect = __DIR__./InstructorDashboard.php;
	}

	$_SESSION['login_task'] = $loginTask;
	$_SESSION['redirect'] = $redirect;

	if($loginTask == 'logout')
	{
		session_destroy();
		header("LOCATION:".$redirect);
		return;
	}

	$oauth_credentials = "client-secret.json";

	if(!file_exists($oauth_credentials))
	{
		echo "No authentication file could be found!";
		return;
	}

	$redirect_uri = __DIR__ . "/login.php";

	$client = new Google\Client();
	$client->setAuthConfig($oauth_credentials);
	$client->setRedirectUri($redirect_uri);
	$client->setScopes('email');

	if (isset($_GET['code'])) {
		$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

		// store in the session also
		$_SESSION['id_token_token'] = $token;
		$client->setAccessToken($token);

		// redirect back to the example
		header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
		return;
	}

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

	if ($client->getAccessToken()) {
		$_SESSION['TOKEN_DATA'] = $client->verifyIdToken();
		header('Location:'.$redirect);
		return;
	}

?>