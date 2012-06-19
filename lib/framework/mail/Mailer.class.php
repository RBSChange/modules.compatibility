<?php
/**
 * @deprecated
 */
abstract class Mailer
{

	protected $mailDriver = null;
	protected $mimeObject = null;

	protected $factoryParams = array();
	protected $mailerParams = array();
	
	
	/**
	 * @var Array
	 */
	private $mailHeaders = array();
	
	/**
	 * @var String
	 */
	private $sender;

	/**
	 * @var String
	 */
	private $replyTo;
	
	/**
	 * @var String
	 */
	private $bcc;
	
	/**
	 * @var String
	 */
	private $cc;
	
	/**
	 * @var String
	 */
	private $subject;
	
	/**
	 * @var String
	 */
	private $receiver;
	
	/**
	 * @var String
	 */
	private $bounceBackAddress;
	
	/**
	 * @deprecated
	 */
	public abstract function __construct($params);
	
	/**
	 * @deprecated
	 */
	public abstract function sendMail();
	
	/**
	 * @deprecated
	 */
	public function getMimeObject()
	{
		if (is_null($this->mimeObject))
		{
			if (class_exists('Mail_mime'))
			{
				$this->mimeObject = new Mail_mime("\n");
			}
		    else
		    {
		    	throw new Exception("Class Mail_mime not found");
		    }
		}
		return $this->mimeObject;
	}

	/**
	 * @deprecated
	 */
	public function setEncoding($encoding)
	{
	    $this->getMimeObject()->_build_params['html_charset'] = $encoding;
        $this->getMimeObject()->_build_params['text_charset'] = $encoding;
        $this->getMimeObject()->_build_params['head_charset'] = $encoding;
	}


	/**
	 * @deprecated
	 */
	public function setHtmlAndTextBody($htmlBody, $textBody = null)
	{
		$this->getMimeObject()->setHtmlBody($htmlBody);
		if (is_null($textBody))
		{
		    $textBody = f_util_HtmlUtils::htmlToText($htmlBody);
		}
		$this->getMimeObject()->setTxtBody($textBody);
	}
	
	/**
	 * @deprecated
	 */
	public function getMessage()
	{
		return $this->getMimeObject()->getMessage();
	}
	
	/**
	 * @deprecated
	 */
	public function addAttachment($attachment)
	{
		$this->getMimeObject()->addAttachment($attachment);
	}
	
	/**
	 * @deprecated
	 */
	public function getHeaders()
	{
		return $this->mailHeaders;
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
	public function setBcc($bcc)
	{
		$this->setHeader('Bcc', $bcc);
		$this->bcc = $bcc;
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
	public function setBounceBackAddress($bounceBackAddress)
	{
		$this->setHeader('Return-Path', $bounceBackAddress);
		$this->bounceBackAddress = $bounceBackAddress;
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
	public function setCc($cc)
	{
		$this->setHeader('Cc', $cc);
		$this->cc = $cc;
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
	public function setReceiver($receiver)
	{
		$this->setHeader('To', $receiver);
		$this->receiver = $receiver;
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
	public function setReplyTo($replyTo)
	{
		$this->setHeader('Reply-To', $replyTo);
		$this->replyTo = $replyTo;
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
	public function setSender($sender)
	{
		$this->setHeader('From', $sender);
		$this->sender = $sender;
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
	public function setSubject($subject)
	{
		$this->setHeader('Subject', $subject);
		$this->subject = $subject;
	}
	
	/**
	 * @deprecated
	 */
	protected function getAllRecipientEmail()
	{
		$emails = $this->getReceiver();

		// Get cc emails
		if ( $this->getCc() )
		{
			$emails .= ',' . $this->getCc();
		}

		// Get bcc emails
		if ( $this->getBcc() )
		{
			$emails .= ',' . $this->getBcc();
		}

		return $emails;
	}
	
	public function setHeader($name, $value)
	{
		if ($value === null)
		{
			unset($this->mailHeaders[$name]);
		}
		else
		{
			$this->mailHeaders[$name] = $value;
		}
	}
}