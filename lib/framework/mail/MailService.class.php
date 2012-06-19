<?php
/**
 * @deprecated
 */
class MailService extends change_BaseService
{

	/**
	 * @deprecated
	 */
	const CHANGE_SOURCE_ID_HEADER = 'X-Change-Source-Id';
	
	/**
	 * @deprecated
	 */
	public static function getInstance()
	{
		$finalClass = (defined("FAKE_EMAIL")) ? 'FakeMailService' : 'MailService';
		return self::getInstanceByClassName($finalClass);
	}

	/**
	 * @deprecated
	 */
	public function getNewMailMessage()
	{
		return new MailMessage();
	}

	/**
	 * @deprecated
	 */
	public function send($mailMessage)
	{
		$this->mailer = null;
		return $this->buildMailer($mailMessage)->sendMail();
	}

	/**
	 * @deprecated
	 */
	public function sendTo($mailMessage, $receiver)
	{
		$mailMessage->setReceiver($receiver);
		return $this->send($mailMessage);
	}
	
	/**
	 * @var Mailer
	 */
	private $mailer;

	/**
	 * @deprecated
	 */
	public function getMailer()
	{
		if ($this->mailer !== null)
		{
			return $this->mailer;
		}
		// Load configuration of mailer
		$mailConfiguration = Framework::getConfiguration('mail');
		
		// Instance the mailer
		$className = "Mailer" . ucfirst( strtolower( $mailConfiguration['type'] ) );
		$class = new ReflectionClass($className);
		$mailer = $class->newInstance($mailConfiguration);
		$this->mailer = $mailer;
		
		return $mailer;
	}
	
	/**
	 * @deprecated
	 */
	protected function buildMailer($mailMessage)
	{
		$mailer = $this->getMailer();
		
		// Pass the mailMessage to the mailer
		$mailer->setSender($mailMessage->getSender());
		$mailer->setReceiver($mailMessage->getReceiver());
		$mailer->setBcc($mailMessage->getBcc());
		$mailer->setCc($mailMessage->getCc());
		$mailer->setEncoding($mailMessage->getEncoding());
		$mailer->setHtmlAndTextBody($mailMessage->getHtmlContent(), $mailMessage->getTextContent());
		$mailer->setReplyTo($mailMessage->getReplyTo());
		$mailer->setSubject($mailMessage->getSubject());
		$mailer->setBounceBackAddress($mailMessage->getBounceBackAddress());
		
		// Accusé de reception
		if ($mailMessage->hasNotificationTo())
		{
			$mailer->setHeader('Disposition-Notification-To', $mailMessage->getNotificationTo());
		}
		
		foreach ($mailMessage->getAttachment() as $attachement)
		{
			$mailer->addAttachment($attachement);
		}
		return $mailer;
	}
}