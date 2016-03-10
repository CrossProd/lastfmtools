<?php

session_start();

require_once('config/config.php');
require_once('code/twitterapi/twitteroauth/twitteroauth.php');

$connection = new TwitterOAuth(TWITTER_API_KEY, TWITTER_API_SECRET);

$request_token = $connection->getRequestToken(TWITTER_API_CALLBACK);

$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

switch ($connection->http_code)
{
	case 200:
	{
		$url = $connection->getAuthorizeURL($_SESSION['oauth_token']);

		header('Location: ' . $url);

		break;
	}

	default:
	{
		echo 'Could not connect to Twitter. Refresh the page or try again later.';

		break;
	}
}
