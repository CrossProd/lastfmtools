<?php

if (!isset($_GET['username']))
{
	exit('failed: username is missing.');
}

if (!isset($_GET['key']))
{
	exit('failed: key is missing.');
}

if (!isset($_GET['usernametoclear']))
{
	exit('failed: username to clear is missing.');
}

require_once('config/config.php');
require_once('code/tools/priorityusers.php');

$username = strtolower($_GET['username']);
$key = strtolower($_GET['key']);
$usernameToClear = strtolower($_GET['usernametoclear']);

if (!IsPriorityKeyCorrect($username, $key))
{
	exit('failed: not a priority user or key is incorrect.');
}

foreach ($GLOBALS['generator_list'] as $generator)
{
	$fullPath = $GLOBALS['absolute_path'] . 'cache/output/' . $generator . '/' . $usernameToClear . '_*';

      echo "Cleaning: $fullPath<br />";

	@array_map("unlink", glob($fullPath));
}

echo 'done';
