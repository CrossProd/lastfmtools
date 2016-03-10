<?php

require_once('code/generators/tools/imagegeneratorconfig.php');
require_once('code/generators/tools/security.php');
require_once('code/tools/log/filelogger.php');
require_once('config/config.php');

abstract class ImageGenerator
{
	protected $parameters;
	protected $imageHandle;

	protected $isPreview;
	protected $isPriorityUser;
	protected $isBannedUser;

	protected $cacheTime;

	protected $debugMode;

	protected $serviceSideRequest;

	protected $period;
	protected $username;
	protected $headerColor = NULL;

	protected $headerHeight;

	protected $data = NULL;

	protected $enableAlpha = FALSE;

	private $reveivedHeaderColor;

	public $temporaryPath = NULL;

	abstract protected function GetImageType();
	abstract protected function GetIdentifier();
	abstract protected function GetFileCacheParameters();
	abstract protected function RenderImage();

	public function __construct($serverSideRequest = FALSE)
	{
		$this->serviceSideRequest = $serverSideRequest;

		$this->imageHandle = 0;
		$this->isPreview = false;
		$this->isPriorityUser = false;
		$this->isBannedUser = false;
		$this->debugMode = false;
		$this->headerHeight = 0;

		if (!$this->serviceSideRequest)
		{
			$this->LoadGlobalParameters();
			$this->DoRefererChecks();
			$this->DoPriorityUserCheck();

			if ($this->isBannedUser)
			{
				exit;
			}

			$this->SetCacheTime();
		}
	}

	public function __destruct()
	{
		if ($this->imageHandle)
		{
			imagedestroy($this->imageHandle);
		}
	}

	public function Run($writeToTemporaryFile = FALSE)
	{
		$this->LoadParameters();

		if (!$writeToTemporaryFile)
		{
			if ($this->ReturnClientOrFileCache())
			{
				return false;

			}
		}

		if (!$this->RetrieveLastFmData())
		{
			return false;
		}

		if (!$this->ProcessData())
		{
			return false;
		}

		$this->InitializeImage($this->GetClientImageHeight() + $this->headerHeight, 'white');

		$this->RenderHeader('albums');

		if (!$this->RenderImage())
		{
			return false;
		}

		if (!$writeToTemporaryFile)
		{
			if (!$this->OutputImage())
			{
				return false;
			}

			$this->WriteFileCache();
		}
		else
		{
			if (!$this->WriteAsTemporaryFile())
			{
			}
		}

		return true;
	}

	protected function RetrieveLastFmData()
	{
		return true;
	}

	protected function ProcessData()
	{
		return true;
	}

	protected function GetClientImageHeight()
	{
		return 0;
	}

	protected function GenerateErrorImage($message, $width = 300)
	{
		$this->imageHandle = imagecreatetruecolor($width, 100);

		$font = $GLOBALS['absolute_path'] . '/code/generators/data/general/fonts/luximb.ttf';

		imagettftext($this->imageHandle, 10, 0, 5,50, imagecolorallocate($this->imageHandle, 255, 255, 255), $font, $message);

		return true;
	}

	private function SetCacheTime()
	{
		$this->cacheTime = ImageGeneratorConfig::GetFileCacheDuration($this->GetIdentifier(), $this->parameters['period'],
							$this->isPriorityUser);
	}

	public function DoPriorityUserCheck()
	{
		if ($this->parameters['username'] != '')
		{
			$this->isPriorityUser = in_array($this->parameters['username'], $GLOBALS['priority_users']);
			$this->isBannedUser = in_array($this->parameters['username'], $GLOBALS['banned_users']);
		}
	}

	protected function LoadParameters()
	{
		return true;
	}


	protected function InitializeImage($height, $clearColorName)
	{
		$this->imageHandle = imagecreatetruecolor(300, $height);

		if ($this->enableAlpha)
		{
			imagesavealpha($this->imageHandle, TRUE);

			$color = $this->GetColorFromName($clearColorName, 127);

			imagefill($this->imageHandle, 0, 0, $color);
		}
		else
		{
			$color = $this->GetColorFromName($clearColorName);

			imagefilledrectangle($this->imageHandle, 0, 0, 300, $height + $this->headerHeight, $color);
		}
	}

