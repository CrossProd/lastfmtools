<?php

$GLOBALS['output'] = '';

function AddStat(&$array, $key)
{
	if (isset($array[$key]))
	{
		$array[$key]++;
	}
	else
	{
		$array[$key] = 1;
	}
}

function DisplayList(&$array, $limit, $title)
{
	$GLOBALS['output'] .= $title . ':' . '<br />';

	$count = 0;
	foreach ($array as $key => $value)
	{
		$GLOBALS['output'] .= sprintf('%02d ', $count + 1) . $key . ': ' . $value . '<br />';

		if (++$count == $limit)
		{
			break;
		}
	}

	$GLOBALS['output'] .= '<br />';
}

function EmailText($address, $title, $text)
{
	$body = str_replace('<br />', "\r\n", $text);

	$to = $address;
	$subject = $title;
	$headers = 'From: ' . "\r\n" .
					 'Reply-To: ' . "\r\n" .
					 'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $body, $headers);
}
