<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('code/tools/log/generatorvisitsanalyzer.php');

$analyzer = new VisitorStatistics();

$analyzer->HandleVisits($GLOBALS['generator_list']);
