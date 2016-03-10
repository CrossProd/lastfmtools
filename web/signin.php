<?php

$page = 'home';

if (isset($_GET['page']))
{
	$page =  $_GET['page'];
}

$username = $_POST['username'];

// we want this to never expire at max once a year
setcookie('username', $username, time() + 1 * 365 * 24 * 3600);

header('Location: index.php?page=' . $page);
