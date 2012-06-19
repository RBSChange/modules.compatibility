<?php 

/**
 * @deprecated
 */
class NoUserForWorkitemException extends BaseException
{
	public function __construct ($argumentName)
	{
		$key = 'framework.exception.errors.No-valid-user-found-for-this-workitem';
		parent::__construct($argumentName, $key);
	}	
}

/**
 * @deprecated
 */
class ClassException extends BaseException
{
}

/**
 * @deprecated
 */
class FrameworkException extends BaseException
{
}

/**
 * @deprecated
 */
class ListNotFoundException extends Exception
{
}

/**
 * @deprecated
 */
class DataobjectException extends BaseException
{
}

/**
 * @deprecated
 */
class FunctionNotFoundException extends Exception
{
}

/**
 * @deprecated
 */
class InvalidComponentTypeException extends BaseException
{
}

/**
 * @deprecated
 */
class MalformedURLException extends Exception
{
	public function __construct($url)
	{
		parent::__construct('Invalid URL: "'.$url.'".');
	}
}

/**
 * @deprecated
 */
class PearException extends BaseException
{

	public function __construct($pear_error)
	{
		$key = 'framework.exception.errors.pear-exception';
		$attributes = array("message" =>$pear_error->message,
							"code" => $pear_error->code, 
							"mode" => $pear_error->mode,
							"user_info" => $pear_error->userinfo);
		
		parent::__construct("pear-exception", $key, $attributes);
	}
}

/**
 * @deprecated
 */
class RecursivityException extends BaseException
{

	public function __construct ($message = "")
	{
		$key = 'framework.exception.errors.recursivity-exception';
		$attributes = array("message" => $message);		
		parent::__construct("recursivity-exception", $key, $attributes);
	}
}

/**
 * @deprecated
 */
class ServiceException extends Exception 
{
	
}

/**
 * @deprecated
 */
class SessionExpiredException extends Exception
{
	public function __construct()
	{
		parent::__construct('Your session has expired.');
	}
}

/**
 * @deprecated
 */
class TranslationKeyNotFoundException extends BaseException
{
	public function __construct($translationKey)
	{
		$key = 'framework.exception.errors.Translation-key-not-found';
		$attributes = array("key" => $translationKey);
		parent::__construct("translation-key-not-found", $key, $attributes);
	}
}

/**
 * @deprecated
 */
class UnexpectedExclusiveTagException extends TagException
{
	public function __construct($tagName)
	{
		parent::__construct('Unexpected exclusive tag: '.$tagName);
	}
}

/**
 * @deprecated
 */
class UserNotFoundException extends Exception
{
}

/**
 * @deprecated
 */
class UnimplementedMethodException extends Exception 
{	
}

/**
 * @deprecated
 */
class ExtendedAgaviException
{

	private	$id = null;

	public function __construct ($message = null, $code = 0)
	{
		parent::__construct($message, $code);

		$this->setName('ExtendedAgaviException');

	}

	public function getId()
	{
		return $this->id;
	}

	// -------------------------------------------------------------------------

	/**
	 * @deprecated
	 */
	public function printStackTrace ($format = 'html')
	{
		f_util_ProcessUtils::printBackTrace($format == 'html');
	}

	// -------------------------------------------------------------------------

	/**
	 * @deprecated
	 */
	protected function setName ($name)
	{
		parent::setName($name);
		$this->id = $name;
	}
}