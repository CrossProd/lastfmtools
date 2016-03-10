<?php

class Image
{
	public static function GetExtension($filename)
	{
		if (strpos($filename, '.png') != false)
		{
			return '.png';
		}
		else if (strpos($filename, '.jpg') != false)
		{
			return '.jpg';
		}
		else if (strpos($filename, '.gif') != false)
		{
			return '.gif';
		}
		else if (strpos($filename, '.bmp') != false)
		{
			return '.bmp';
		}
	}

	public static function Open($filename)
	{
		if (strpos($filename, '.png') != false)
		{
			return imagecreatefrompng($filename);
		}
		else if (strpos($filename, '.jpg') != false)
		{
			return @imagecreatefromjpeg($filename);
		}
		else if (strpos($filename, '.gif') != false)
		{
			return imagecreatefromgif($filename);
		}
		else if (strpos($filename, '.bmp') != false)
		{
			return imagecreatefrombmp($filename);
		}

		return 0;
	}

	public static function ReadImageAndSaveToFile($url, $filename)
	{
		$contents = file_get_contents($url);

		if (!$contents)
		{
			return 0;
		}

		$fileHandle = fopen($filename, 'w');

		fwrite($fileHandle, $contents);
		fclose($fileHandle);

		return self::Open($filename);
	}

	public static function ReadImage($url)
	{
		if (strpos($url, '.png') != false)
		{
			return imagecreatefrompng($url);
		}
		else if (strpos($url, '.jpg') != false)
		{
			return @imagecreatefromjpeg($url);
		}
		else if (strpos($url, '.gif') != false)
		{
			return imagecreatefromgif($url);
		}
		else if (strpos($url, '.bmp') != false)
		{
			return imagecreatefrombmp($url);
		}

		return 0;
	}
}
