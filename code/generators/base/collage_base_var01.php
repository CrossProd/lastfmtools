<?php

require_once('code/generators/base/imagegenerator.php');
require_once('code/generators/tools/artcache.php');

abstract class CollageBaseVar01 extends ImageGenerator
{
	private $type;

	private $usableImages = array();
	private $usableItems = array();

	private $nrOfRows = 0;
	private $nrOfCols = 0;
	private $artSize = 0;

	private $receivedShowPC;

	public function __construct($type, $serverSideRequest = FALSE)
	{
		parent::__construct($serverSideRequest);

		$this->type = $type;
	}

	protected function LoadParameters()
	{
		$this->parameters['border'] = isset($_GET['border']) ? strtolower($_GET['border']) : '';
		$this->parameters['showPC'] = isset($_GET['showPC']) ? strtolower($_GET['showPC']) : 'no';

		$this->receivedShowPC = isset($_GET['showPC']);

		return TRUE;
	}

	protected function GetClientImageHeight()
	{
		return 300;
	}

	protected function GetImageType()
	{
		return '.jpg';
	}

	protected function GetFileCacheParameters()
	{
		if ($this->receivedShowPC)
		{
			return array('border', 'showPC');
		}
		else
		{
			return array('border');
		}
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

	protected function ProcessData()
	{
		$usableCount = 0;

		$artLimit = 17;

		foreach ($this->data as $item)
		{
			if ($this->type == 'album')
			{
				$url = $item['i4'];
				$name = $item['name'];
				$artist = $item['artist'];

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

				if ($usableCount >= 13)
				{
					$artImage = ArtCache::GetAlbum50x50($artist, $name, $url, false);
				}
				else if ($usableCount >= 4)
				{
					$artImage = ArtCache::GetAlbum67x67($artist, $name, $url, false);
				}
				else
				{
					$artImage = ArtCache::GetAlbum100x100($artist, $name, $url, false);
				}
			}
			else if ($this->type == 'artist')
			{
				$url = $item['i4'];
				$name = $item['name'];

				// prefilter bad apples
				if ($url == '')
				{
					continue;
				}

				if ($usableCount >= 13)
				{
					$artImage = ArtCache::GetArtist50x50($name, $url, true);
				}
				else if ($usableCount >= 4)
				{
					$artImage = ArtCache::GetArtist67x67($name, $url, true);
				}
				else
				{
					$artImage = ArtCache::GetArtist100x100($name, $url, true);
				}
			}

			// if valid image, then add it to list and check if we reached the set cover limit
			if ($artImage)
			{
				$this->usableImages[] = $artImage;
				$this->usableItems[] = $item;

				if (++$usableCount >= $artLimit)
				{
					break;
				}
			}
		}

		return TRUE;
	}

	public function RenderImage()
	{
		$usableCount = 0;

		$playCountBackgroundColor = imagecolorallocatealpha($this->imageHandle, 0, 0, 0, 50);
		$playCountTextColor = imagecolorallocate($this->imageHandle, 255, 255, 255);
		$playCountFont = $this->GetFontPath('lucida');

		// 3 x 3 smaller in top right
		imagecopy($this->imageHandle, $this->usableImages[4], 100,   0 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[5], 167,   0 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[6], 234,   0 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[7], 100, 67 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[8], 167, 67 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[9], 234, 67 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[10], 100, 134 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[11], 167, 134 + $this->headerHeight, 0, 0, 67, 67);
		imagecopy($this->imageHandle, $this->usableImages[12], 234, 134 + $this->headerHeight, 0, 0, 67, 67);

		// 2 x 2 smallest in the bottom left
		imagecopy($this->imageHandle, $this->usableImages[13],   0, 200 + $this->headerHeight, 0, 0, 50, 50);
		imagecopy($this->imageHandle, $this->usableImages[14],  50, 200 + $this->headerHeight, 0, 0, 50, 50);
		imagecopy($this->imageHandle, $this->usableImages[15],   0, 250 + $this->headerHeight, 0, 0, 50, 50);
		imagecopy($this->imageHandle, $this->usableImages[16],  50, 250 + $this->headerHeight, 0, 0, 50, 50);

		// four big images
		imagecopy($this->imageHandle, $this->usableImages[0],   0,   0 + $this->headerHeight, 0, 0, 100, 100);
		imagecopy($this->imageHandle, $this->usableImages[1],   0, 100 + $this->headerHeight, 0, 0, 100, 100);
		imagecopy($this->imageHandle, $this->usableImages[2], 100, 200 + $this->headerHeight, 0, 0, 100, 100);
		imagecopy($this->imageHandle, $this->usableImages[3], 200, 200 + $this->headerHeight, 0, 0, 100, 100);

		$this->RenderBorder();

		// destroy all images
		for ($i = 0; $i < count($this->usableImages); $i++)
		{
			imagedestroy($this->usableImages[$i]);
		}

		return TRUE;
	}

	private function RenderBorder()
	{
		// add borders if necessary
		if ($this->parameters['border'] != '' && $this->parameters['border'] != 'none')
		{
			$borderColor = $this->GetColorFromName($this->parameters['border']);

			imageline($this->imageHandle,   0,   0 + $this->headerHeight, 299,   0 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle,   0, 299 + $this->headerHeight, 299, 299 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle,   0,   0 + $this->headerHeight,   0, 299 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle, 299,   0 + $this->headerHeight, 299, 299 + $this->headerHeight, $borderColor);

			imageline($this->imageHandle, 100,   0 + $this->headerHeight, 100, 299 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle,   0, 200 + $this->headerHeight, 299, 200 + $this->headerHeight, $borderColor);

			imageline($this->imageHandle,   0, 100 + $this->headerHeight, 100, 100 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle, 200, 200 + $this->headerHeight, 200, 299 + $this->headerHeight, $borderColor);

			imageline($this->imageHandle,  50, 200 + $this->headerHeight,  50, 299 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle,   0, 250 + $this->headerHeight, 100, 250 + $this->headerHeight, $borderColor);

			imageline($this->imageHandle, 167,   0 + $this->headerHeight, 167, 200 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle, 234,   0 + $this->headerHeight, 234, 200 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle, 100,  67 + $this->headerHeight, 299,  67 + $this->headerHeight, $borderColor);
			imageline($this->imageHandle, 100, 134 + $this->headerHeight, 299, 134 + $this->headerHeight, $borderColor);
		}
	}
}
