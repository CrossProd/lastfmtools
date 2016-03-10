<?php

require_once('../config/config.php');
require_once('../config/priorityusers.php');

$page = 'home';

if (isset($_GET['page']))
{
	$page =  $_GET['page'];
}

if (isset($_COOKIE['username']))
{
	$username =  $_COOKIE['username'];
}

if ($page == 'twitter')
{
	require_once(ABSOLUTE_PATH . 'code/twitterapi/twitterdb.php');
}

switch ($page)
{
	case 'home':
	{
		$selectedSubMenu = 'welcome';
		break;
	}
	case 'widgets':
	{
		$selectedSubMenu = 'albumCollage';
		break;
	}
	case 'app':
	{
		$selectedSubMenu = 'homeWelcome';
		break;
	}
	case 'twitter':
	{
		session_start();

		$selectedSubMenu = 'homeWelcome';

		$twitterIsLoggedIn = false;

		if (isset($_SESSION['access_token']) && isset($_SESSION['access_token']['oauth_token']) && isset($_SESSION['access_token']['oauth_token_secret']))
		{
			$twitterDb = new TwitterDb(LASTFM_DB_HOST, LASTFM_DB_NAME, LASTFM_DB_USER, LASTFM_DB_PASSWORD);

			$account = $twitterDb->GetUserPeriodicPreferences($_SESSION['access_token']['user_id']);

			if ($account)
			{
				$twitterSelectedTweetPeriod = $account->selected_tweet_period;
				$twitterSelectedCollagePeriod = $account->selected_collage_period;
			}

			$twitterScreenName = $_SESSION['access_token']['screen_name'];

			$twitterIsLoggedIn = true;
		}

		break;
	}
}

if (isset($_GET['sub']))
{
	$selectedSubMenu =  $_GET['sub'];
}

include "template.html";
