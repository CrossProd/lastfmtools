<?php

require_once('config/config.php');

require_once('code/lastfmapi/artistmanager.php');
require_once('code/lastfmapi/usermanager.php');
require_once('code/lastfmapi/albummanager.php');

require_once('code/lastfmapi/caching.php');
require_once('code/lastfmapi/socket.php');

class LastFmManager
{
	private $socket;

	private $lastFmArtistManager;
	private $lastFmUserManager;
	private $lastFmAlbumManager;

	public $error_string;
	public $error_number;

	public $lastFmCaching;

	function LastFmManager()
	{
		$this->lastFmCaching = new LastFmCaching($GLOBALS['caching_db_host'], $GLOBALS['caching_db_name'], $GLOBALS['caching_db_user'], $GLOBALS['caching_db_pass']);

		$this->lastFmArtistManager = NULL;
		$this->lastFmUserManager   = NULL;
		$this->lastFmAlbumManager  = NULL;
	}

	private function Connect()
	{
		if ($this->socket = new LastFmSocket($GLOBALS['lastfm_host'], $GLOBALS['lastfm_port']))
		{
			return true;
		}
		else
		{
			$this->error_number = $this->socket->error_number;
			$this->error_string = $this->socket->error_string;

			return false;
		}
	}

	function GetArtistManager()
	{
		if ($this->lastFmArtistManager == NULL)
		{
			$this->lastFmArtistManager = new LastFmArtistManager($this);
		}

		return $this->lastFmArtistManager;
	}

	function GetUserManager()
	{
		if ($this->lastFmUserManager == NULL)
		{
			$this->lastFmUserManager = new LastFmUserManager($this);
		}

		return $this->lastFmUserManager;
	}

	function GetAlbumManager()
	{
		if ($this->lastFmAlbumManager == NULL)
		{
			$this->lastFmAlbumManager = new LastFmAlbumManager($this);
		}

		return $this->lastFmAlbumManager;
	}

	function PerformQuery($method, $vars)
	{
		$this->Connect();

		$url = '/2.0/?';
		$url .= trim(urlencode('method')) . '=' . trim(urlencode($method)) . '&';
		$url .= trim(urlencode('api_key')) . '=' . trim(urlencode($GLOBALS['lastfm_key'])) . '&';

		foreach ($vars as $name => $value)
		{
			$url .= trim(urlencode($name)) . '=' . trim(urlencode($value)) . '&';
		}

		$url = substr($url, 0, -1);
		$url = str_replace(' ', '%20', $url);

		$response = $this->socket->Send($url, 'array');

		return $this->ProcessResponse($response);
	}

	private function ProcessResponse($response)
	{
		$xmlstr = '';
		$record = 0;

		foreach ($response as $line)
		{
			if ($record == 1)
			{
				$xmlstr .= $line;
			}
			elseif (substr($line, 0, 1) == '<')
			{
				$record = 1;
			}
		}

		try
		{
			libxml_use_internal_errors(true);

			$xml = new SimpleXMLElement($xmlstr);
		}
		catch (Exception $e)
		{
			print_r($e);
//				$errors = libxml_get_errors();
//
//				$error = $errors[0];
//
//				$this->handleError(95, 'SimpleXMLElement error: '.$e->getMessage().': '.$error->message);
		}

		if (!isset($e))
		{
			return $xml;
		}
	}
}
