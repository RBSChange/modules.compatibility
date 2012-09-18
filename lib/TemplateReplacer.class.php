<?php
class compatibility_TemplateReplacer
{
	/**
	 *
	 * @var string
	 */
	private $tempTalPath;
	
	/**
	 *
	 * @var compatibility_Logger
	 */
	private $logger = null;
	
	/**
	 *
	 * @var string
	 */
	private $logPrefix = 'Fix: ';
	
	/**
	 *
	 * @var string
	 */
	private $currentPath = null;
	
	/**
	 * 
	 * @param unknown_type $tempTalPath
	 * @param unknown_type $logger
	 */
	public function __construct($tempTalPath, $logger)
	{
		$this->tempTalPath = $tempTalPath;
		$this->logger = $logger;
	}
	
	public function migrateTemplate($fullpath)
	{
		$this->currentPath = $fullpath;
		$updates = array();
		$lines = file($fullpath);
		$count = count($lines);
		for ($i = 0; $i < $count; $i++)
		{
			$update = $this->migrateTemplateLine($lines[$i], $i + 1);
			if ($update !== false)
			{
				$updates[$i] = array($lines[$i], $update);
				$lines[$i] = $update;
			}
		}
		
		if (count($updates))
		{
			$this->logger->logInfo('Template Updated: ' . $this->currentPath);
			file_put_contents($this->currentPath, implode('', $lines));
		}
		
		return $this->testFile();
	}
	
	private function testFile()
	{
		try
		{
			$phptal = new PHPTAL($this->currentPath);
			$phptal->setPhpCodeDestination($this->tempTalPath);
			$array = explode('.', $this->currentPath);
			$ext = end($array);
			if ($ext === 'xml')
			{
				$phptal->set('HOST', Framework::getUIBaseUrl());
				$phptal->setOutputMode(PHPTAL::XML);
			}
			else
			{
				$phptal->set('HOST', Framework::getBaseUrl());
				$phptal->setOutputMode(PHPTAL::XHTML);
			}
			$phptal->set('UIHOST', Framework::getUIBaseUrl());
			
			$phptal->setForceReparse(true);
			$phptal->prepare();
			return true;
		}
		catch (Exception $e)
		{
			$this->logger->logError("Error on check template: " . $this->currentPath);
			$log_exception = $e;
		}
		$this->logger->logError("Error line: " . $log_exception->getLine() . ', ' . $log_exception->getMessage());
		return false;
	}
	

	function migrateTemplateLine($line, $lineNumber)
	{
		// echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		if (preg_match_all('/change:price="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			$this->logger->logWarn("Deprecated change:price file: " . $this->currentPath . " line: ". $lineNumber);
			$result = $line;
		}
		
		if (preg_match_all('/change:id="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			$this->logger->logWarn("Deprecated change:id file: " . $this->currentPath . " line: ". $lineNumber);
			$result = $line;
		}
		
		if (preg_match_all('/\$\{escape:/', $line, $matches, PREG_SET_ORDER))
		{
			$line = str_replace(array('${escape: ', '${escape:'), '${', $line);
			$result = $line;
		}
		
		if (preg_match_all('/="([a-z0-9A-Z\/]+get[a-z0-9A-Z]+)AsHtml"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], '="structure ' . $match[1] . 'AsHtml"', $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/\$\{([a-z0-9A-Z\/]+get[a-z0-9A-Z]+)AsHtml\}/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], '${structure ' . $match[1] . 'AsHtml}', $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/change:date="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertDate($match), $line);
			}
			$result = $line;
		}
		if (preg_match_all('/change:datetime="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertDateTime($match), $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/change:webappimage="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertWebappimage($match), $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/change:icon="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertIcon($match), $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/change:image="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertImage($match), $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/change:currentPageLink="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace('change:currentPageLink', 'change:currentpagelink', $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/i18n:translate="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertI18nTranslate($match), $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/change:translate="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertI18nTranslate($match), $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/i18n:attributes="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertI18nAttributes($match), $line);
			}
			$result = $line;
		}
		
		if (preg_match_all('/change:i18nattr="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$line = str_replace($match[0], $this->convertI18nAttributes($match), $line);
			}
			$result = $line;
		}
		
		$tmpResult = str_replace(
			array('<tal:block change:loadhandler="form_FormLoadHandler" />', "\r\n", "    "),
			array('<script change:javascript="head \'modules.form.lib.js.form\'"></script>', PHP_EOL, "\t"),
			$line
		);
		if ($tmpResult !== $line)
		{
			$line = $tmpResult;
			$result = $line;
		}
		
		return $result;
	}
	
	
	function convertDate($match)
	{
		$formatters = array('from=gmt');
		foreach ($this->splitExpression($match[1]) as $exp)
		{
			list ($n, $v) = $this->parseSetExpression($exp);
			if ($v === null)
			{
				$value = $n;
			}
			else if ($n == 'date' || $n == 'value')
			{
				$value = $v;
			}
			else if ($n == 'format')
			{
				if (!in_array(strtolower($v), array("'classic'", "null", "")))
				{
					$formatters[] = $v;
				}
			}
			else if ($n == 'formatI18n')
			{
				$if = array();
				$formatters[] = 'trans:' . $this->convertKey($v, $if);
			}
		}
		return 'tal:replace="date:' . $value . ',' . implode(',', $formatters) . '"';
	}
	
	
	function convertDateTime($match)
	{
		$formatters = array('from=gmt');
		foreach ($this->splitExpression($match[1]) as $exp)
		{
			list ($n, $v) = $this->parseSetExpression($exp);
			if ($v === null)
			{
				$value = $n;
			}
			else if ($n == 'date' || $n == 'value')
			{
				$value = $v;
			}
			else if ($n == 'format')
			{
				if (!in_array(strtolower($v), array("'classic'", "null", "")))
				{
					$formatters[] = $v;
				}
			}
			else if ($n == 'formatI18n')
			{
				$if = array();
				$formatters[] = 'trans:' . $this->convertKey($v, $if);
			}
		}
		return 'tal:replace="datetime:' . $value . ',' . implode(',', $formatters) . '"';
	}
	
