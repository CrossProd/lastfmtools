<?php

session_start();

if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret']))
{
	echo "No session.";

	exit;
}

if (empty($_COOKIE['username']))
{
	echo "No username.";

	exit;
}

if (empty($_GET['uploadPeriod']) || empty($_GET['collagePeriod']))
{
	echo "Missing parameters.";

	exit;
}

$twitterToken = $_SESSION['access_token']['oauth_token'];
$twitterSecret = $_SESSION['access_token']['oauth_token_secret'];
$twitterScreenName = $_SESSION['access_token']['screen_name'];
$twitterUserId = $_SESSION['access_token']['user_id'];

$lastfmUsername = $_COOKIE['username'];

$uploadPeriod = $_GET['uploadPeriod'];
$collagePeriod = $_GET['collagePeriod'];

require_once('config/config.php');
require_once('code/generators/topalbums_collage.php');
require_once('code/lastfmapi/manager.php');
require_once('code/twitterapi/twitterdb.php');

$twitterDb = new TwitterDb(LASTFM_DB_HOST, LASTFM_DB_NAME, LASTFM_DB_USER, LASTFM_DB_PASSWORD);

$twitterDb->UpdateUserPeriodicPreferences($twitterUserId, $uploadPeriod, $collagePeriod);

echo "Your preferences have been updated.";
