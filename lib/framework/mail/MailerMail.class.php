<?php
/**
 * @deprecated
 */
class MailerMail extends Mailer
{

	private $params = array();
	
	private function getParam($name)
	{
		if (is_array($this->params) && isset($this->params[$name]))
		{
			return $this->params[$name];
		}
		return null;
	}
	
	/**
	 * @deprecated
	 */
	public function __construct($params)
	{
		$this->params = $params;
		
	}
	
	/**
	 * @deprecated
	 */
	public function sendMail($body = null, $hdrs = null)
	{
		$body = $this->getMimeObject()->get();
        $hdrs = $this->getMimeObject()->headers($this->getHeaders());
        if (class_exists('Mail'))
        {
        	$mailObject = Mail::factory($this->mailDriver);
        }
        else
        {
        	throw new Exception("Class Mail not found");
        }
		
		if (empty($hdrs))
		{
			return $mailObject->send($this->getParam('receiver'), $this->getHeaders(), $body);
		}
		else
		{
			return $mailObject->send($this->getParam('receiver'), $hdrs, $body);
		}
	}

	
}
