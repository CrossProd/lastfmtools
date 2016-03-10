<?php

require_once('config/config.php');

class LastFmUserManager
{
	private $lastFmManager;

	function LastFmUserManager($lastFmManager)
	{
		$this->lastFmManager = $lastFmManager;
	}

	function GetTopArtists($user, $period = 'overall')
	{
		$cacheKey = 'user.gettopartists_' . $user . '_' . $period;

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'user' => $user,
				'period' => $period,
				'limit' => '60'
			);

			$result = $this->lastFmManager->PerformQuery('user.gettopartists', $vars);

			if ($result && ($result['status'] == 'ok'))
			{
				$i = 0;

				$artistList = array();

				foreach ($result->topartists->artist as $artist)
				{
					$artistList[$i]['name'] = (string)$artist->name;
//						$artistList[$i]['rank'] = (string)$artist['rank'];
					$artistList[$i]['PC'] = (string)$artist->playcount;
//						$artistList[$i]['mbid'] = (string)$artist->mbid;
//						$artistList[$i]['url'] = (string)$artist->url;
					$artistList[$i]['i1'] = (string)$artist->image[0];
					$artistList[$i]['i2'] = (string)$artist->image[1];
					$artistList[$i]['i3'] = (string)$artist->image[2];
					$artistList[$i]['i4'] = (string)$artist->image[3];

					$i++;
				}

				if (count($artistList) == 0)
				{
					return NULL;
				}

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($artistList), $GLOBALS['cache_user_topartists']);

				return $artistList;
			}
			else
			{
				return NULL;
			}
		}
	}

	function GetTopAlbums($user, $period = 'overall', $limit = NULL)
	{
		$cacheKey = 'user.gettopalbums_' . $user . '_' . $period;

		if ($limit)
		{
			$cacheKey .= '_' . $limit;
		}

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'user' => $user,
				'period' => $period,
				'limit' => '60'
			);

			if ($limit)
			{
				$vars['limit'] = $limit;
			}

			$result = $this->lastFmManager->PerformQuery('user.gettopalbums', $vars);

			if ($result && ($result['status'] == 'ok'))
			{
				$i = 0;

				$albumList = array();

				foreach ($result->topalbums->album as $album)
				{
					$albumList[$i]['name'] = (string)$album->name;
					$albumList[$i]['artist'] = (string)$album->artist->name;
//						$artistList[$i]['rank'] = (string)$artist['rank'];
					$albumList[$i]['PC'] = (string)$album->playcount;
//						$artistList[$i]['mbid'] = (string)$album->mbid;
//						$artistList[$i]['url'] = (string)$artist->url;
					$albumList[$i]['i1'] = (string)$album->image[0];
					$albumList[$i]['i2'] = (string)$album->image[1];
					$albumList[$i]['i3'] = (string)$album->image[2];
					$albumList[$i]['i4'] = (string)$album->image[3];

					$i++;
				}

				if (count($albumList) == 0)
				{
					return NULL;
				}

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($albumList), $GLOBALS['cache_user_topalbums']);

				return $albumList;
			}
			else
			{
				return NULL;
			}
		}
	}

	function GetUserInfo($user)
	{
		$cacheKey = 'user.getinfo' . $user . '_';

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'user' => $user
			);

			$result = $this->lastFmManager->PerformQuery('user.getinfo', $vars);

			if ($result && ($result['status'] == 'ok'))
			{
				$i = 0;

				$data = array();

				$data['username'] = (string)$result->user->name;
				$data['realname'] = (string)$result->user->realname;
				$data['country'] = (string)$result->user->country;
				$data['age'] = (string)$result->user->age;
				$data['gender'] = (string)$result->user->gender;
				$data['playcount'] = (string)$result->user->playcount;
				$data['i1'] = (string)$result->user->image[0];
				$data['i2'] = (string)$result->user->image[1];
				$data['i3'] = (string)$result->user->image[2];
				$data['i4'] = (string)$result->user->image[3];

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($data), $GLOBALS['cache_user_info']);

				return $data;
			}
			else
			{
				return 0;
			}
		}
	}

	function GetWeeklyChartList($user)
	{
		$cacheKey = 'user.getweeklychartlist_' . $user;

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'user' => $user
			);

			$result = $this->lastFmManager->PerformQuery('user.getweeklychartlist', $vars);

			if ($result)
			{
				$i = 0;

				foreach ($result->weeklychartlist->chart as $chart)
				{
					$chartList[$i]['from'] = (string)$chart['from'];
					$chartList[$i]['to'] = (string)$chart['to'];

					$i++;
				}

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($chartList), $GLOBALS['cache_user_weeklychartlist']);

				return $chartList;
			}
			else
			{
				return 0;
			}
		}
	}

	function PollWeeklyArtistChart($user, $from, $to)
	{
		$cacheKey = 'user.getweeklyartistchart_' . $user . '_' . $from . '_' . $to;

		return $this->lastFmManager->lastFmCaching->PollCacheItem($cacheKey);
	}

	function GetWeeklyArtistChart($user, $from, $to)
	{
		$cacheKey = 'user.getweeklyartistchart_' . $user . '_' . $from . '_' . $to;

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'user' => $user,
				'from' => $from,
				'to' => $to
			);

			$result = $this->lastFmManager->PerformQuery('user.getweeklyartistchart', $vars);

			if ($result)
			{
				$i = 0;

				foreach ($result->weeklyartistchart->artist as $artist)
				{
					$artistList[$i]['name'] = (string)$artist->name;
//						$artistList[$i]['rank'] = (string)$artist['rank'];
//						$artistList[$i]['mbid'] = (string)$artist->mbid;
					$artistList[$i]['PC'] = (string)$artist->playcount;
//						$artistList[$i]['url'] = (string)$artist->url;

					$i++;
				}

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($artistList), $GLOBALS['cache_user_weeklyartistchart']);

				return $artistList;
			}
			else
			{
				return 0;
			}
		}
	}

	function GetWeeklyAlbumChart($user, $from, $to)
	{
		$cacheKey = 'user.getweeklyalbumchart_' . $user . '_' . $from . '_' . $to;

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'user' => $user,
				'from' => $from,
				'to' => $to
			);

			$result = $this->lastFmManager->PerformQuery('user.getweeklyalbumchart', $vars);

			if ($result)
			{
				$i = 0;

				foreach ($result->weeklyalbumchart->album as $album)
				{
					$albumList[$i]['artist'] = (string)$album->artist;
					$albumList[$i]['name'] = (string)$album->name;
//						$albumList[$i]['rank'] = (string)$artist['rank'];
					$albumList[$i]['mbid'] = (string)$album->mbid;
					$albumList[$i]['PC'] = (string)$album->playcount;
//						$albumList[$i]['url'] = (string)$artist->url;

					$i++;
				}

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($albumList), $GLOBALS['cache_user_weeklyalbumchart']);

				return $albumList;
			}
			else
			{
				return 0;
			}
		}
	}
}
