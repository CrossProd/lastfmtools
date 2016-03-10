<?php

require_once('config/config.php');

class LastFmPriorityUserManager
{
	public function AddPriorityUser($username, $amount)
	{
		$query =
			"INSERT INTO " .
			"		priority_users " .
			"		( " .
			"				priority_user_username, " .
			"				priority_user_amount " .
			"		) " .
			"VALUES " .
			"		( " .
			"				'$username', " .
			"				'$amount', " .
			"		) ";

		mysql_query($query);
	}
}
