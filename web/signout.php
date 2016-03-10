<?php

$page = 'home';

if (isset($_GET['page']))
{
	$page =  $_GET['page'];
}

setcookie('username', NULL);

header('Location: index.php?page=' . $page);
