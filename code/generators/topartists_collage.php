<?php

require_once('code/generators/base/collage_base.php');
require_once('code/lastfmapi/manager.php');

class TopArtistsCollage extends CollageBase
{
	public function __construct($serverSideRequest = FALSE)
	{
		parent::__construct('artist', $serverSideRequest);
	}

	protected function GetIdentifier()
	{
		return 'topartists_collage';
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
}
