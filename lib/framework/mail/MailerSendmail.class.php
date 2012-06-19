<?php
/**
 * @deprecated
 */
class MailerSendmail extends Mailer
{
	
	/**
	 * @deprecated
	 */
	public function __construct($params)
	{
		// Set mail driver
		$this->mailDriver = strtolower($params['type']);
		if (isset($params['sendmail_path']))
		{
			$this->factoryParams['sendmail_path'] = $params['sendmail_path'];
		}
		if (isset($params['sendmail_args']))
		{
			$this->factoryParams['sendmail_args'] = $params['sendmail_args'];
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
		$body = $this->getMimeObject()->get();
		$hdrs = $this->getMimeObject()->headers($this->getHeaders());
		if (class_exists('Mail'))
		{
			$mailObject = Mail::factory('sendmail', $this->getFactoryParams());
		}
		else
		{
			throw new Exception("Class Mail not found");
		}
		return $mailObject->send($this->getAllRecipientEmail(), $hdrs, $body);
	}	
}