	function convertWebappimage($match)
	{
		$expressions = $this->splitExpression($match[1]);
		$name = 'blank.gif';
		$folder = 'front';
		// foreach attribute
		foreach ($expressions as $exp)
		{
			list ($attribute, $value) = $this->parseSetExpression($exp);
			switch ($attribute)
			{
				case 'name' :
					$name = $value;
					break;
				case 'document' :
					$folder = $value;
					break;
			}
		}
		if ($folder == 'front')
		{
			$name = 'front/' . $name;
		}
		elseif ($folder == 'back')
		{
			$name = 'back/' . $name;
		}
		
		return 'change:img="' . str_replace('//', '/', $name) . '"';
	}
	
	
	function convertImage($match)
	{
		$exp = trim($match[1]);
		$p = strpos($exp, ' ');
		if ($p !== false)
		{
			$properties = explode(' ', trim(substr($exp, $p + 1)));
		}
		else
		{
			$properties = explode(' ', $exp);
		}
		$name = $properties[0];
		$pos = strpos($name, 'front/');
		if ($pos === 0 || $pos === 1)
		{
			return 'change:img="front/' . substr($name, 6 + $pos) . '"';
		}
		$pos = strpos($name, 'back/');
		if ($pos === 0 || $pos === 1)
		{
			return 'change:img="back/' . substr($name, 5 + $pos) . '"';
		}
		return 'change:img="' . str_replace('//', '/', 'back/' . $name) . '"';
	}
	
	
	function convertIcon($match)
	{
		$exp = trim($match[1]);
		$expArray = explode(' ', $exp);
		$size = 'normal';
		$icon = 'unknown';
		$layout = '/';
		switch (count($expArray))
		{
			case 1 :
				list ($icon, $size) = explode('/', $expArray[0]);
				break;
			case 2 :
				if ($expArray[1] == 'shadow')
				{
					list ($icon, $size) = explode('/', $expArray[0]);
					$layout = '/shadow/';
				}
				else
				{
					list ($icon, $size) = explode('/', $expArray[1]);
				}
				break;
			default :
				if (isset($expArray[1]))
				{
					list ($icon, $size) = explode('/', $expArray[1]);
				}
				$layout = '/shadow/';
				break;
		}
		
		$imageAttr = 'icon/' . $size . $layout . $icon . '.png';
		return 'change:img="' . $imageAttr . '"';
	}
	
	
	function convertI18nAttributes($match)
	{
		$result = array();
		$expression = str_replace(array('&amp;', ';;'), array('&', ';'), $match[1]);
		$attributes = explode(' ', $expression);
		$substitutions = array();
		for ($i = 0; isset($attributes[$i + 1]); $i += 2)
		{
			$name = $attributes[$i];
			$matches = array();
			if (preg_match('/^([a-z]+)\[([a-zA-Z]+)\]$/', $name, $matches))
			{
				$paramName = $matches[1];
				if (!isset($substitutions[$paramName]))
				{
					$substitutions[$paramName] = array();
				}
				$value = str_replace(';', '', $attributes[$i + 1]);
				$substitutions[$paramName][$matches[2]] = $value;
				continue;
			}
			$formatters = array('attr');
			$key = $this->convertKey($attributes[$i + 1], $formatters);
			if (isset($substitutions[$name]))
			{
				foreach ($substitutions[$name] as $n => $v)
				{
					$formatters[] = $n . '=' . $v;
				}
			}
			$strFormatters = (count($formatters)) ? ',' . implode(',', $formatters) :  '';
			$result[] = $name . '="${trans:' . $key . $strFormatters . '}"';
		}
		return implode(' ', $result);
	}
	
