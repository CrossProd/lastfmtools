<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret']))
{
	echo "No session.";
	//header('Location: index.php?page=twitter');

	exit;
}

if (empty($_COOKIE['username']))
{
	echo "No username.";
//		header('Location: index.php?page=twitter');

	exit;
}

$twitterToken = $_SESSION['access_token']['oauth_token'];
$twitterSecret = $_SESSION['access_token']['oauth_token_secret'];
$twitterScreenName = $_SESSION['access_token']['screen_name'];
$twitterUserId = $_SESSION['access_token']['user_id'];

$lastfmUsername = $_COOKIE['username'];
$collagePeriod = $_REQUEST['collagePeriod'];

require_once('config/config.php');
require_once('code/generators/topalbums_collage.php');
require_once('code/twitterapi/tmhOAuth/tmhOAuth.php');
require_once('code/twitterapi/twitterdb.php');

$twitterDb = new TwitterDb(LASTFM_DB_HOST, LASTFM_DB_NAME, LASTFM_DB_USER, LASTFM_DB_PASSWORD);

if (!$twitterDb->UserCanDoDirectTweet($twitterUserId))
{
	echo "Only one tweet per minute is allowed.";

	return;
}

$username =  $_COOKIE['username'];


$_GET['username'] = $lastfmUsername;
$_GET['period'] = $collagePeriod;

$_GET['cols'] = '3';
$_GET['rows'] = '3';

$_GET['border'] = '';

$generator = new TopAlbumsCollage();

try
{
	if ($generator->Run(TRUE))
	{
		$imagePath = $generator->temporaryPath;

		$text = "";

		switch ($collagePeriod)
		{
			case "7day":
				$text = "These are my favorite albums of the last seven days:";
				break;

			case "1month":
				$text = "These are my favorite albums of the last month:";
				break;

			case "3month":
				$text = "These are my favorite albums of the last three months:";
				break;

			case "6month":
				$text = "These are my favorite albums of the last six months:";
				break;

			case "12month":
				$text = "These are my favorite albums of the last twelve months:";
				break;

			case "overall":
				$text = "These are my favorite albums of all time:";
				break;
		}

		$tmhOAuth = new tmhOAuth(array(
		  'consumer_key'    => TWITTER_API_KEY,
		  'consumer_secret' => TWITTER_API_SECRET,
		  'user_token'      => $_SESSION['access_token']['oauth_token'],
		  'user_secret'     => $_SESSION['access_token']['oauth_token_secret']
		));

		$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update_with_media'),
				array(
				'media[]'  => "@{$imagePath};type=image/jpeg;filename={albumart.jpg}",
				'status'   => $text
			),
			true,
			true
		);

		$response = $tmhOAuth->response['response'];

		$twitterDb->UserUpdateDirectTweetDate($twitterUserId);

		echo "Done! The collage has been tweeted!";
	}
}
catch (LastFmCachingException $e)
{
	echo 'Caught last.fm caching exception: ',  $e->getMessage(), "\n";
}
catch (LastFmSocketException $e)
{
	echo 'Caught last.fm socket exception: ',  $e->getMessage(), "\n";
}
catch (exception $e)
{
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
