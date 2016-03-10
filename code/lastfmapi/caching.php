<?php

class LastFmCachingException extends Exception { }

class LastFmCaching
{
	private $connected = false;

	private $server;
	private $db;
	private $username;
	private $password;

	function LastFmCaching($server, $db, $username, $password)
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
				throw new LastFmCachingException('Unable to connect to the caching database server.');
			}

			if (!mysql_select_db($this->db))
			{
				throw new LastFmCachingException('Unable to select the caching database.');
			}

			$this->connected = true;
		}
	}

	public function SetCacheItem($key, $value, $expiration)
	{
		$value = addslashes($value);
		$key   = addslashes($key);

		$this->DeleteCacheItem($key);

		$query =
			"INSERT INTO query_cache " .
			"	(query_key, query_date, query_expiration, query_value_compressed) " .
			"VALUES " .
			"	('$key', " . strtotime('now') . ", $expiration, COMPRESS('" . $value . "')) ";

		if (!($result = mysql_query($query)))
		{
			throw new LastFmCachingException('Unable to execute SetCacheItem: ' . mysql_error());
		}
	}

	public function GetCacheItem($key)
	{
		$key = addslashes($key);

		$query =
			"SELECT query_date, query_expiration, query_key, UNCOMPRESS(query_value_compressed) AS query_value_uncompressed " .
			"FROM query_cache WHERE query_key = '$key'";

		if ($result = mysql_query($query))
		{
			if ($cacheItem = mysql_fetch_object($result))
			{
				if ((strtotime('now') - $cacheItem->query_date) > ($cacheItem->query_expiration * 60))
				{
					$this->DeleteCacheItem($key);

					return 0;
				}
				else
				{
					$cacheItem->query_key   = stripslashes($cacheItem->query_key);
					//$cacheItem->query_value = stripslashes($cacheItem->query_value_uncompressed);
					$cacheItem->query_value = $cacheItem->query_value_uncompressed;




					return $cacheItem;
				}
			}
		}
		else
		{
			throw new LastFmCachingException('Unable to execute GetCacheItem: ' . mysql_error());
		}

		return 0;
	}

	public function PollCacheItem($key)
	{
		$key = addslashes($key);

		$query =
			"SELECT query_date, query_expiration " .
			"FROM query_cache WHERE query_key = '$key'";

		if ($result = mysql_query($query))
		{
			if ($cacheItem = mysql_fetch_object($result))
			{
				if ((strtotime('now') - $cacheItem->query_date) > ($cacheItem->query_expiration * 60))
				{
					$this->DeleteCacheItem($key);

					return false;
				}
				else
				{
					return true;
				}
			}
		}
		else
		{
			throw new LastFmCachingException('Unable to execute PollCacheItem: ' . mysql_error());
		}

		return false;
	}

	private function DeleteCacheItem($key)
	{
		$query =
			"DELETE FROM query_cache WHERE query_key = '$key'";

		if (!mysql_query($query))
		{
			throw new LastFmCachingException('Unable to execute DeleteCacheItem: ' . mysql_error());
		}
	}

	public function GetAllCacheItems($clearExpired = false)
	{
		$query =
			"SELECT query_date, query_expiration, query_key " .
			"FROM query_cache ";

		if ($result = mysql_query($query))
		{
			$i = 0;

			$cacheItemList = array();

			while ($cacheItem = mysql_fetch_object($result))
			{
				if ($clearExpired)
				{
					if ((strtotime('now') - $cacheItem->query_date) > ($cacheItem->query_expiration * 60))
					{
						$this->DeleteCacheItem($cacheItem->query_key);

						continue;
					}
				}

				$cacheItem->query_key = stripslashes($cacheItem->query_key);

				$cacheItemList[$i++] = $cacheItem;
			}

			return $cacheItemList;
		}

		return 0;
	}

	public function CountAllCacheItemsFromTimestamp($timestamp)
	{
		$query =
			"SELECT COUNT(*) as total " .
			"FROM query_cache " .
			"WHERE query_date > $timestamp ";

		if ($result = mysql_query($query))
		{
			$cacheDetails =  mysql_fetch_object($result);

			return $cacheDetails->total;
		}

		return 0;
	}

	public function DeleteAllCacheItems($timestamp)
	{
		$query =
			"DELETE " .
			"FROM query_cache " .
			"WHERE query_date < $timestamp ";

		mysql_query($query);
	}
}
