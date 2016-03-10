<?php

if (!isset($_GET['username']))
{
	exit('failed: username is missing.');
}

if (!isset($_GET['key']))
{
	exit('failed: key is missing.');
}

if (!isset($_GET['artist']))
{
	exit('failed: artist is missing.');
}

if (!isset($_GET['album']))
{
	exit('failed: album is missing.');
}

require_once('config/config.php');
require_once('code/tools/priorityusers.php');

$username = strtolower($_GET['username']);
$key = strtolower($_GET['key']);
$artist = strtolower($_GET['artist']);
$album = strtolower($_GET['album']);

if (!IsPriorityKeyCorrect($username, $key))
{
	exit('failed: not a priority user or key is incorrect.');
}

require_once('code/generators/tools/artcache.php');

ArtCache::DeleteAlbumCache($artist, $album);

echo "Done";
