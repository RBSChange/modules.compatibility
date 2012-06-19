<?php
/**
 * @deprecated In favor of Zend Framework class
 */
class f_web_oauth_Token
{
	/**
	 * @deprecated
	 */
	const TOKEN_NOT_AUTHORIZED = 0;
	
	/**
	 * @deprecated
	 */
	const TOKEN_AUTHORIZED = 1;
	
	/**
	 * @deprecated
	 */
	const TOKEN_ACCESS = 2;
	
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
	private $verificationCode;
	
	/**
	 * @var Int
	 */
	private $status = self::TOKEN_NOT_AUTHORIZED;
	
	/**
	 * @var String
	 */
	private $callback;
	
	/**
	 * @deprecated
	 */
	public function getVerificationCode()
	{
		return $this->verificationCode;
	}
	
	/**
	 * @deprecated
	 */
	public function setVerificationCode($verificationCode)
	{
		$this->verificationCode = $verificationCode;
	}
	
	/**
	 * @deprecated
	 */
	public function __construct($key = null, $secret = null)
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
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * @deprecated
	 */
	public function setStatus($status)
	{
		$this->status = $status;
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