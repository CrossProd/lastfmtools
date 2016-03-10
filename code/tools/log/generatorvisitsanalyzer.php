<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('config/config.php');
require_once('code/tools/log/analyzertools.php');

class VisitorStatistics
{
	private $cache = array();
	private $version = array();
	private $periods = array();
	private $userNames = array();
	private $generator = array();

	private $nrLines;

	private $yesterdayLog;

	function VisitorStatistics()
	{
		$this->ResetCounters();
	}

	private function ResetCounters()
	{
		$this->cache = array();
		$this->version = array();
		$this->periods = array();
		$this->userNames = array();
		$this->generators = array();

		$this->nrLines = 0;

		$this->yesterdayLog = date('Y-m-d', time() - (60 * 60 * 24));
	}

	private function UpdateCounters($generator)
	{
		$myFile = ABSOLUTE_PATH . 'log/output/' . $generator . '/' . $this->yesterdayLog . '.log';

		$fh = @fopen($myFile, 'r');

		$counter = 0;

		if ($fh)
		{
			while (!feof($fh))
			{
				$theData = fgets($fh);

				if ($theData == '')
				{
					break;
				}

				$parameters = explode(', ', substr($theData, 28));

				// cache & version
				AddStat($this->cache, $parameters[0]);
				AddStat($this->version, $parameters[1]);

				// username
				$username = explode(' = ', $parameters[2]);

				AddStat($this->userNames, $username[1]);

				// period
				$period = explode(' = ', $parameters[3]);

				AddStat($this->periods, $period[1]);

				$this->nrLines++; $counter++;
			}

			fclose($fh);
		}

		$this->generators[$generator] = $counter;
	}

	private function DisplayCounters()
	{
		arsort($this->cache);
		arsort($this->version);
		arsort($this->userNames);
		arsort($this->periods);
		arsort($this->generators);

		DisplayList($this->cache, 10, 'Response');
		DisplayList($this->version, 10, 'Version');
		DisplayList($this->userNames, 10, 'Top users');
		DisplayList($this->periods, 10, 'Periods');
		DisplayList($this->generators, 10, 'Generators');

		$GLOBALS['output'] .= 'Hits: ' . $this->nrLines . '<br />';
		$GLOBALS['output'] .= 'Unique users: ' . count($this->userNames) . '<br />';
	}

	public function HandleVisits($generators)
	{
		if (is_array($generators))
		{
			foreach ($generators as $generator)
			{
				$this->UpdateCounters($generator);
			}

			$this->DisplayCounters();
		}
		else
		{
			$this->UpdateCounters($generators);
			$this->DisplayCounters();
		}

		echo $GLOBALS['output'];

		if (is_array($generators))
		{
		//	EmailText('rjbruinier@gmail.com', $this->yesterdayLog . ': all_generators - visits' , $GLOBALS['output']);
		}
		else
		{
		//	EmailText('rjbruinier@gmail.com', $this->yesterdayLog . ': ' . $generators . ' - visits' , $GLOBALS['output']);
		}

		$GLOBALS['output'] = '';
	}
}
