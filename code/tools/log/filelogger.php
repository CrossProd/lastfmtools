<?php

require_once('config/config.php');

class FileLogger
{
	static private function GetFormattedIp()
	{
		$ip = $_SERVER['REMOTE_ADDR'];

		$explodedIp = explode('.', $ip);

		if (count($explodedIp) == 4)
		{
			return sprintf("%03d.%03d.%03d.%03d", $explodedIp[0], $explodedIp[1], $explodedIp[2], $explodedIp[3]);
		}
		else
		{
			return '000.000.000.000';
		}
	}

	static public function AppendLine($path, $data)
	{
		$filename = $GLOBALS['absolute_path'] . $path . date('Y-m-d') . '.log';

		$entry = date('H:i:s') . ' ' . FileLogger::GetFormattedIp() . ' -> ';

		if (is_array($data))
		{
			$entry .= implode(", ", $data) . "\r\n";
		}
		else
		{
			$entry .= $data . "\r\n";
		}

		file_put_contents($filename, $entry, FILE_APPEND | FILE_USE_INCLUDE_PATH);
	}
}
