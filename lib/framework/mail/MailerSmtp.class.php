<?php
/**
 * @deprecated
 */
class MailerSmtp extends Mailer
{
	/**
	 * @deprecated
	 */
	public function __construct($params)
	{
		// Set mail driver
		$this->mailDriver = strtolower($params['type']);
		
		$this->factoryParams['host'] = $params['host'];

		if (empty($params['port']))
		{
			$this->factoryParams['port'] = 25;
		}
		else
		{
			$this->factoryParams['port'] = $params['port'];
		}

		if ($params['auth'] == "true")
		{
			$this->factoryParams['auth'] = TRUE;
			$this->factoryParams['username'] = $params['username'];
			$this->factoryParams['password'] = $params['password'];
		}
	}
		
	/**
	 * @deprecated
	 */
	public function getFactoryParams()
	{
		return $this->factoryParams;
	}
		
	/**
	 * @deprecated
	 */
	public function sendMail()
	{
		Framework::info(__METHOD__." to : ".$this->getReceiver());

		$body = $this->getMimeObject()->get();
		$hdrs = $this->getMimeObject()->headers($this->getHeaders());

		if (class_exists('Mail'))
		{
			$mailObject = Mail::factory('smtp', $this->getFactoryParams());
		}
		else
		{
			throw new Exception("Class Mail not found");
		}
		return $mailObject->send($this->getAllRecipientEmail(), $hdrs, $body);
	}
}