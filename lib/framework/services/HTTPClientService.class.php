<?php
/**
 * @deprecated in favor of change_HttpClientService
 */
/**
 * @package framework.service
 */
class HTTPClientService extends BaseService
{

	private static $instance;

	/**
	 * @deprecated
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @deprecated
	 */
	public function getNewHTTPClient($acceptCookies = true)
	{
		return new HTTPClient($acceptCookies);
	}
}
/**
 * @deprecated
 */
class HTTPClient
{
	/**
	 * @var Integer
	 */
	private $httpReturnCode = -1;

	/**
	 * @var Integer
	 */
	private $httpHeaders = array();
	/**
	 * @var resource
	 */
	private $curlResource;

	private $followRedirects = true;
	private $timeOut = 60;
	private $referer = '';

	private $proxyHost;
	private $proxyPort;

	private $acceptCookies;
	private $cookieName;

	public function __construct($acceptCookies = true)
	{
		$this->curlResource = curl_init();
		$this->setOption(CURLOPT_RETURNTRANSFER, true);

		// Cookies handling.
		$this->acceptCookies = $acceptCookies;
		if ($this->acceptCookies)
		{
			$this->cookieName = uniqid('./.');
			$this->setOptions(array(
			CURLOPT_COOKIEJAR => $this->cookieName,
			CURLOPT_COOKIEFILE => $this->cookieName,
			));
		}

		// Set default proxy.
		if (defined('OUTGOING_HTTP_PROXY_HOST') && OUTGOING_HTTP_PROXY_HOST
		&& defined('OUTGOING_HTTP_PROXY_PORT') && OUTGOING_HTTP_PROXY_PORT)
		{
			$this->setProxy(OUTGOING_HTTP_PROXY_HOST, OUTGOING_HTTP_PROXY_PORT);
		}
	}

	public function __destruct()
	{
		if ($this->curlResource !== null)
		{
			$this->close();
		}
	}

	/**
	 * @deprecated
	 */
	public function close()
	{
		curl_close($this->curlResource);
		if ($this->acceptCookies && file_exists($this->cookieName))
		{
			@unlink($this->cookieName);
		}
		$this->curlResource = null;
	}

	/**
	 * @deprecated
	 */
	public function get($url)
	{
		$this->setOption(CURLOPT_POSTFIELDS, null);
		$this->setOption(CURLOPT_POST, false);
		return $this->execute($url);
	}
	
	/**
	 * @deprecated
	 */
	public function download($url, $path)
	{
		// TODO: refactor with get, use default curl handler
		if (!is_writable(dirname($path)))
		{
			throw new Exception("Can not write to $path");
		}
		$tmpPath =  f_util_FileUtils::getTmpFile("httpclientdownload");
		register_shutdown_function(array("f_util_FileUtils", "unlink"), $tmpPath);
		$fp = fopen($tmpPath, 'w');
		if (!$fp)
		{
			throw new Exception("Could not open ".$tmpPath." for writing");
		}
		$ch = curl_init(str_replace(" ", "%20", $url));
		if ($ch === false)
		{
			fclose($fp);
			throw new Exception("Could not download $url");
		}
		
		if ($this->proxyHost && $this->proxyPort)
		{
			$this->setOption(CURLOPT_PROXY, $this->proxyHost.':'.$this->proxyPort, $ch);
		}
		$this->setOption(CURLOPT_FOLLOWLOCATION, 1, $ch);
		$tmpCookiePath = f_util_FileUtils::getTmpFile();
		$this->setOption(CURLOPT_COOKIEJAR, $tmpCookiePath, $ch);
		$this->setOption(CURLOPT_COOKIEFILE, $tmpCookiePath, $ch);
		$this->setOption(CURLOPT_TIMEOUT, 300, $ch);
		$this->setOption(CURLOPT_CONNECTTIMEOUT, 5, $ch);
		$this->setOption(CURLOPT_FILE, $fp, $ch);
		if ($this->followRedirects)
		{
			$this->setOption(CURLOPT_FOLLOWLOCATION, true, $ch);
		}

		// FIXME anything to do with data ?
		$data = curl_exec($ch);
		f_util_FileUtils::unlink($tmpCookiePath);
		$errno = curl_errno($ch);
		curl_close($ch);
		fclose($fp);
		if ($errno)
		{
			throw new Exception("Error curlerrno: ".$errno);
		}		
		if (!rename($tmpPath, $path))
		{
			throw new Exception("Could not create $path");
		}
	}

	/**
	 * @deprecated
	 */
	public function post($url, $params)
	{
		$query = http_build_query($params, null , '&');		
		$this->setOption(CURLOPT_POSTFIELDS, $query);
		$this->setOption(CURLOPT_POST, true);
		return $this->execute($url);
	}

	/**
	 * @deprecated
	 */
	public function setProxy($host, $port)
	{
		$this->proxyHost = $host;
		$this->proxyPort = $port;
	}

	/**
	 * @deprecated
	 */
	public function setFollowRedirects($value)
	{
		$this->followRedirects = $value;
	}

	/**
	 * @deprecated
	 */
	public function setTimeOut($value)
	{
		$this->timeOut = $value;
	}

	/**
	 * @deprecated
	 */
	public function setReferer($referer)
	{
		$this->referer = $referer;
	}
	
	/**
	 * @deprecated
	 */
	public function setOption($option, $value, $ch = null)
	{
		if ($ch === null)
		{
			$ch = $this->curlResource;
		}
		if (curl_setopt($ch, $option, $value) === false)
		{
			throw new Exception("Unable to set curl option ".$option);
		}
	}

	/**
	 * @deprecated
	 */
	public function setOptions($options)
	{
		curl_setopt_array($this->curlResource, $options);
	}

	/**
	 * @param String $url
	 * @param String $postFields
	 * @return String
	 */
	private function execute($url = null)
	{
		Framework::info(__METHOD__ . ": " . $url);
		if ($this->proxyHost && $this->proxyPort)
		{
			$this->setOption(CURLOPT_PROXY, $this->proxyHost.':'.$this->proxyPort);
		}

		$this->setOptions(array(
			CURLOPT_REFERER => $this->referer,
			CURLOPT_TIMEOUT => $this->timeOut,
			CURLOPT_CONNECTTIMEOUT => $this->timeOut,
			CURLOPT_FOLLOWLOCATION => $this->followRedirects,
			CURLOPT_URL => $url,
			CURLOPT_DNS_USE_GLOBAL_CACHE => 1,
			CURLOPT_HEADERFUNCTION => array($this, 'readHeaders')
		));

		$data = curl_exec($this->curlResource);
		$errno = curl_errno($this->curlResource);
		// 52 is just CURLE_GOT_NOTHING
		if ($errno && $errno != 52)
		{
			Framework::error(__METHOD__ . ': curl_errno : ' . $errno);
		}
		$this->httpReturnCode = curl_getinfo($this->curlResource, CURLINFO_HTTP_CODE);
		$this->close();
		return $data;
	}

	private function readHeaders($ch, $header)
	{
		$trimmedHeader = trim($header);
		if (f_util_StringUtils::isNotEmpty($trimmedHeader))
		{
			$this->httpHeaders[] = $trimmedHeader;
		}
		return strlen($header);
	}
	
	/**
	 * @deprecated
	 */
	public function getHTTPReturnCode()
	{
		return $this->httpReturnCode;
	}

	/**
	 * @deprecated
	 */
	public function getHTTPHeaders()
	{
		return $this->httpHeaders;
	}
}
