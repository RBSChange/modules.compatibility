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
class ExtendedAgaviException extends AgaviException
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
	 * Print the stack trace for this exception.
	 *
	 * @param string The format you wish to use for printing. Options
	 *               include:
	 *               - html
	 *               - plain
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.9.0
	 */
	public function printStackTrace ($format = 'html')
	{
		if (function_exists('__agavi_printStackTrace')) {
			__agavi_printStackTrace($this, $format);
		}

		// exception related properties
		$class     = ($this->getFile() != null)
				     ? Toolkit::extractClassName($this->getFile()) : 'N/A';

		$class     = ($class != '')
				     ? $class : 'N/A';

		$code      = ($this->getCode() > 0)
				     ? $this->getCode() : 'N/A';

		$file      = ($this->getFile() != null)
				     ? $this->getFile() : 'N/A';

		$line      = ($this->getLine() != null)
				     ? $this->getLine() : 'N/A';

		$message   = ($this->getMessage() != null)
				     ? $this->getMessage() : 'N/A';

		$name      = $this->getName();

		$traceData = $this->getTrace();
		$trace     = array();

		// lower-case the format to avoid sensitivity issues
		$format = strtolower($format);

		if ($trace !== null && count($traceData) > 0)
		{

			// format the stack trace
			for ($i = 0, $z = count($traceData); $i < $z; $i++)
			{

				if (!isset($traceData[$i]['file']))
				{

				    // no file key exists, skip this index
				    continue;

				}

				// grab the class name from the file
				// (this only works with properly named classes)
				$tClass = Toolkit::extractClassName($traceData[$i]['file']);

				$tFile      = $traceData[$i]['file'];
				$tFunction  = $traceData[$i]['function'];
				$tLine      = $traceData[$i]['line'];

				if ($tClass != null)
				{

				    $tFunction = $tClass . '::' . $tFunction . '()';

				} else
				{

				    $tFunction = $tFunction . '()';

				}

				if ($format == 'html')
				{

				    $tFunction = '<strong>' . $tFunction . '</strong>';

				}

				$data = 'at %s in [%s:%s]';
				$data = sprintf($data, $tFunction, $tFile, $tLine);

				$trace[] = $data;

			}

		}

		switch ($format)
		{

			case 'html':

				// print the exception info
				echo '<!DOCTYPE html
				      PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
				      <html xmlns="http://www.w3.org/1999/xhtml"
						    xml:lang="en" lang="en">
				      <head>
				      <meta http-equiv="Content-Type"
						    content="text/html; charset=iso-8859-1"/>
				      <title>Agavi Exception</title>
				      <style type="text/css">

				      #exception {
						  background-color: #EEEEEE;
						  border:           solid 1px #750000;
						  font-family:      verdana, helvetica, sans-serif;
						  font-size:        76%;
						  font-style:       normal;
						  font-weight:      normal;
						  margin:           10px;
				      }

				      #help {
						  color:     #750000;
						  font-size: 0.9em;
				      }

				      .message {
						  color:       #FF0000;
						  font-weight: bold;
				      }

				      .title {
						  font-size:   1.1em;
						  font-weight: bold;
				      }

				      td {
						  background-color: #EEEEEE;
						  padding:          5px;
				      }

				      th {
						  background-color: #750000;
						  color:            #FFFFFF;
						  font-size:        1.2em;
						  font-weight:      bold;
						  padding:          5px;
						  text-align:       left;
				      }

				      </style>
				      </head>
				      <body>

				      <table id="exception" cellpadding="0" cellspacing="0">
						  <tr>
						      <th colspan="2">' . $name . '</th>
						  </tr>
						  <tr>
						      <td class="title">Message:</td>
						      <td class="message">' . $message . '</td>
						  </tr>
						  <tr>
						      <td class="title">Code:</td>
						      <td>' . $code . '</td>
						  </tr>
						  <tr>
						      <td class="title">Class:</td>
						      <td>' . $class . '</td>
						  </tr>
						  <tr>
						      <td class="title">File:</td>
						      <td>' . $file . '</td>
						  </tr>
						  <tr>
						      <td class="title">Line:</td>
						      <td>' . $line . '</td>
						  </tr>';

				if (count($trace) > 0)
				{

				    echo '<tr>
						      <th colspan="2">Stack Trace</th>
						  </tr>';

				    foreach ($trace as $line)
				    {

						echo '<tr>
							  <td colspan="2">' . $line . '</td>
						      </tr>';

				    }

				}

				echo     '<tr>
						      <th colspan="2">Info</th>
						  </tr>
						  <tr>
						      <td class="title">Agavi Version:</td>
						      <td>' . AG_APP_VERSION . '</td>
						  </tr>
						  <tr>
						      <td class="title">PHP Version:</td>
						      <td>' . PHP_VERSION . '</td>
						  </tr>
						  <tr id="help">
						      <td colspan="2">
							  For help resolving this issue, please visit
							  <a href="http://www.agavi.org">www.agavi.org</a>.
						      </td>
						  </tr>
				      </table>

				      </body>
				      </html>';

				break;
			case 'xml':
				$xml = array();
				$xml[] = '<exception>';
				$xml[] = '<status>EXCEPTION</status>';
				$xml[] = '<name>' . $name . '</name>';
				$xml[] = '<message>' . $message . '</message>';
				$xml[] = '<type>' . get_class($this) . '</type>';
				$xml[] = '<code>' . $code . '</code>';
				$xml[] = '<class>' . $class. '</class>';
				$xml[] = '<file>' . $file. '</file>';
				$xml[] = '<line>' . $line. '</line>';

				if (count($trace) > 0)
				{
					$xml[] = '<trace>';
				    foreach ($trace as $line)
				    {
						$xml[] = '<line>' . $line . '</line>';
				    }
					$xml[] = '</trace>';
				}
				$xml[] = '</exception>';
				header("Content-type: text/xml");
				echo join("\r\n", $xml);

			break;
			case 'plain':
			default:

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Set the name of this exception.
	 *
	 * @param string An exception name.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	protected function setName ($name)
	{

		parent::setName($name);
		$this->id = $name;

	}

}