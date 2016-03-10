<?php
		
class LastFmSocketException extends Exception { }

class LastFmSocket
{
	private $handle;
	private $host;
	private $port;

	public $error_string;
	public $error_number;

	function LastFmSocket($host, $port)
	{
		$this->host = $host;
		$this->port = $port;

		if (!($this->handle = @fsockopen($this->host, $this->port, $this->error_number, $this->error_string, 500)))
		{
			throw new LastFmSocketException('Unable to connect to last.fm servers.');

		}

		if ($this->handle)
		{
			stream_set_timeout($this->handle, 20);
		}

		return $this->handle ? true : false;
	}

	function Send($msg, $type = '')
	{
		$out = "GET " . $msg . " HTTP/1.0\r\nHost: " . $this->host . "\r\n\r\n";

		if (!@fwrite($this->handle, $out))
		{
			throw new LastFmSocketException('Unable to send data to last.fm servers.');
		}

		if ($type == 'array')
		{
			$response = array();

			$line_num = 0;

			while(!feof($this->handle))
			{
				if (!($response[$line_num++] = @fgets($this->handle, 4096)))
				{
					throw new LastFmSocketException('Unable to read data from last.fm servers.');
				}
			}

//				echo '<pre>';
//				print_r($response);
//				echo '</pre>';

			return $response;
		}
		elseif ($type == 'string')
		{
			$response = '';

			while (!feof($this->handle))
			{
				if (!($response .= fgets($this->handle, 4096)))
				{
					throw new LastFmSocketException('Unable to read data from last.fm servers.');
				}
			}

			return $response;
		}
		else
		{
			return true;
		}
	}

	function Close()
	{
		fclose($this->handle);

		return true;
	}
}
