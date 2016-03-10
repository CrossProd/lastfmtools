<?php

require_once('code/generators/base/imagegenerator.php');
require_once('code/generators/tools/artcache.php');

class TopAlbumsOffline extends ImageGenerator
{
	protected function GetIdentifier()
	{
		return 'topalbums_offline';
	}

	protected function LoadParameters()
	{
		parent::LoadParameters();

	$this->parameters['border'] = isset($_GET['border']) ? strtolower($_GET['border']) : '';
	}

	protected function GetImageType()
	{
		return '.jpg';
	}

	protected function GetFileCacheParameters()
	{
		return array();
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

		$imageNrCols = 10;
		$imageNrRows = 5;
		$imageArtSize = 85;
		$artLimit = $imageNrCols * $imageNrRows;

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

			$artImage = ArtCache::GetAlbum85x85($artist, $name, $url, false);

			// if valid image, then add it to list and check if we reached the set cover limit
			if ($artImage)
			{
				$usableImages[] = $artImage;

				if (++$usableCount >= $artLimit)
				{
					break;
				}
			}
		}

		$imageNrRows = floor(count($usableImages) / $imageNrCols);

		if ($imageNrRows == 0)
		{
			return;
		}

		// create the final image
		$this->imageHandle = imagecreatetruecolor(851, $imageNrRows * $imageArtSize);

		$usableCount = 0;

		for ($row = 0; $row < $imageNrRows; $row++)
		{
			for ($col = 0; $col < $imageNrCols; $col++)
			{
				$artImage = $usableImages[$usableCount++];

	  // fix the one pixel on the right (85*1*)
	  if ($col == $imageNrCols - 1)
	  {
				imagecopy($this->imageHandle, $artImage, ($col * $imageArtSize) + 1, $row * $imageArtSize, 0, 0, $imageArtSize, $imageArtSize);
	  }

				imagecopy($this->imageHandle, $artImage, $col * $imageArtSize, $row * $imageArtSize, 0, 0, $imageArtSize, $imageArtSize);
			}
		}

		$this->RenderBorder($imageNrRows, $imageNrCols, $imageArtSize);

		// destroy all images
		for ($i = 0; $i < count($usableImages); $i++)
		{
			imagedestroy($usableImages[$i]);
		}

		return true;
	}

	private function RenderBorder($imageNrRows, $imageNrCols, $imageArtSize)
	{
		// add borders if necessary
		if ($this->parameters['border'] != '' && $this->parameters['border'] != 'none')
		{
			$borderColor = imagecolorallocate($this->imageHandle, 255, 255, 255);

			if ($this->parameters['border'] == 'black')
			{
				$borderColor = imagecolorallocate($this->imageHandle, 0, 0, 0);
			}

			for ($i = 1; $i < $imageNrCols; $i++)
			{
				imageline($this->imageHandle, $i * $imageArtSize, 0, $i * $imageArtSize, $imageNrRows * $imageArtSize, $borderColor);
			}

			for ($i = 1; $i < $imageNrRows; $i++)
			{
				imageline($this->imageHandle, 0, $i * $imageArtSize, 851, $i * $imageArtSize, $borderColor);
			}
		}
	}
}
