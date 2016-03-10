<?php

require_once('code/generators/base/collage_base_var01.php');
require_once('code/lastfmapi/manager.php');

class TopAlbumsCollageVar01 extends CollageBaseVar01
{
	public function __construct($serverSideRequest = FALSE)
	{
		parent::__construct('album', $serverSideRequest);
	}

	protected function GetIdentifier()
	{
		return 'topalbums_collage_var01';
	}

      protected function RetrieveLastFmData()
      {
          if (($lastFmManager = new LastFmManager()) == NULL)
		{
			die('connection: fail! - ' . $lastFmManager->error_number . ' => ' . $lastFmManager->error_string);
		}

		$this->data = $lastFmManager->GetUserManager()->GetTopAlbums($this->username, $this->period);

          if (!$this->data)
          {
              return false;
          }

          return true;
      }
}
