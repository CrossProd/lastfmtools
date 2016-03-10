<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

session_start();

require_once('config/config.php');
require_once('code/twitterapi/twitteroauth/twitteroauth.php');
require_once('code/twitterapi/twitterdb.php');

if (isset($_REQUEST['denied']))
{
	header('Location: ./twitterclearsession.php');

	return;
}

if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token'])
{
	$_SESSION['oauth_status'] = 'oldtoken';

	header('Location: ./twitterclearsession.php');

	return;
}

$connection = new TwitterOAuth(TWITTER_API_KEY, TWITTER_API_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

$_SESSION['access_token'] = $access_token;

$twitterToken = $_SESSION['access_token']['oauth_token'];
$twitterSecret = $_SESSION['access_token']['oauth_token_secret'];
$twitterScreenName = $_SESSION['access_token']['screen_name'];
$twitterUserId = $_SESSION['access_token']['user_id'];

$lastfmUsername = $_COOKIE['username'];

/* Save the access tokens. Normally these would be saved in a database for future use. */
$twitterDb = new TwitterDb(LASTFM_DB_HOST, LASTFM_DB_NAME, LASTFM_DB_USER, LASTFM_DB_PASSWORD);

$twitterDb->InsertOrUpdateTwitterAccount($twitterToken, $twitterSecret, $twitterUserId, $twitterScreenName, $lastfmUsername);

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->http_code)
{
	/* The user has been verified and the access tokens can be saved for future use */
	$_SESSION['status'] = 'verified';

	header('Location: index.php?page=twitter');
}
else
{
	echo "FAILED!";

	/* Save HTTP status for error dialog on connnect page.*/
	// header('Location: ./clearsessions.php');
}
