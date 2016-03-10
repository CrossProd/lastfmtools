<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

ini_set('include_path', '/home/lastfm/public_html');

require_once('config/config.php');
require_once('code/twitterapi/twitterdb.php');
require_once('code/generators/topalbums_collage.php');
require_once('code/twitterapi/tmhOAuth/tmhOAuth.php');

$twitterDb = new TwitterDb($GLOBALS['caching_db_host'], $GLOBALS['caching_db_name'], $GLOBALS['caching_db_user'], $GLOBALS['caching_db_pass']);

$account = $twitterDb->GetNextAccountToUpload();

if (!$account)
{
	echo "Nothing to do! LATER ALLIGATOR!";

	return;
}

$_GET['username'] = $account->lastfm_username;
$_GET['period'] = $account->selected_collage_period;

$_GET['cols'] = '3';
$_GET['rows'] = '3';

$_GET['border'] = '';

$generator = new TopAlbumsCollage();

try
{
	if ($generator->Run(TRUE))
	{
		$imagePath = $generator->temporaryPath;

		echo $account->token . ", " . $account->secret;

		$tmhOAuth = new tmhOAuth(array(
		  'consumer_key'    => TWITTER_API_KEY,
		  'consumer_secret' => TWITTER_API_SECRET,
		  'user_token'      => $account->token,
		  'user_secret'     => $account->secret
		));

		$text = "";

		switch ($account->selected_collage_period)
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

		echo $text;

		$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update_with_media'),
		 		array(
		   		'media[]'  => "@{$imagePath};type=image/jpeg;filename={albumart.jpg}",
		   		'status'   => $text
		   	),
		   	true,
		  	true
		);

		echo $account->user_id;

		$twitterDb->UserUpdatePeriodicTweetDate($account->user_id);
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
