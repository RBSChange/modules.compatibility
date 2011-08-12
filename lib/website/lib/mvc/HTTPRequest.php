<?php

class f_mvc_HTTPRequest implements f_mvc_Request 
{
	const GET = 'GET';
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
	 * @return f_mvc_HTTPRequest
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
	 * @param String $name
	 * @param String $defaultValue
	 * @return String the value of the parameter or $defaultValue
	 */
	function getParameter($name, $defaultValue = null)
	{
		return $this->agaviRequest->getParameter($name, $defaultValue);
	}
	
	/**
	 * @return array<String, array<String>>
	 */
	function getParameters()
	{
		return $this->agaviRequest->getParameters();
	}
	
	/**
	 * @param String $name
	 * @param mixed $value
	 */
	function setAttribute($name, $value)
	{
		$this->agaviRequest->setAttribute($name, $value);
	}
	
	/**
	 * @param String $name
	 * @param mixed $defaultValue
	 * @return mixed
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
	 * @return array<String, mixed>
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
	 * @return f_mvc_HTTPSession
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
	 * @param String $name
	 * @return Boolean 
	 */
	function hasParameter($name)
	{
		return $this->agaviRequest->hasParameter($name);
	}
	
	/**
	 * @param String $name
	 * @return Boolean
	 */
	function hasNonEmptyParameter($name)
	{
		return $this->hasParameter($name) && !f_util_StringUtils::isEmpty($this->getParameter($name));
	}
	
	/**
	 * @param String $name
	 * @return Boolean
	 */
	function hasAttribute($name)
	{
		return array_key_exists($name, $this->agaviRequest->getAttributeNames());
	}
	
	/**
	 * @param String $moduleName
	 * @return array
	 */
	public function getModuleParameters($moduleName)
	{
		return $this->getParameter($moduleName."Param");
	}
	
	/**
	 * Set a cookie.
	 * @param string $key
	 * @param string $value
	 * @param integer $days
	 */
	public function setCookie($key, $value, $days = 30)
	{
		// TODO: handle arrays
		setcookie($key, $value, time() + 86400 * $days, '/');
	}
	
	/**
	 * Test a cookie availability.
	 * @param string $key
	 * @return boolean
	 */
	public function hasCookie($key)
	{
		return isset($_COOKIE[$key]);
	}
	
	/**
	 * Get a cookie value.
	 * @param string $key
	 * @param string $defaultValue
	 * @return string
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
	 * Remove a cookie.
	 * @param string $key
	 */
	public function removeCookie($key)
	{
		setcookie($key, '', time() - 3600, '/');
	}
}

class f_mvc_FakeHttpRequest extends f_mvc_HTTPRequest 
{
	private $parameters;
	
	/**
	 * @var f_mvc_HTTPRequest
	 */
	private $httpRequest;
	
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
	 * 
	 * @param String $name 
	 * @param String $defaultValue 
	 * @return String the value of the parameter or $defaultValue 
	 * @see f_mvc_Request::getParameter()
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
	 * 
	 * @return array<String, array<String>> 
	 * @see f_mvc_Request::getParameters()
	 */
	function getParameters()
	{
		return $this->parameters;

	}

	/**
	 * @param String $name 
	 * @return Boolean 
	 * @see f_mvc_Request::hasParameter()
	 */
	function hasParameter($name)
	{
		return isset($this->parameters[$name]);
	}
}