<?php

require_once('config/config.php');

class LastFmAlbumManager
{
	private $lastFmManager;

	function LastFmAlbumManager($lastFmManager)
	{
		$this->lastFmManager = $lastFmManager;
	}

	function GetInfo($artist, $album)
	{
		$cacheKey = 'album.getinfo_' . $artist . '_' . $album;

		if ($cacheItem = $this->lastFmManager->lastFmCaching->GetCacheItem($cacheKey))
		{
			return unserialize($cacheItem->query_value);
		}
		else
		{
			$vars = array
			(
				'album' => $album,
				'artist' => $artist
			);

			$result = $this->lastFmManager->PerformQuery('album.getinfo', $vars);

			if ($result)
			{
			  $info['id'] = (string)$result->album->id;
			  $info['mbid'] = (string)$result->album->mbid;
			  $info['rd'] = (string)$result->album->releasedate;

				$this->lastFmManager->lastFmCaching->SetCacheItem($cacheKey, serialize($info), $GLOBALS['cache_album_info']);

				return info;
			}
			else
			{
			  return 0;
			}
		}
	}
}
