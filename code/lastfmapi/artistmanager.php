<?php

require_once('config/config.php');

class LastFmArtistManager
{
	private $lastFmManager;

	function LastFmArtistManager($lastFmManager)
	{
		$this->lastFmManager = $lastFmManager;
	}

	function GetTopTags($artist)
	{
		$cacheKey = 'artist.gettoptags_' . $artist;

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'artist' => $artist
			);

			$result = $this->lastFmManager->PerformQuery('artist.gettoptags', $vars);

			if ($result)
			{
				$i = 0;

				foreach ($result->toptags->tag as $tag)
				{
					$topTags[$i]['name'] = (string)$tag->name;
					$topTags[$i]['PC'] = (int)$tag->count;
					//$topTags[$i]['url']  = (string)$tag->url;

					$i++;

					if ($i >= $GLOBALS['max_artist_top_tags'])
					{
						break;
					}
				}

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($topTags), $GLOBALS['cache_artist_toptags']);

				return $topTags;
			}
			else
			{
				return 0;
			}
		}
	}

	function GetSimilar($artist)
	{
		$cacheKey = 'artist.getsimilar_' . $artist;

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'artist' => $artist
			);

			$result = $this->lastFmManager->PerformQuery('artist.getsimilar', $vars);

			if ($result)
			{
				$i = 0;

				foreach ($result->similarartists->artist as $similarArtist)
				{
					$similarList[$i]['name']  = (string)$similarArtist->name;
					$similarList[$i]['mbid']  = (string)$similarArtist->mbid;
					$similarList[$i]['match'] = (string)$similarArtist->match;
					$similarList[$i]['url']   = (string)$similarArtist->url;

					$i++;
				}

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($similarList), $GLOBALS['cache_artist_similar']);

				return $similarList;
			}
			else
			{
				return 0;
			}
		}
	}
}
