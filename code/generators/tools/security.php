<?php

class Security
{
	public static function DoRefererCheck()
	{
		$allowedDomains = array('localhost', 'gammalyrae.com', 'last.fm', 'lastfm', 'what.cd');

		$domain = 'localhost';

		if (isset($_SERVER['HTTP_REFERER']))
		{
			$refererData = parse_url($_SERVER['HTTP_REFERER']);

			if (isset($refererData['host']))
			{
				$domain = $refererData['host'];
			}
		}

		$access = false;

		foreach ($allowedDomains as $allowedDomain)
		{
			if (stristr($domain, $allowedDomain))
			{
				$access = true;

				break;
			}
		}

		if (!$access)
		{
			exit;
		}
	}

//		function CleanFilename($filename)
//		{		
//			return preg_replace( "/[^\w_-]/", "-", strtolower( $filename) );
//		}


	// only allow previews from the configuration domains, otherwise people might implement the preview to bypass caching
	public static function IsRefererPreviewDomain()
	{
		$allowedDomains = array('localhost', 'gammalyrae.com');

		$domain = 'localhost';

		if (isset($_SERVER['HTTP_REFERER']))
		{
			$refererData = parse_url($_SERVER['HTTP_REFERER']);

			if (isset($refererData['host']))
			{
				$domain = $refererData['host'];
			}
		}

		foreach ($allowedDomains as $allowedDomain)
		{
			if (stristr($domain, $allowedDomain))
			{
				return true;
			}
		}

		return false;
	}
}
