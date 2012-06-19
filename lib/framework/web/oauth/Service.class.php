<?php
/**
 * @deprecated In favor of Zend Framework class
 * @method f_web_oauth_Service getInstance()
 */
class f_web_oauth_Service extends change_BaseService 
{	
	/**
	 * @deprecated
	 */
	public function getNewToken($consumer)
	{
		return new f_web_oauth_Token($consumer->getKey(), $consumer->getSecret());
	}
	
	/**
	 * @deprecated
	 */
	public function authorizeToken($token)
	{
		return false;
	}
	
	/**
	 * @deprecated
	 */
	public function getAccessToken($token, $consumer)
	{
		return null;
	}
	
	/**
	 * @deprecated
	 */
	public function validateTimestamp($timestamp, $token = null, $consumer = null)
	{
		return false;
	}
	
	/**
	 * @deprecated
	 */
	public function getConsumerSecret($consumer)
	{
		$consumer->setSecret(PROJECT_ID);
		return $consumer->getSecret();
	}
	
	/**
	 * @deprecated
	 */
	public function getTokenSecret($tken)
	{
		$tken->setSecret('');
		return $tken->getSecret();
	}
}