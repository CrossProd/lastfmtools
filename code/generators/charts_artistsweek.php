<?php

require_once('code/generators/base/imagegenerator.php');
require_once('code/generators/tools/artcache.php');
require_once('code/tools/priorityusers.php');

class ChartsArtistsWeek extends ImageGenerator
{
	protected function GetIdentifier()
	{
		return 'charts_artistsweek';
	}

	protected function LoadParameters()
	{
		parent::LoadParameters();

		$this->parameters['artists'] = isset($_GET['artists']) ? strtolower($_GET['artists']) : '';
		$this->parameters['nrweeks'] = isset($_GET['nrweeks']) ? strtolower($_GET['nrweeks']) : '20';
		$this->parameters['layout'] = isset($_GET['layout']) ? strtolower($_GET['layout']) : '2dbars';
	}

	protected function GetImageType()
	{
		return '.png';
	}

	protected function GetFileCacheParameters()
	{
		return array();
	}

	public function ReturnClientOrFileCache()
	{
		return false;
	}

	private $chartColors = array();
	private $topArtists = array();

	private $top;
	private $bottom;
	private $left;
	private $right;

	private $graphWidth;
	private $graphHeight;

	private $paddingVertical;
	private $paddingHorizontal;

	private $graphInnerPaddingTop;

	private $imageWidth;
	private $imageHeight;

	private $nrWeeksInChart;

	private $maxCount;

