<?php

if (!isset($_GET['username']))
{
	exit('failed: username is missing.');
}

require_once('config/config.php');
require_once('code/tools/priorityusers.php');

$username = strtolower($_GET['username']);

echo IsPriorityUser($username) ? "yes" : "no";