	public function ReturnClientOrFileCache()
	{
		return $this->HandleClientCache() ? true : $this->OutputFileCache();
	}

	protected function GetFileCachePath()
	{
		$path = ABSOLUTE_PATH . 'cache/output/' . $this->GetIdentifier() . '/' .  $this->username . '_' . $this->period;

		if ($this->reveivedHeaderColor)
		{
			$path .= '_' . $this->headerColor;
		}

		foreach ($this->GetFileCacheParameters() as $name)
		{
			$path .= '_' . $this->parameters[$name];
		}

		return $path . $this->GetImageType();
	}

	public function WriteFileCache()
	{
		if ($GLOBALS['filecache_enabled'] && !$this->isPreview)
		{
			switch ($this->GetImageType())
			{
				case '.jpg':
					imagejpeg($this->imageHandle, $this->GetFileCachePath(), $this->GetImageQuality());
					break;
				case '.png':
					imagepng($this->imageHandle, $this->GetFileCachePath());
					break;
				case '.gif':
					break;
			}
		}
	}

	// returns full path
	public function WriteAsTemporaryFile()
	{
		$this->temporaryPath = $GLOBALS['absolute_path'] . 'cache/twitter/' . uniqid() . $this->GetImageType();

		switch ($this->GetImageType())
		{
			case '.jpg':
				imagejpeg($this->imageHandle, $this->temporaryPath, $this->GetImageQuality());
				break;
			case '.png':
				imagepng($this->imageHandle, $this->temporaryPath);
				break;
		}

		return TRUE;
	}

	//
	// image to client
	//

	public function OutputImage()
	{
		$this->OutputHeaderContentType();

		$expirationTime = time() + $this->cacheTime;

		$this->WriteCacheControlHeader(time(), $expirationTime);

		switch ($this->GetImageType())
		{
			case '.jpg':
				imagejpeg($this->imageHandle, NULL, $this->GetImageQuality());
			break;
			case '.png':
				imagepng($this->imageHandle);
			break;
			case '.gif':
			break;
		}

		$this->WriteVisitsLogEntry('fresh');

		return true;
	}

	public function OutputErrorImage($message)
	{
		if ($this->$imageHandle != 0)
		{
			imagedestroy($this->$imageHandle);
		}
	}

