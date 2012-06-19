<?php
/**
 * @deprecated
 */
class MailerNull extends Mailer
{

	/**
	 * @deprecated
	 */
	public function __construct($params)
	{
		
	}

	/**
	 * @deprecated
	 */
	public function getFactoryParams()
	{
		return array();
	}

	/**
	 * @deprecated
	 */
	public function sendMail()
	{
		return true;
	}

}

/**
 * @deprecated
 */
class NullMailService extends MailService
{

	/**
	 * @deprecated
	 */
	protected function buildMailer($mailMessage)
	{
		if (!Framework::isDebugEnabled())
		{
			Framework::warn("You are currently using NullMailService - this should not be the case outside of DEBUG mode");
		}
		else
		{
			Framework::debug(__METHOD__);
			Framework::debug("Mail sender : " . $mailMessage->getSender());
			Framework::debug("Mail receiver : " . $mailMessage->getReceiver());
			Framework::debug("Mail reply to : " . $mailMessage->getReplyTo());
			Framework::debug("Mail subject : " . $mailMessage->getSubject());
			Framework::debug("Mail Html : " . $mailMessage->getHtmlContent());
			Framework::debug("Mail Text : " . $mailMessage->getTextContent());
		}
		return new MailerNull(array());
	}
}