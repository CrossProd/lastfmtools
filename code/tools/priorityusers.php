<?php

require_once('config/priorityusers.php');

function IsPriorityUser($username)
{
	foreach ($GLOBALS['priority_users'] as $user)
	{
		if (strcasecmp($username, $user) == 0)
		{
			return true;
		}
	}

	return false;
}

function IsPriorityKeyCorrect($username, $userkey)
{
	foreach ($GLOBALS['priority_keys'] as $user => $key)
	{
		if (strcasecmp($username, $user) == 0)
		{
			if (strcasecmp($userkey, $key) == 0)
			{
				return true;
			}

			return false;
		}
	}

	return false;
}
