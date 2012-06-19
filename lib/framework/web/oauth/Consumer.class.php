<?php
/**
 * @deprecated In favor of Zend Framework class
 */
class f_web_oauth_Consumer
{
	/**
	 * @var String
	 */
	private $key;
	
	/**
	 * @var String
	 */
	private $secret;
	
	/**
	 * @var String
	 */
	private $callback;

	/**
	 * @deprecated
	 */
	public function __construct($key, $secret = null)
	{
		$this->setSecret($secret);
		$this->setKey($key);
	}

	/**
	 * @deprecated
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @deprecated
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @deprecated
	 */
	public function getSecret()
	{
		return $this->secret;
	}

	/**
	 * @deprecated
	 */
	public function setSecret($secret)
	{
		$this->secret = $secret;
	}

	/**
	 * @deprecated
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * @deprecated
	 */
	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

}