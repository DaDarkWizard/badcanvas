<?php
	//echo pageHeader("Logging you in....");
	
	include_once __DIR__ . '/vendor/autoload.php';
	//include_once "base.php";

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
		$redirect = "InstructorDashboard.php";
	}

	$_SESSION['login_task'] = $loginTask;
	$_SESSION['redirect'] = $redirect;

	if($loginTask == 'logout')
	{
		session_destroy();
		header("LOCATION:index.html");
		return;
	}

	$oauth_credentials = "client-secret.json";

	if(!file_exists($oauth_credentials))
	{
		echo "No authentication file could be found!";
		return;
	}
	$splitServerUri = explode("badcanvas", $_SERVER["REQUEST_URI"]);
	$redirect_uri = "https://".$_SERVER["HTTP_HOST"].$splitServerUri[0]."badcanvas/login.php";
	$client = new Google\Client();
	$client->setAuthConfig($oauth_credentials);
	$client->setRedirectUri($redirect_uri);
	$client->setScopes('email');

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
		echo "<pre>".var_export($client->getAccessToken())."</pre>";
		$_SESSION['TOKEN_DATA'] = $client->verifyIdToken();

		if(!$_SESSION['TOKEN_DATA'])
		{
			unset($_SESSION['id_token_token']);
			header('Location:login.php');
			return;
		}

		header('Location:'.$redirect);
		//echo "<pre>".var_export($_SESSION['TOKEN_DATA'])."</pre>";
		//echo "<label>Time: ".time()."</label>";
		//echo ("TEST");
		return;
	}

?>

