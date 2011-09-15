<?php
/**
 * @deprecated
 */
class paginator_Url
{
	/**
	 * @deprecated
	 */
	public static function getInstanceFromCurrentUrl()
	{
		$inst = new paginator_Url();
		$rq = RequestContext::getInstance();
		if ($rq->getAjaxMode())
		{
			$requestUri = $rq->getAjaxFromURI();
		}
		else
		{
			$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
		}
		$index = strpos($requestUri, '?');
		$baseUrl = $index ? substr($requestUri, 0, $index) : $requestUri;
		$inst->baseUrl = $baseUrl;
		$inst->setQueryParameters($_GET);
		return $inst;
	}
	
	/**
	 * @deprecated
	 */
	public function removeQueryParameter($name)
	{
		$key = urlencode($name);
		if (isset($this->urlRequestParts[$key]))
		{
			unset($this->urlRequestParts[$key]);
			$this->setNeedsUpdate();
		}
	}

	/**
	 * @deprecated
	 */
	public function setQueryParameter($name, $value)
	{
		if (!is_array($value))
		{
			$key = urlencode($name);
			$this->urlRequestParts[$key] =  $key. "=" . urlencode($value);
		}
		else
		{
			$this->buildRecursivelyWithKeyAndValue($name, $value);
		}
		$this->setNeedsUpdate();
	}

	/**
	 * @deprecated
	 */
	public function setRequestPart($key, $value)
	{
		$this->urlRequestParts[$key] = $value;
		$this->setNeedsUpdate();
	}

	/**
	 * @deprecated
	 */
	public function setQueryParameters($array)
	{
		foreach ($array as $key => $val)
		{
			$this->buildRecursivelyWithKeyAndValue($key, $val);
		}
		$this->setNeedsUpdate();
	}
	
	/**
	 * @deprecated
	 */
	public function setBaseUrl($url)
	{
		$this->baseUrl = $url;
		$this->setNeedsUpdate();
	}
	
	/**
	 * @deprecated
	 */
	public function getStringRepresentation()
	{
		if (is_null($this->stringRepresentation))
		{
			$this->stringRepresentation = $this->baseUrl;
			if (count($this->urlRequestParts) > 0)
			{
				$this->stringRepresentation .= '?' . implode('&', $this->urlRequestParts);
			}
		}
		return $this->stringRepresentation;
	}
	
	/**
	 * @deprecated
	 */
	public function __toString()
	{
		return $this->getStringRepresentation();
	}

	private $currentPath = array();
	private $urlRequestParts = array();
	private $stringRepresentation = null;
	private $baseUrl = null;
	
	/**
	 * @deprecated
	 */
	private function buildRecursivelyWithKeyAndValue($name, $value)
	{
		$this->currentPath[] = urlencode(count($this->currentPath) == 0 ? $name : "[$name]");
		if (is_array($value))
		{
			foreach ($value as $k => $v)
			{
				$this->buildRecursivelyWithKeyAndValue($k, $v);
			}
		}
		else
		{
			$path = implode($this->currentPath);
			if ($value === null)
			{
				if (isset($this->urlRequestParts[$path]))
				{
					unset($this->urlRequestParts[$path]);
				}
			}
			else
			{
				$this->urlRequestParts[$path] =  $path. "=" . urlencode($value);
			}
		}
		array_pop($this->currentPath);
	}
	
	/**
	 * @deprecated
	 */
	private function setNeedsUpdate()
	{
		$this->stringRepresentation = null;
	}
}