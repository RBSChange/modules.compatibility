<?php
/**
 * @deprecated
 */
class f_mvc_HTTPRequest implements f_mvc_Request 
{
	/**
	 * @deprecated
	 */
	const GET = 'GET';

	/**
	 * @deprecated
	 */
	const POST = 'POST';
	
	/**
	 * @var array<String,array<String>>
	 */
	private $parameters;
	
	/**
	 * @var array<String,mixed>
	 */
	private $attributes = array();
	
	/**
	 * @var HttpSession
	 */
	private $session;
	
	/**
	 * @var array<String>
	 */
	private $errors = array();
	
	/**
	 * @var array<String>
	 */
	private $messages = array();
	
	/**
	 * @var change_Request
	 */
	private $agaviRequest;
	
	/**
	 * @var f_mvc_HTTPRequest
	 */
	private static $instance;
	
	private function __construct()
	{
		$this->agaviRequest = change_Controller::getInstance()->getContext()->getRequest();
	}
	
	/**
	 * @deprecated
	 */
	static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new f_mvc_HTTPRequest();
		}
		return self::$instance;
	}
	
	/**
	 * @deprecated
	 */
	function getParameter($name, $defaultValue = null)
	{
		return $this->agaviRequest->getParameter($name, $defaultValue);
	}
	
	/**
	 * @deprecated
	 */
	function getParameters()
	{
		return $this->agaviRequest->getParameters();
	}
	
	/**
	 * @deprecated
	 */
	function setAttribute($name, $value)
	{
		$this->agaviRequest->setAttribute($name, $value);
	}
	
	/**
	 * @deprecated
	 */
	function getAttribute($name, $defaultValue = null)
	{
		if ($this->agaviRequest->hasAttribute($name))
		{
			return $this->agaviRequest->getAttribute($name);
		}
		return $defaultValue;
	}
	
	/**
	 * @deprecated
	 */
	function getAttributes()
	{
		$attributes = array();
		foreach($this->agaviRequest->getAttributeNames() as $name)
		{
			$attributes[$name] = $this->agaviRequest->getAttribute($name);
		}
		return $attributes;
	}
	
	/**
	 * @deprecated
	 */
	function getSession()
	{
		if ($this->session === null)
		{
			$this->session = new f_mvc_HTTPSession();
		}
		return $this->session;
	}
	
	/**
	 * @deprecated
	 */
	function hasParameter($name)
	{
		return $this->agaviRequest->hasParameter($name);
	}
	
	/**
	 * @deprecated
	 */
	function hasNonEmptyParameter($name)
	{
		return $this->hasParameter($name) && !f_util_StringUtils::isEmpty($this->getParameter($name));
	}
	
	/**
	 * @deprecated
	 */
	function hasAttribute($name)
	{
		return array_key_exists($name, $this->agaviRequest->getAttributeNames());
	}
	
	/**
	 * @deprecated
	 */
	public function getModuleParameters($moduleName)
	{
		return $this->getParameter($moduleName."Param");
	}
	
	/**
	 * @deprecated
	 */
	public function setCookie($key, $value, $days = 30)
	{
		// TODO: handle arrays
		setcookie($key, $value, time() + 86400 * $days, '/');
	}
	
	/**
	 * @deprecated
	 */
	public function hasCookie($key)
	{
		return isset($_COOKIE[$key]);
	}
	
	/**
	 * @deprecated
	 */
	public function getCookie($key, $defaultValue = null)
	{
		if ($this->hasCookie($key))
		{
			return $_COOKIE[$key];
		}
		return $defaultValue;
	}
	
	/**
	 * @deprecated
	 */
	public function removeCookie($key)
	{
		setcookie($key, '', time() - 3600, '/');
	}
}

/**
 * @deprecated
 */
class f_mvc_FakeHttpRequest extends f_mvc_HTTPRequest 
{
	private $parameters;
	
	/**
	 * @var f_mvc_HTTPRequest
	 */
	private $httpRequest;
	
	/**
	 * @deprecated
	 */
	function __construct($parametersArray = array())
	{
		if (!is_array($parametersArray))
		{
			throw new IllegalArgumentException(__METHOD__ . ' is expecting an array');
		}
		$this->parameters = $parametersArray;
		$this->httpRequest = f_mvc_HTTPRequest::getInstance();
	}
		
	/**
	 * @deprecated
	 */
	function getParameter($name, $defaultValue = null)
	{
		if (isset($this->parameters[$name]))
		{
			return $this->parameters[$name];
		}
		return $defaultValue;
	}
	
	/**
	 * @deprecated
	 */
	function getParameters()
	{
		return $this->parameters;

	}

	/**
	 * @deprecated
	 */
	function hasParameter($name)
	{
		return isset($this->parameters[$name]);
	}
}