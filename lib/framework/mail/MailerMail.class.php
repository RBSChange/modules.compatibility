<?php
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
	 * @see Mailer::__construct()
	 */
	public function __construct($params)
	{
		$this->params = $params;
		
	}

	public function sendMail($body = null, $hdrs = null)
	{
		if (Framework::isDebugEnabled())
		{
			Framework::debug("Mailer to : ".$this->getParam('receiver'));
		}

		$body = $this->getMimeObject()->get();
        $hdrs = $this->getMimeObject()->headers($this->getHeaders());
        
		$mailObject = Mail::factory($this->mailDriver);
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