	public function RenderImage($lastFmManager)
	{
		if (!IsPriorityUser($this->parameters['username']))
		{
			$this->GenerateErrorImage('Not a priority user!', 720);

  		  	return true;
		}

		$activeArtists = explode(';', $this->parameters['artists']);

		for ($i = 0; $i < count($activeArtists); $i++)
		{
			$activeArtists[$i] = trim(mb_convert_case(urldecode($activeArtists[$i]), MB_CASE_LOWER, 'UTF-8'));
		}

		$availableWeeks = $lastFmManager->GetUserManager()->GetWeeklyChartList($this->parameters['username']);

		// this is the real number of weeks in the chart
		$this->nrWeeksInChart = min($this->parameters['nrweeks'], count($availableWeeks));

		// initialize artist charts
		foreach ($activeArtists as &$activeArtist)
		{
			$this->topArtists[$activeArtist]['name'] = $activeArtist;
			$this->topArtists[$activeArtist]['maxCount'] = 0;
			$this->topArtists[$activeArtist]['total'] = 0;

			for ($i = 0; $i < $this->nrWeeksInChart; $i++)
			{
				$this->topArtists[$activeArtist]['weeks'][$i]['PC'] = 0;
			}
		}

		// extract data
		$this->maxCount = 0;

		$startIndex = max(0, count($availableWeeks) - $this->nrWeeksInChart);

		for ($i = 0; $i < $this->nrWeeksInChart; $i++)
		{
			$weekDetails = &$availableWeeks[$startIndex + $i];

			$weekTopArtists = $lastFmManager->GetUserManager()->GetWeeklyArtistChart($this->parameters['username'], $weekDetails['from'], $weekDetails['to']);

			foreach ($weekTopArtists as &$artist)
			{
				$artistName = mb_convert_case($artist['name'], MB_CASE_LOWER, 'UTF-8');

				if (in_array($artistName, $activeArtists))
				{
					$this->topArtists[$artistName]['weeks'][$i]['PC'] = $artist['PC'];

					$this->topArtists[$artistName]['total'] += $artist['PC'];

					if ($artist['PC'] > $this->maxCount)
					{
						$this->maxCount = $artist['PC'];
					}

					if ($artist['PC'] > $this->topArtists[$artistName]['maxCount'])
					{
						$this->topArtists[$artistName]['maxCount'] = $artist['PC'];
					}
				}
			}
		}

		$this->paddingVertical   = 20;
		$this->paddingHorizontal = 30;

		$this->graphInnerPaddingTop = 10;

		$this->imageWidth  = 720;
		$this->imageHeight = 310 + count($this->topArtists) * 20;

		$this->top    = 0 + $this->paddingVertical;
		$this->bottom = 300 - $this->paddingVertical;

		$this->left  = 0 + $this->paddingHorizontal;
		$this->right = $this->imageWidth - $this->paddingHorizontal;

		$this->graphWidth  = $this->right - $this->left;
		$this->graphHeight = ($this->bottom - $this->top) - $this->graphInnerPaddingTop;

		$this->imageHandle = imagecreatetruecolor($this->imageWidth, $this->imageHeight);

	    imagefill($this->imageHandle, 0, 0, imagecolorallocate($this->imageHandle, 255, 255, 255));

	 	// some colors
		$colorBlack = imagecolorallocate($this->imageHandle, 0, 0, 0);
		$colorRed   = imagecolorallocate($this->imageHandle, 255, 0, 0);
		$colorGray  = imagecolorallocate($this->imageHandle, 150, 150, 150);

		// vertical scale
		$font     = $GLOBALS['absolute_path'] . 'code/generators/data/general/fonts/courier.ttf';
		$fontBold = $GLOBALS['absolute_path'] . 'code/generators/data/general/fonts/courier-bold.ttf';

		imageline($this->imageHandle, $this->left, $this->top + $this->graphInnerPaddingTop, $this->right, $this->top + $this->graphInnerPaddingTop, $colorGray);
		imageline($this->imageHandle, $this->left, $this->top + $this->graphInnerPaddingTop + ($this->graphHeight / 2), $this->right, $this->top + $this->graphInnerPaddingTop + ($this->graphHeight / 2), $colorGray);

		self::imageTextAlignRight($this->imageHandle, 9, 0, $this->left - 4, $this->top + 4 + $this->graphInnerPaddingTop, $colorBlack, $font, $this->maxCount);
		self::imageTextAlignRight($this->imageHandle, 9, 0, $this->left - 4, $this->top + 4 + $this->graphInnerPaddingTop + ($this->graphHeight / 2), $colorBlack, $font, floor($this->maxCount / 2));

		$this->chartColors[] = imagecolorallocate($this->imageHandle, 255, 69, 0);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 93, 138, 168);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 255, 191,	0);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 75, 83, 32);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 174, 22, 32);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 218, 50, 135);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 192, 192,	192);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 255, 0, 0);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 0, 255, 0);
		$this->chartColors[] = imagecolorallocate($this->imageHandle, 0, 0, 255);

		if ($this->parameters['layout'] == '2dbars')
		{
			self::RenderBarGraph();
		}
		else
		{
			self::RenderLineGraph();
		}

		self::imageTextAlignCenter($this->imageHandle, 9, 0, $this->right, $this->bottom + $this->graphInnerPaddingTop + 2, $colorBlack, $font, 'last');
		self::imageTextAlignCenter($this->imageHandle, 9, 0, $this->right, $this->bottom + $this->graphInnerPaddingTop + 11, $colorBlack, $font, 'week');

		self::imageTextAlignCenter($this->imageHandle, 9, 0, $this->left, $this->bottom + $this->graphInnerPaddingTop + 2, $colorBlack, $font, $this->nrWeeksInChart . ' weeks');
		self::imageTextAlignCenter($this->imageHandle, 9, 0, $this->left, $this->bottom + $this->graphInnerPaddingTop + 11, $colorBlack, $font, 'ago');

		imageline($this->imageHandle, $this->left, $this->top, $this->left, $this->bottom, $colorBlack);
		imageline($this->imageHandle, $this->right, $this->top, $this->right, $this->bottom, $colorBlack);
		imageline($this->imageHandle, $this->left, $this->top, $this->right, $this->top, $colorBlack);
		imageline($this->imageHandle, $this->left, $this->bottom, $this->right, $this->bottom, $colorBlack);

		$artistCount = 0;
		foreach ($this->topArtists as &$topArtist)
		{
			$x = 90;
			$y = 300 + $artistCount * 20;

			imagefilledrectangle($this->imageHandle, $x, $y, $x + 15, $y + 15, $this->chartColors[$artistCount]);

			$artistString = $topArtist['name'] . ' (' . $topArtist['total'] . ' plays)';

			imagettftext($this->imageHandle, 9, 0, $x + 20, $y + 11, $colorBlack, $font, $artistString);

			$artistCount++;
		}


		return true;
	}

	private function RenderLineGraph()
	{
		$artistCount = 0;
		foreach ($this->topArtists as &$topArtist)
		{
			for ($i = 0; $i < $this->nrWeeksInChart - 1; $i++)
			{
				$weekCount = &$topArtist['weeks'][$i];
				$nextCount = &$topArtist['weeks'][$i + 1];

				if ($weekCount['PC'] > 0)
				{
					$height1 = $this->bottom - (($this->graphHeight / $this->maxCount) * $weekCount['PC']) + 0;
				}
				else
				{
					$height1 = $this->bottom;
				}

				if ($nextCount['PC'] > 0)
				{
					$height2 = $this->bottom - (($this->graphHeight / $this->maxCount) * $nextCount['PC']) + 0;
				}
				else
				{
					$height2 = $this->bottom;
				}

				$left1X = self::GetBarCenter($i, $this->nrWeeksInChart, $this->graphWidth - 2) + $this->left + 2;
				$left2X = self::GetBarCenter($i + 1, $this->nrWeeksInChart, $this->graphWidth - 2) + $this->left + 2;

				imageantialias($this->imageHandle, false);

				imageline($this->imageHandle, $left1X, $height1, $left2X, $height2, $this->chartColors[$artistCount]);

				imageantialias($this->imageHandle, true);

				imageline($this->imageHandle, $left1X, $height1, $left2X, $height2, $this->chartColors[$artistCount]);
				imageline($this->imageHandle, $left1X, $height1 - 1, $left2X, $height2 - 1, $this->chartColors[$artistCount]);
			}

			$artistCount++;
		}
	}

	private function RenderBarGraph()
	{
		$artistCount = 0;
		foreach ($this->topArtists as &$topArtist)
		{
			for ($i = 0; $i < $this->nrWeeksInChart; $i++)
			{
				$weekCount = &$topArtist['weeks'][$i];

				if ($weekCount['PC'] > 0)
				{
					$startHeight = $this->bottom - (($this->graphHeight / $this->maxCount) * $weekCount['PC']) + 0;

					$leftX = self::GetBarLeft($i, $this->nrWeeksInChart, $this->graphWidth - 2) + $this->left + 2;
					$rghtX = self::GetBarLeft($i + 1, $this->nrWeeksInChart, $this->graphWidth - 2) + $this->left;

					for ($x = $leftX; $x <= $rghtX; $x++)
					{
						imageline($this->imageHandle, $x, $startHeight, $x, $this->bottom - 1, $this->chartColors[$artistCount]);
					}
				}
			}

			$artistCount++;
		}
	}

	private function imageTextAlignRight($image, $size, $rotation, $x, $y, $color, $font, $text)
	{
		$boundingBox = imagettfbbox($size, $rotation, $font, $text);

		$width = $boundingBox[4] - $boundingBox[6];

		$x -= $width;

		imagettftext($image, $size, $rotation, $x, $y, $color, $font, $text);
	}

	private function imageTextAlignCenter($image, $size, $rotation, $x, $y, $color, $font, $text)
	{
		$boundingBox = imagettfbbox($size, $rotation, $font, $text);

		$width = $boundingBox[4] - $boundingBox[6];

		$x -= $width / 2;

		imagettftext($image, $size, $rotation, $x, $y, $color, $font, $text);
	}

	function GetBarLeft($weekNr, $nrWeeks, $graphWidth)
	{
		$weekWidth = $graphWidth / $nrWeeks;

		return floor($weekNr * $weekWidth);
	}

	function GetBarCenter($weekNr, $nrWeeks, $graphWidth)
	{
		return floor((self::GetBarLeft($weekNr, $nrWeeks, $graphWidth) + self::GetBarLeft($weekNr + 1, $nrWeeks, $graphWidth)) / 2);
	}
}
