<?php

require_once('code/generators/base/imagegenerator.php');
require_once('code/generators/tools/artcache.php');

class TopAlbums3D extends ImageGenerator
{
	protected function GetIdentifier()
	{
		return 'topalbums_3d';
	}

	protected function LoadParameters()
	{
		parent::LoadParameters();

//			// backward compatibality
//			if (isset($_GET['limit']))
//			{
//				$this->parameters['cols'] = 3;
//				$this->parameters['rows'] = ceil($_GET['limit'] / 3);
//			}
//			else
//			{
//				$this->parameters['cols'] = isset($_GET['cols']) ? strtolower($_GET['cols']) : '3';
//				$this->parameters['rows'] = isset($_GET['rows']) ? strtolower($_GET['rows']) : '3';
//			}
//
//			$this->parameters['border'] = isset($_GET['border']) ? strtolower($_GET['border']) : '';
	}

	protected function GetImageType()
	{
		return '.jpg';
	}

	protected function GetFileCacheParameters()
	{
		return array();
//			return array('rows', 'cols', 'border');
	}

	protected function GetImageQuality()
	{
		if ($this->isPriorityUser)
		{
			return 95;
		}
		else
		{
			return 60;
		}
	}

	public function RenderImage($topAlbumsList)
	{
		$usableCount = 0;
		$usableImages = array();

		foreach ($topAlbumsList as $album)
		{
			$url = $album['i3'];
			$name = $album['name'];
			$artist = $album['artist'];

			// prefilter bad apples
			if (strpos($url, 'default_album_') != false)
			{
				continue;
			}

			if ((strpos($url, 'last.fm') == false) && (strpos($url, 'lastfm') == false) && (strpos($url, 'amazon') == false))
			{
				continue;
			}

			if ($this->debugMode)
			{
				echo "$artist - $name ($url)<br />";
			}

			$path = ArtCache::GetAlbumOriginalPath($artist, $name, $url);

			if ($path !== NULL)
			{
				$usableImages[] = $path;
			}
		}

//			echo '<pre>';
//			print_r($usableImages);
//			echo '</pre>';

		$guid = uniqid();

		$tempPath = $GLOBALS['absolute_path'] . 'cache/temp/';

		$configText = '';

		$configPath = $tempPath . $guid . '_config.txt';


		foreach ($usableImages as $path)
		{
			$newPath = $tempPath . basename($path);

//				copy($path, $newPath);

			$configText .= $path . "\n";
		}


		file_put_contents($configPath, $configText);

		$output = array();
		$error;

		exec("/srv/www/vhosts/gammalyrae.com/subdomains/lastfm/httpdocs/executables/3dparser " . $configPath, $output, $error);

//			echo "Error: " . $error;
//			print_r($output);

		$this->imageHandle = imagecreatefromjpeg('/srv/www/vhosts/gammalyrae.com/subdomains/lastfm/httpdocs/cache/temp/test.jpg');

		return true;
	}

}
