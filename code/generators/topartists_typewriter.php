<?php

require_once('code/generators/base/imagegenerator.php');
require_once('code/lastfmapi/manager.php');

class TopArtistsTypewriter extends ImageGenerator
{
	public function __construct($serverSideRequest = FALSE)
	{
		parent::__construct($serverSideRequest);
	}

	protected function GetIdentifier()
	{
		return 'topartists_typewriter';
	}

	protected function LoadParameters()
	{

		$this->parameters['distortion'] = isset($_GET['distortion']) ? strtolower($_GET['distortion']) : 'average';
		$this->parameters['font'] = isset($_GET['font']) ? strtolower($_GET['font']) : 'carbon';
		$this->parameters['background'] = isset($_GET['background']) ? strtolower($_GET['background']) : 'transparent';
		$this->parameters['mask'] = isset($_GET['mask']) ? strtolower($_GET['mask']) : 'gy!be';
		$this->parameters['case'] = isset($_GET['case']) ? strtolower($_GET['case']) : 'lower';

		if ($this->parameters['background'] == 'transparent')
		{
			$this->enableAlpha = TRUE;
		}

	}

	public function GetImageType()
	{
		return '.png';
	}

	protected function GetFileCacheParameters()
	{
		return array('font', 'distortion', 'background', 'mask', 'case');
	}


	protected function RetrieveLastFmData()
	{
		if (($lastFmManager = new LastFmManager()) == NULL)
		{
			die('connection: fail! - ' . $lastFmManager->error_number . ' => ' . $lastFmManager->error_string);
		}

		$this->data = $lastFmManager->GetUserManager()->GetTopArtists($this->username, $this->period);

		if (!$this->data)
		{
			return false;
		}

		return true;
	}

	protected function GetClientImageHeight()
	{
		return 200;
	}

	public function RenderImage()
	{
		if ($this->parameters['background'] == 'transparent')
		{
			imagesavealpha($this->imageHandle, true);
			imagefill($this->imageHandle, 0, 0, imagecolorallocatealpha($this->imageHandle, 0, 0, 0, 127));
		}
		else if ($this->parameters['background'] == 'black')
		{
			imagefill($this->imageHandle, 0, 0, imagecolorallocate($this->imageHandle, 0, 0, 0));
		}
		else
		{
			imagefill($this->imageHandle, 0, 0, imagecolorallocate($this->imageHandle, 255, 255, 255));
		}

		// build a long string of artist names
		$artistString = '';

		$font = $this->GetFontPath($this->parameters['font']);

		$artistString = '';

		foreach ($this->data as $artist)
		{
			if ($this->parameters['case'] == 'upper')
			{
				$artistString .= mb_strtoupper($artist['name'], 'UTF-8') . '.';
			}
			else if ($this->parameters['case'] == 'lower')
			{
				$artistString .= mb_strtolower($artist['name'], 'UTF-8') . '.';
			}
			else
			{
				$artistString .= $artist['name'] . '.';
			}
		}

		// fix unavailable characters
		$healthy = array("?", "?");
		$yummy   = array("r", "R");

		$artistString = str_replace($healthy, $yummy, $artistString);

		$artistString .= $artistString . $artistString . $artistString . $artistString . $artistString . $artistString;

		$count = 0;

		if (($this->parameters['mask'] != 'black') && ($this->parameters['mask'] != 'white'))
		{
			$maskImage = @imagecreatefromjpeg(dirname(__FILE__) . '/data/typewriter/masks/' . $this->parameters['mask'] . '.jpg');
		}

		$fontSize = 9;

		srand(0);
		for ($i = 0; $i < strlen($artistString); $i++)
		{
			$lineSize = 40;

			$x = (($count % $lineSize) * 8) - 2;
			$y = (floor($count / $lineSize) * ($fontSize + 1) + 5);

			$rotationLimit = 0;

			if ($this->parameters['distortion'] >= 1)
			{
				$x += rand(0, 1);
			}

			if ($this->parameters['distortion'] >= 2)
			{
				$y += rand(0, 1);
			}

			if ($this->parameters['distortion'] >= 3)
			{
				$rotationLimit = ($this->parameters['distortion'] - 2) * 4;
			}

			if ($this->parameters['distortion'] >= 6)
			{
				$x += rand(0, 1);
				$y += rand(0, 1);
			}

			if ($y > 210)
			{
				break;
			}

			if ($this->parameters['mask'] == 'black')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 0, 0, 0, 0);
			}
			else if ($this->parameters['mask'] == 'white')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 255, 255, 255, 0);
			}
			else
			{
				$textColor = $this->GetImagePixelAverage($maskImage, $x, $y, $this->imageHandle);
			}

			$char = $this->FixTextForGD(mb_substr($artistString, $i, 1, 'UTF-8'));

			$y += $this->headerHeight;

			imagettftext($this->imageHandle, $fontSize, rand(-$rotationLimit, $rotationLimit), $x, $y, $textColor, $font, $char);

			$count++;
		}

		if (($this->parameters['mask'] != 'black') && ($this->parameters['mask'] != 'white'))
		{
			imagedestroy($maskImage);
		}

		return TRUE;
	}

	private function GetImagePixelAverage($image, $x, $y, $finalImage)
	{
		$sampleClippedX = min(max($x + 2, 0), 299);
		$sampleClippedY = min(max($y - 2, 0), 199);

		$rgbColor = imagecolorat($image, $sampleClippedX, $sampleClippedY);

		$r = ($rgbColor >> 16) & 255;
		$g = ($rgbColor >>  8) & 255;
		$b = ($rgbColor >>  0) & 255;

		$sampleClippedX = min(max($x + 6, 0), 299);
		$sampleClippedY = min(max($y - 2, 0), 199);

		$rgbColor = imagecolorat($image, $sampleClippedX, $sampleClippedY);

		$r += ($rgbColor >> 16) & 255;
		$g += ($rgbColor >>  8) & 255;
		$b += ($rgbColor >>  0) & 255;

		$sampleClippedX = min(max($x + 2, 0), 299);
		$sampleClippedY = min(max($y + 2, 0), 199);

		$rgbColor = imagecolorat($image, $sampleClippedX, $sampleClippedY);

		$r += ($rgbColor >> 16) & 255;
		$g += ($rgbColor >>  8) & 255;
		$b += ($rgbColor >>  0) & 255;

		$sampleClippedX = min(max($x + 6, 0), 299);
		$sampleClippedY = min(max($y + 2, 0), 199);

		$rgbColor = imagecolorat($image, $sampleClippedX, $sampleClippedY);

		$r += ($rgbColor >> 16) & 255;
		$g += ($rgbColor >>  8) & 255;
		$b += ($rgbColor >>  0) & 255;

		return imagecolorallocatealpha($finalImage, ($r / 4), ($g / 4), ($b / 4), 0);
	}
}
