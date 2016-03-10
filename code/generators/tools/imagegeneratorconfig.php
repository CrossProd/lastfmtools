<?php

require_once('config/config.php');
require_once('config/priorityusers.php');

class ImageGeneratorConfig
{
	static public function GetFileCacheDuration($generator, $period, $isPriorityUser)
	{
		$key = 'filecache_user_' . $period;

		if ($isPriorityUser)
		{
			$key = 'priority_' . $key;
		}

		if (!isset($GLOBALS[$key]))
		{
			throw new exception('config key not found: ' . $key);
		}

		return $GLOBALS[$key];
	}
}
