<?php

    require_once 'vendor/autoload.php';

    $client = new Google\Client();
    $client->setAuthConfig('client-secret.json');

    $redirect_uri = 'https://classdb.it.mtu.edu/~dtmasker/badcanvas/test.php';
    $client->setRedirectUri($redirect_uri);

    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    }

    var_dump($token);
?>