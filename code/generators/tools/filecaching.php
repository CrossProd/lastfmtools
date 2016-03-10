<?php

class FileCaching
{
	// $duration in minutes
	static public function IsFileValid($filename, $duration)
	{
		if (!file_exists($filename))
		{
			return false;
		}

		if ((strtotime('now') - filemtime($filename)) < $duration)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	static public function RemoveFile($filename)
	{
		if (file_exists($filename))
		{
			unlink($filename);
		}
	}
}