	protected function FixTextForGD($text)
	{
		$text_encoding = mb_detect_encoding($text, 'UTF-8, ISO-8859-1');

		if ($text_encoding != 'UTF-8')
		{
			$text = mb_convert_encoding($text, 'UTF-8', $text_encoding);
		}

		return mb_encode_numericentity($text, array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
	}

	protected function GetImageQuality()
	{
		return 0;
	}

	protected function GetColorFromName($parameter, $alpha = 0)
	{
		switch ($parameter)
		{
			case 'black':
				return imagecolorallocatealpha($this->imageHandle, 0, 0, 0, $alpha);
			case 'red':
				return imagecolorallocatealpha($this->imageHandle, 255, 0, 0, $alpha);
			case 'blue':
				return imagecolorallocatealpha($this->imageHandle, 0, 0, 255, $alpha);
			case 'yellow':
				return imagecolorallocatealpha($this->imageHandle, 255, 255, 0, $alpha);
			case 'green':
				return imagecolorallocatealpha($this->imageHandle, 0, 255, 0, $alpha);
			case 'purple':
				return imagecolorallocatealpha($this->imageHandle, 255, 0, 255, $alpha);
			default:
				return imagecolorallocatealpha($this->imageHandle, 255, 255, 255, $alpha);
		}
	}

	protected function GetFontPath($name)
	{
		return ABSOLUTE_PATH . 'code/generators/data/general/fonts/' . $name . '.ttf';
	}

	protected function RenderHeader($type)
	{
		if ($this->headerHeight == 0)
		{
			return;
		}

		$font = $this->GetFontPath('lucida');

		$textColor = $this->GetColorFromName($this->parameters['headerColor']);

		$text = 'favorite ' . $type . ' ';

		switch ($this->parameters['period'])
		{
			case 'overall':
				$text .= 'overall';
				break;

			case '12month':
				$text .= 'in the last 12 months';
				break;

			case '6month':
				$text .= 'in the last 6 months';
				break;

			case '12month':
				$text .= 'in the last 12 months';
				break;

			case '3month':
				$text .= 'in the last 3 months';
				break;

			case '1month':
				$text .= 'in the last month';
				break;

			case '7day':
				$text .= 'in the last 7 days';
				break;
		}

		$textSize = 10;

		$dimensions = imagettfbbox($textSize, 0, $font, $text);

		$width = abs($dimensions[4] - $dimensions[0]);

		$x = 150 - ($width * 0.5);

		imagettftext($this->imageHandle, $textSize, 0, $x, 14, $textColor, $font, $text);
	}

	//
	// private methods
	//

	private function LoadGlobalParameters()
	{
		$this->parameters['username'] = isset($_GET['username']) ? strtolower($_GET['username']) : '';
		$this->parameters['period'] = isset($_GET['period']) ? strtolower($_GET['period']) : 'overall';
		$this->parameters['key'] = isset($_GET['key']) ? $_GET['key'] : '';
		$this->parameters['headerColor'] = isset($_GET['headerColor']) ? $_GET['headerColor'] : 'none';

		$this->period = $this->parameters['period'];
		$this->username = $this->parameters['username'];
		$this->headerColor = $this->parameters['headerColor'];

		if ($this->headerColor != 'none')
		{
			$this->headerHeight = 20;
		}

		$this->isPreview = isset($_GET['preview']) ? ($_GET['preview'] == '1' ? true : false) : false;

		$this->reveivedHeaderColor = isset($_GET['headerColor']);
	}

	private function DoRefererChecks()
	{
		if ($this->isPreview)
		{
			$this->isPreview = Security::IsRefererPreviewDomain();
		}
		else
		{
			Security::DoRefererCheck();
		}
	}

	private function WriteVisitsLogEntry($exitPoint)
	{
		$logItems = array();

		$logItems[] = $exitPoint;
		$logItems[] = $this->isPreview ? 'preview' : 'live';

		foreach ($this->parameters as $key => $parameter)
		{
			$logItems[] = $key . ' = ' . str_replace(',', '<COMMA>', $parameter);
		}

		FileLogger::AppendLine('log/output/' . $this->GetIdentifier() . '/', $logItems);
	}

	// returns true if client cache is sufficient -> exit, false if image needs to be returned
	private function HandleClientCache()
	{
		if ($GLOBALS['clientcache_enabled'] && !$this->isPreview)
		{
			// check if client cache is still relevant
			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			{
				$expirationTime =
					strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) + $this->cacheTime;

				if (time() < $expirationTime)
				{
					header('HTTP/1.0 304 Not Modified');

					$this->WriteVisitsLogEntry('client cache');

					return true;
				}
			}
		}

		return false;
	}

	private function WriteCacheControlHeader($lastModified, $expires)
	{
		if ($GLOBALS['clientcache_enabled'] && !$this->isPreview)
		{
			header('Expires: ' . gmdate("D, d M Y H:i:s", $expires) . " GMT");
			header('last-modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
			header('Cache-control: public');
		}
	}

	private function OutputHeaderContentType()
	{
		switch($this->GetImageType())
		{
			case '.jpg':
				header('Content-Type: image/jpg');
				break;
			case '.png':
				header('Content-Type: image/png');
				break;
			case '.gif':
				header('Content-Type: image/gif');
				break;
		}
	}

	// returns true if file cache is sufficient -> exit, false if new image needs to be generated
	private function OutputFileCache()
	{
		if ($GLOBALS['filecache_enabled'] && !$this->isPreview)
		{
			$path = $this->GetFileCachePath();

			if (file_exists($path))
			{
				if ((strtotime('now') - filemtime($path)) < $this->cacheTime)
				{

					$this->OutputHeaderContentType();

					$expirationTime = filemtime($path) + $this->cacheTime;

					$this->WriteCacheControlHeader(filemtime($path), $expirationTime);

					readfile($path);

					$this->WriteVisitsLogEntry('file cache');

					return true;
				}
			}
		}

		return false;
	}
}
