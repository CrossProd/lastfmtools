<?php

class TwitterDbException extends Exception { }

class TwitterDb
{
	private $connected = false;

	private $server;
	private $db;
	private $username;
	private $password;

	function TwitterDb($server, $db, $username, $password)
	{
		$this->server = $server;
		$this->db = $db;
		$this->username = $username;
		$this->password = $password;

		$this->Connect();
	}

	private function Connect()
	{
		if (!$this->connected)
		{
			if (!mysql_connect($this->server, $this->username, $this->password))
			{
				throw new TwitterDbException('Unable to connect to the twitter database server.');
			}

			if (!mysql_select_db($this->db))
			{
				throw new TwitterDbException('Unable to select the twitter database.');
			}

			$this->connected = true;
		}
	}

	public function InsertOrUpdateTwitterAccount($token, $secret, $userId, $screenName, $lastFmUsername)
	{
		$token = addslashes($token);
		$secret = addslashes($secret);
		$userId = addslashes($userId);
		$screenName = addslashes($screenName);
		$lastFmUsername = addslashes($lastFmUsername);

		$query =
			"INSERT INTO twitter_user " .
			"	(token, secret, user_id, screen_name, lastfm_username, record_created, record_updated) " .
			"VALUES " .
			"	('$token', '$secret', '$userId', '$screenName', '$lastFmUsername', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()) " .
			"ON DUPLICATE KEY UPDATE secret = '$secret', " .
			"						 token = '$token', " .
			" 						 screen_name = '$screenName', " .
			"						 lastfm_username = '$lastFmUsername', " .
			"						 record_updated = UNIX_TIMESTAMP(); ";

		if (!($result = mysql_query($query)))
		{
			throw new TwitterDbException('Unable to execute InsertOrUpdateTwitterAccount: ' . mysql_error());
		}
	}

	public function UpdateUserPeriodicPreferences($userId, $tweetPeriod, $collagePeriod)
	{
		$uploadInterval = 0;

		switch ($tweetPeriod)
		{
			case 'week':
				$uploadInterval = 7 * 24 * 3600;
				break;
			case '2week':
				$uploadInterval = 14 * 24 * 3600;
				break;
			case 'month':
				$uploadInterval = 28 * 24 * 3600;
				break;
		}

		$enabled = $uploadInterval > 0 ? "1" : "0";

		$tweetPeriod = addslashes($tweetPeriod);
		$collagePeriod = addslashes($collagePeriod);

		$query =
			"UPDATE twitter_user " .
			"SET selected_tweet_period = '$tweetPeriod', " .
			"	 selected_tweet_interval = $uploadInterval, " .
			"	 periodic_upload_enabled = $enabled, " .
			"	 selected_collage_period = '$collagePeriod', " .
			"	 record_updated = UNIX_TIMESTAMP() " .
			"WHERE user_id = '$userId'; ";

		if ($result = mysql_query($query))
		{
		}
		else
		{
			throw new TwitterDbException('Unable to execute UpdateUserPeriodicPreferences: ' . mysql_error());
		}
	}

	public function UserCanDoDirectTweet($userId)
	{
		$userId = addslashes($userId);

		$query =
			"SELECT 1 " .
			"FROM twitter_user " .
			"WHERE user_id = '$userId' " .
			"AND (last_direct_tweet IS NULL OR UNIX_TIMESTAMP() - last_direct_tweet > 60); ";

		if ($result = mysql_query($query))
		{
			if (mysql_fetch_object($result))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			throw new TwitterDbException('Unable to execute UserCanDoDirectTweet: ' . mysql_error());
		}
	}

	public function UserUpdateDirectTweetDate($userId)
	{
		$userId = addslashes($userId);

		$query =
			"UPDATE twitter_user " .
			"SET last_direct_tweet = UNIX_TIMESTAMP(), " .
			"	 nr_of_direct_tweets = nr_of_direct_tweets + 1, " .
			"	 record_updated = UNIX_TIMESTAMP() " .
			"WHERE user_id = '$userId'; ";

		if ($result = mysql_query($query))
		{
		}
		else
		{
			throw new TwitterDbException('Unable to execute UserUpdateDirectTweetDate: ' . mysql_error());
		}
	}


	public function UserUpdatePeriodicTweetDate($userId)
	{
		$userId = addslashes($userId);

		$query =
			"UPDATE twitter_user " .
			"SET last_periodic_tweet = UNIX_TIMESTAMP(), " .
			"	 nr_of_periodic_tweets = nr_of_periodic_tweets + 1, " .
			"	 record_updated = UNIX_TIMESTAMP() " .
			"WHERE user_id = '$userId'; ";

		if ($result = mysql_query($query))
		{
		}
		else
		{
			throw new TwitterDbException('Unable to execute UserUpdateDirectTweetDate: ' . mysql_error());
		}
	}

	public function GetUserPeriodicPreferences($userId)
	{
		// first try to get one that has never done an UPLOAD
		$query =
			"SELECT selected_tweet_period, selected_collage_period " .
			"FROM twitter_user " .
			"WHERE user_id = $userId; ";

		if ($result = mysql_query($query))
		{
			if ($account = mysql_fetch_object($result))
			{
				return $account;
			}
		}
		else
		{
			throw new TwitterDbException('Unable to execute GetUserPeriodicPreferences: ' . mysql_error());
		}
	}

	public function GetNextAccountToUpload()
	{
		// first try to get one that has never done an UPLOAD
		$query =
			"SELECT * " .
			"FROM twitter_user " .
			"WHERE last_periodic_tweet IS NULL " .
			"AND periodic_upload_enabled = 1 " .
			"AND valid_account = 1 " .
			"LIMIT 1; ";

		if ($result = mysql_query($query))
		{
			if ($account = mysql_fetch_object($result))
			{
				return $account;
			}
		}
		else
		{
			throw new TwitterDbException('Unable to execute GetNextAccountToUpload: ' . mysql_error());
		}

		// otherwise we just get the oldest
		$query =
			"SELECT * " .
			"FROM twitter_user " .
			"WHERE last_periodic_tweet IS NOT NULL " .
			"AND periodic_upload_enabled = 1 " .
			"AND valid_account = 1 " .
			"AND (last_periodic_tweet + selected_tweet_interval - 86400) < UNIX_TIMESTAMP() " .
			"ORDER BY last_periodic_tweet ASC " .
			"LIMIT 1; ";

		if ($result = mysql_query($query))
		{
			if ($account = mysql_fetch_object($result))
			{
				return $account;
			}
		}
		else
		{
			throw new TwitterDbException('Unable to execute GetNextAccountToUpload: ' . mysql_error());
		}

		return NULL;
	}
}
