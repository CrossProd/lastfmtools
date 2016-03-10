<?php

require_once('code/generators/base/imagegenerator.php');
require_once('code/lastfmapi/manager.php');

class TopArtistsSpiral extends ImageGenerator
{
	public function __construct($serverSideRequest = FALSE)
	{
		parent::__construct($serverSideRequest);

		$this->enableAlpha = TRUE;
	}

	protected function GetIdentifier()
	{
		return 'topartists_spiral';
	}

	protected function LoadParameters()
	{
		$this->parameters['color'] = isset($_GET['color']) ? strtolower($_GET['color']) : 'red';
	}

	public function GetImageType()
	{
		return '.png';
	}

	protected function GetFileCacheParameters()
	{
		return array('color');
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
		return 300;
	}

	public function RenderImage()
	{
		// build a long string of artist names
		$artistString = '';

		$current_path = dirname(__FILE__) . '/';

		$font = $current_path . 'data/general/fonts/monospatial.ttf';

		foreach ($this->data as $artist)
		{
			$artistString .= mb_strtoupper($artist['name'], 'UTF-8') . ' - ';
		}

		if (strlen($artistString) > 3)
		{
			$artistString = substr($artistString, 0, strlen($artistString) - 3);
		}

		// fix unavailable characters
		$healthy = array("?", "?", "Ž");
		$yummy   = array("r", "R", "Z");

		$artistString = str_replace($healthy, $yummy, $artistString);

		/*
			RENDER THE IMAGE
		*/

		$positionCounter = 0;
		$rotationOffset  = 45;

		for ($i = 0; $i < strlen($artistString); $i++)
		{
			$rotation = (180 - ($positionCounter * 8)) + $rotationOffset;
			$radius   = 130 - ($positionCounter * (0.7 - ($positionCounter * 0.0009)));
			$fontSize = 30 - ($positionCounter * 0.11);

			$x = (sin(deg2rad($rotation)) * $radius) + 145;
			$y = (cos(deg2rad($rotation)) * $radius) + 160 + $this->headerHeight;

			$colorFade = 255 - ($positionCounter * 0.7);

			if ($this->parameters['color'] == 'black')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 0, 0, 0, 127 - ($colorFade * 0.5));
			}
			else if ($this->parameters['color'] == 'red')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 255, 0, 0, 127 - ($colorFade * 0.5));
			}
			else if ($this->parameters['color'] == 'blue')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 0, 0, 255, 127 - ($colorFade * 0.5));
			}
			else if ($this->parameters['color'] == 'yellow')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 255, 255, 0, 127 - ($colorFade * 0.5));
			}
			else if ($this->parameters['color'] == 'green')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 0, 255, 0, 127 - ($colorFade * 0.5));
			}
			else if ($this->parameters['color'] == 'purple')
			{
				$textColor = imagecolorallocatealpha($this->imageHandle, 255, 0, 255, 127 - ($colorFade * 0.5));
			}

			$char = $this->FixTextForGD(mb_substr($artistString, $i, 1, 'UTF-8'));

			imagettftext($this->imageHandle, $fontSize, $rotation - 187, $x, $y, $textColor, $font, $char);

			$positionCounter++;

			if ($positionCounter > 350)
			{
				break;
			}
		}

		return TRUE;
	}
}