	function convertI18nTranslate($match)
	{
		$expression = str_replace(array('&amp;', ';;'), array('&', ';'), $match[1]);
		$parts = $this->splitExpression($expression);
		$formatters = array();
		$key = $this->convertKey($parts[0], $formatters);
		$extends = array();
		for ($r = 1; $r < count($parts); $r++)
		{
			list ($name, $value) = $this->parseSetExpression($parts[$r]);
			if ($name !== 'ui' && $value !== null)
			{
				$formatters[] = $name . '=' . $value;
			}
		}
		$strFormatters = (count($formatters)) ? ',' . implode(',', $formatters) :  '';
		return 'tal:content="trans:' . $key . $strFormatters . '"';
	}
		
	protected function parseSetExpression($exp)
	{
		$exp = trim($exp);
		// (dest) (value)
		if (preg_match('/^([a-z0-9:\-_]+)\s+(.*?)$/si', $exp, $m))
		{
			return array($m[1], trim($m[2]));
		}
		// (dest)
		return array($exp, null);
	}
	
	
	public function splitExpression($src)
	{
		preg_match_all('/(?:[^;]+|;;)+/sm', $src, $array);
		$array = $array[0];
		foreach ($array as &$a)
		{
			$a = str_replace(';;', ';', $a);
		}
		return $array;
	}
	
	/**
	 * @param string $key
	 * @param string[] $formatters
	 * @return string clean key
	 */
	private function convertKey($key, &$formatters)
	{
		$key = str_replace(array('&modules.', '&framework.', '&themes.', ';'), array('m.', 'f.', 't.', ''), $key);
		$keyPart = explode('.', $key);
		if ($keyPart[0] === 'modules')
		{
			$keyPart[0] = 'm';
		}
		elseif ($keyPart[0] === 'framework')
		{
			$keyPart[0] = 'f';
		}
		elseif ($keyPart[0] === 'themes')
		{
			$keyPart[0] = 't';
		}
	
		$keyPartCount = count($keyPart);
		if ($keyPartCount > 1 && in_array($keyPart[0], array('m', 'f', 't')))
		{
			$keyPart[$keyPartCount-1] = $this->extractFormatterByKeyId($keyPart[$keyPartCount-1], $formatters);
			return strtolower(implode('.', $keyPart));
		}
		return $key;
	}
	
	/**
	 * @param string $keyId
	 * @param string[] $formatters
	 * @return string
	 */
	private function extractFormatterByKeyId($keyId, &$formatters)
	{
		if (preg_match('/^[A-Z][a-z-]+/', $keyId))
		{
			$formatters[] = 'ucf';
		}
		elseif (preg_match('/^[A-Z][A-Z]+/', $keyId))
		{
			$formatters[] = 'uc';
		}
	
		if (preg_match('/[a-zA-Z0-9-]+label$/', $keyId))
		{
			$formatters[] = 'lab';
			$keyId = substr($keyId, 0, strlen($keyId) - 5);
			if (preg_match('/[a-zA-Z0-9-]+mandatory$/', $keyId))
			{
				$keyId = substr($keyId, 0, strlen($keyId) - 9);
			}
		}
		elseif (preg_match('/[a-zA-Z0-9-]+mandatory$/', $keyId))
		{
			$keyId = substr($keyId, 0, strlen($keyId) - 9);
		}
		elseif (preg_match('/[a-zA-Z0-9-]+spaced$/', $keyId))
		{
			$formatters[] = 'space';
			$keyId = substr($keyId, 0, strlen($keyId) - 6);
		}
		elseif (preg_match('/[a-zA-Z0-9-]+ellipsis$/', $keyId))
		{
			$formatters[] = 'etc';
			$keyId = substr($keyId, 0, strlen($keyId) - 8);
		}
		return strtolower($keyId);
	}
}