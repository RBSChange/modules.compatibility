<?php
/**
 * @deprecated
 */
class MailMessage
{

	/**
	 * Email(s) of sender
	 * @var String
	 */
	private $sender = null;
	/**
	 * Email(s) for the reply to
	 * @var String
	 */
	private $replyTo = null;
	/**
	 * Email(s) of blind carbon copy
	 * @var String
	 */
	private $bcc = null;
	/**
	 * Email(s) of carbon copy
	 * @var String
	 */
	private $cc = null;
	/**
	 * Subject of message
	 * @var String
	 */
	private $subject = null;
	/**
	 * Email(s) of receiver
	 * @var String
	 */
	private $receiver = null;
	/**
	 * Encoding of content
	 * @var String
	 */
	private $encoding = 'utf-8';
	
	/**
	 * Html content
	 * @var String
	 */
	private $html = null;
	
	/**
	 * Text content
	 * @var String
	 */
	private $text = null;
	
	/**
	 * Module name of the module that construct the message
	 * @var String
	 */
	private $moduleName = null;
	/**
	 * Array of path for file attachment
	 * @var array
	 */
	private $attachment = array();

	private $source;
	
	private $notificationTo;

	/**
	 * @deprecated
	 */
	public function setSender($sender)
	{
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getSender()
	{
		return $this->sender;
	}

	/**
	 * @deprecated
	 */
	public function setReplyTo($replyTo)
	{
		$this->replyTo = $replyTo;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getReplyTo()
	{
		return $this->replyTo;
	}

	/**
	 * @deprecated
	 */
	public function setBcc($bcc)
	{
		$this->bcc = $bcc;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getBcc()
	{
		return $this->bcc;
	}

	/**
	 * @deprecated
	 */
	public function setCc($cc)
	{
		$this->cc = $cc;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getCc()
	{
		return $this->cc;
	}

	/**
	 * @deprecated
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @deprecated
	 */
	public function setReceiver($receiver)
	{
		$this->receiver = $receiver;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getReceiver()
	{
		return $this->receiver;
	}

	/**
	 * @deprecated
	 */
	public function addAttachment($attachment)
	{
		$this->attachment[] = $attachment;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getAttachment()
	{
		return $this->attachment;
	}

	/**
	 * @deprecated
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * @deprecated
	 */
	public function setModuleName($moduleName)
	{
		$this->moduleName = $moduleName;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * @deprecated
	 */
	public function setHtmlAndTextBody($htmlBody, $textBody = null)
	{
		$this->html = $htmlBody;
		$this->text = $textBody;
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getHtmlContent()
	{
		return f_util_StringUtils::addCrLfToHtml($this->html);
	}

	/**
	 * @deprecated
	 */
	public function getTextContent()
	{
		return $this->text;
	}
	
	private $bounceBackAddress;

	/**
	 * @deprecated
	 */
	public function setBounceBackAddress($address)
	{
		$this->bounceBackAddress = $address;
	}

	/**
	 * @deprecated
	 */
	public function getBounceBackAddress()
	{
		return $this->bounceBackAddress;
	}

	/**
	 * @deprecated
	 */
	public function send()
	{
		return MailService::getInstance()->send($this);
	}

	/**
	 * @deprecated
	 */
	public function setRecipients($recipients)
	{
		if ($recipients->hasTo())
		{
			$this->setReceiver(implode(',', $recipients->getTo()));
		}	
		if ($recipients->hasCC())
		{
			$this->setCc(implode(',', $recipients->getCC()));
		}
		if ($recipients->hasBCC())
		{
			$this->setBcc(implode(',', $recipients->getBCC()));
		}
	}

	/**
	 * @deprecated
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @deprecated
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}

	/**
	 * @deprecated
	 */
	public function hasSource()
	{
		return $this->source !== null;
	}

	/**
	 * @deprecated
	 */
	public function getNotificationTo()
	{
		return $this->notificationTo;
	}

	/**
	 * @deprecated
	 */
	public function setNotificationTo($notificationTo)
	{
		$this->notificationTo = $notificationTo;
	}

	/**
	 * @deprecated
	 */
	public function hasNotificationTo()
	{
		return $this->notificationTo !== null;
	}
}
