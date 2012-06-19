<?php
/**
 * @deprecated
 */
class mail_MessageRecipients
{
	/**
	 * @var array
	 */
	private $to = null;

	/**
	 * @var array
	 */
	private $cc = null;

	/**
	 * @var array
	 */
	private $bcc = null;

	/**
	 * @deprecated
	 */
	public function __construct($to = null, $cc = null, $bcc = null)
	{
		if ($to !== null)
		{
			$this->setTo($to);
		}
		if ($cc !== null)
		{
			$this->setCC($cc);
		}
		if ($bcc !== null)
		{
			$this->setBCC($bcc);
		}
	}

	/**
	 * @deprecated
	 */
	public function setTo($to)
	{
		$this->to = $this->fixValue($to);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function setCC($cc)
	{
		$this->cc = $this->fixValue($cc);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function setBCC($bcc)
	{
		$this->bcc = $this->fixValue($bcc);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @deprecated
	 */
	public function getCC()
	{
		return $this->cc;
	}

	/**
	 * @deprecated
	 */
	public function getBCC()
	{
		return $this->bcc;
	}

	/**
	 * @deprecated
	 */
	public function hasTo()
	{
		return $this->getTo() !== null;
	}

	/**
	 * @deprecated
	 */
	public function hasCC()
	{
		return $this->getCC() !== null;
	}

	/**
	 * @deprecated
	 */
	public function hasBCC()
	{
		return $this->getBCC() !== null;
	}

	/**
	 * @deprecated
	 */
	private function fixValue($value)
	{
		if (is_string($value))
		{
			$trimValue = trim($value);
			if (f_util_StringUtils::isEmpty($trimValue))
			{
				return null;
			}
			if (strpos($trimValue, ',') !== false)
			{
				return $this->fixValue(explode(',', $trimValue));
			}
			else
			{
				return array($trimValue);
			}
		}
		else if (empty($value))
		{
			return null;
		}
		else if (is_array($value))
		{
			$values = array();			
			foreach ($value as $email) 
			{
				$trimValue = trim($email);
				if (!f_util_StringUtils::isEmpty($trimValue))
				{
					$values[] = $trimValue;			
				}
			}
			if (count($values) > 0)
			{
				return $values;
			}
			return null;
		}
		throw new IllegalArgumentException('$value must be a string containing email addresses or an array of email addresses.');
	}

	/**
	 * @deprecated
	 */
	public final function isEmpty()
	{
		return !$this->hasTo() && !$this->hasCC() && !$this->hasBCC();
	}

	/**
	 * @deprecated
	 */
	public function getAsRecipientsArray()
	{
		$toArray = array();
		$ccArray = array();
		$bccArray = array();
		foreach ($this->getTo() as $string)
		{
			$normalizedData = $this->extractData($string);
			if ($normalizedData !== null)
			{
				$toArray = array_merge($toArray, $normalizedData);
			}
		}
		foreach ($this->getCC() as $string)
		{
			$normalizedData = $this->extractData($string);
			if ($normalizedData !== null)
			{
				$ccArray = array_merge($ccArray, $normalizedData);
			}
		}
		foreach ($this->getBCC() as $string)
		{
			$normalizedData = $this->extractData($string);
			if ($normalizedData !== null)
			{
				$bccArray = array_merge($ccArray, $normalizedData);
			}
		}
		return change_MailService::getInstance()->getRecipientsArray($toArray, $ccArray, $bccArray);
	}
	
	private function extractData($string)
	{
		$emailValidator = new Zend_Validate_EmailAddress();
		if ($emailValidator->isValid($string))
		{
			return array($string);
		}
		return null;
	}
}