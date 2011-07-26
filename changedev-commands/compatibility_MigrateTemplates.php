<?php
/**
 * commands_compatibility_MigrateTemplates
 * @package modules.compatibility.command
 * 
 * i18n:translate i18n:attributes
 * change:translate change:i18nattr
 * change:id change:price change:select
 * change:image change:icon change:webappimage
 * change:date change:datetime 
 * change:currentPageLink -> change:currentpagelink
 * 
 * //DELETED
 * change:edit change:docattr  change:create change:propattr 
 */
class commands_compatibility_MigrateTemplates extends commands_AbstractChangeCommand
{
	/**
	 * @return String
	 */
	public function getUsage()
	{
		return "[module1 module1 ... module1]";
	}
	
	/**
	 * @return String
	 * @example "initialize a document"
	 */
	public function getDescription()
	{
		return "Migrate PHPTAL module templates";
	}
	
	/**
	 * This method is used to handle auto-completion for this command.
	 * @param Integer $completeParamCount the parameters that are already complete in the command line
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @return String[] or null
	 */
	public function getParameters($completeParamCount, $params, $options, $current)
	{
		$components = array();
		foreach (glob(WEBEDIT_HOME. "/modules/*/templates", GLOB_ONLYDIR) as $path)
		{
			$module = dirname($path);
			$components[] = basename($module);
		}	
		
		return $components;
	}
		
	private $tempTalPath;
	
	private $themes = array();
	
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== MigrateTemplates ==");
		$this->loadFramework();
		
		$allPackages = ModuleService::getInstance()->getPackageNames();
		
		if ( f_util_ArrayUtils::isEmpty($params))
		{
			$packages = $allPackages;
		}
		else
		{
			foreach ($params as $moduleName) 
			{
				if (in_array('modules_' .$moduleName, $allPackages))
				{
					$packages[] = 'modules_' .$moduleName;
				}
				elseif (in_array($moduleName, $allPackages))
				{
					$packages[] = $moduleName;
				}
			}
		}
		
		if (! PHPTAL_Dom_Defs::getInstance()->isHandledNamespace(
				PHPTAL_Namespace_CHANGE::NAMESPACE_URI))
		{
			spl_autoload_unregister(array('PHPTAL', 'autoload'));
			PHPTAL_Dom_Defs::getInstance()->registerNamespace(new PHPTAL_Namespace_CHANGE());
			$registry = PHPTAL_TalesRegistry::getInstance();
			foreach (Framework::getConfigurationValue('tal/prefix') as $prefix => $class)
			{
				$registry->registerPrefix($prefix, array($class, $prefix));
			}
		}
		$this->tempTalPath = PHPTAL_PHP_CODE_DESTINATION . 'xx';
		f_util_FileUtils::mkdir($this->tempTalPath);
		$this->themes = $this->getThemes();
		
		foreach ($packages as $packageName)
		{
			list (, $moduleName) = explode('_', $packageName);
			$paths = $this->scanModule($moduleName);
			$this->errors = array();
			foreach ($paths as $fullpath)
			{
				$migratedTemplate = $fullpath . '.mig';
				if (file_exists($migratedTemplate)) {unlink($migratedTemplate);}
				echo "Migrate: ", $fullpath, "\n";
				$this->migrateTemplate($fullpath, $this->tempTalPath);
				if (file_exists($migratedTemplate))
				{
					if ($this->testFile($migratedTemplate))
					{
						rename($fullpath, $fullpath . '.old');
						rename($migratedTemplate, $fullpath);
						echo "\t-> Migrated Successfully\n";
					}
				}
				else
				{
					if ($this->testFile($fullpath))
					{
						echo "\t-> OK\n";
					}
				}
			}
		}
		
		// Put your code here!
		

		$this->quitOk("Command successfully executed");
	}
	
	
	private function getThemes()
	{
		$result = array();
		$array = glob(f_util_FileUtils::buildWebeditPath('themes', '*', 'install.xml'));
		if (is_array($array))
		{
			foreach ($array as $path) 
			{
				$result[] = basename(dirname($path));
			}
		}
		return $result;
	}
	
	private function scanModule($moduleName)
	{
		$baseTemplatePath = f_util_FileUtils::buildWebeditPath('modules', $moduleName, 'templates');
		$paths = $this->scanDir($baseTemplatePath);
		
		$baseTemplatePath = f_util_FileUtils::buildOverridePath('modules', $moduleName, 'templates');
		$paths = array_merge($paths, $this->scanDir($baseTemplatePath));
		
		$baseTemplatePath = f_util_FileUtils::buildWebeditPath('modules', $moduleName, 'lib', 'bindings');
		$paths = array_merge($paths, $this->scanDir($baseTemplatePath));
		
		$baseTemplatePath = f_util_FileUtils::buildOverridePath('modules', $moduleName, 'lib', 'bindings');
		$paths = array_merge($paths, $this->scanDir($baseTemplatePath));
		
		foreach ($this->themes as $theme) 
		{
			$baseTemplatePath = f_util_FileUtils::buildWebeditPath('themes', $theme, 'modules', $moduleName, 'templates');
			$paths = array_merge($paths, $this->scanDir($baseTemplatePath));
			
			$baseTemplatePath = f_util_FileUtils::buildOverridePath('themes', $theme, 'modules', $moduleName, 'templates');
			$paths = array_merge($paths, $this->scanDir($baseTemplatePath));
		}
		
		return $paths;
	}
	
	private function scanDir($basePath)
	{
		$paths = array();
		if (! is_dir($basePath) || ! is_readable($basePath))
		{
			return $paths;
		}
		
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));
		foreach ($objects as $name => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			if (! $splFileInfo->isDir())
			{
				$ext = end(explode('.', $splFileInfo->getBasename()));
				if ($ext === 'html' || $ext === 'xml' || $ext === 'xul')
				{
					$paths[] = $splFileInfo->getPathname();
				}
			}
		}
		return $paths;
	}
	
	private function testFile($fullpath)
	{
		try
		{
			echo "\tCheck TAL parsing: ", basename($fullpath), "\n";
			
			$phptal = new PHPTAL($fullpath);
			$phptal->setPhpCodeDestination($this->tempTalPath);
			$ext = end(explode('.', $fullpath));
			if ($ext === K::XUL || $ext === K::XML)
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
			$log_exception = $e;
		}
		
		// Takes exception from either of the two catch blocks above
		echo "\t\tError line: ", $log_exception->getLine(), ', ' , $log_exception->getMessage(), "\n";
		return false;
	}
	
	function migrateTemplate($fullpath)
	{
		$updates = array();
		if (is_writeable($fullpath))
		{
			$lines = file($fullpath);
			$count = count($lines);
			for($i = 0; $i < $count; $i ++)
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
				file_put_contents($fullpath. '.mig', implode('', $lines));
			}
		}
		else
		{
			echo $fullpath, " is not writeable\n";
		}
	}
	
	function migrateTemplateLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();

		if (preg_match_all('/change:select="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			echo "\t\tDeprecated change:select line: ", $lineNumber, "\n";
			$result = $line;
		}
		if (preg_match_all('/change:price="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			echo "\t\tDeprecated change:price line: ", $lineNumber, "\n";
			$result = $line;
		}
		if (preg_match_all('/change:id="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			echo "\t\tDeprecated change:id line: ", $lineNumber, "\n";
			$result = $line;
		}
		if (preg_match_all('/change:date="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			echo "\t\tDeprecated change:date line: ", $lineNumber, "\n";
			$result = $line;
		}
		if (preg_match_all('/change:datetime="([^"]*)"/', $line, $matches, PREG_SET_ORDER))
		{
			echo "\t\tDeprecated change:datetime line: ", $lineNumber, "\n";
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
		
		return $result;
	}
	
	function convertWebappimage($match)
	{
		$expressions = $this->splitExpression($match[1]);
		$name = 'blank.gif';
		$folder = 'front';
		// foreach attribute
		foreach ($expressions as $exp)
		{
			list($attribute, $value) = $this->parseSetExpression($exp);
			switch ($attribute)
			{
				case 'name':
					$name = $value;
					break;
				case 'document':
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
		
		return 'change:img="'. str_replace('//', '/', $name) . '"';
	}
	
	function convertImage($match)
	{
		$exp = trim($match[1]);
		$p = strpos($exp, ' ');
		if ($p !== false)
		{
			$properties = explode(' ', trim(substr($exp, $p+1)));
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
			return 'change:img="back/' .  substr($name, 5 + $pos) . '"';
		}
		return 'change:img="'. str_replace('//', '/', 'back/' . $name) . '"';
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
		
		$imageAttr = 'icon/'. $size . $layout . $icon . '.png';
		return 'change:img="'. $imageAttr . '"';
	}
	

	function convertI18nAttributes($match)
	{
		$result = array();
		$expression = str_replace('&amp;', '&', $match[1]);
        $attributes = explode(' ', $expression);        
        $substitutions = array();
        for ($i = 0; isset($attributes[$i+1]); $i += 2)
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
        		$value = str_replace(';', '', $attributes[$i+1]);
        		$substitutions[$paramName][$matches[2]] = $value;
        		continue;
        	}
        	
        	$baseTrans = $this->convertKey($attributes[$i+1]);
        	if (isset($substitutions[$name]))
        	{
        		foreach ($substitutions[$name] as $n => $v) 
        		{
        			$baseTrans .= ',attr,' . $n .'=' . $v;
        		}
        	}
        	else
        	{
        		$baseTrans .= ',attr';
        	}
        	$result[] = $name . '="${' . $baseTrans . '}"';
        }
        return  implode(' ', $result);	
	}
	function convertI18nTranslate($match)
	{
		$parts = $this->splitExpression($match[1]);	
		$baseTrans = $this->convertKey($parts[0]);
		if (strpos($baseTrans, 'string' === 0))
		{
			return 'tal:content="' . $baseTrans . '"';
		}
		else
		{
			$extends = array();
			for($r = 1; $r < count($parts); $r ++)
			{
				list ($name, $value) = $this->parseSetExpression($parts[$r]);
				if ($name === 'ui')
				{
					if ($value === 'true')
					{
						$trans = 'transui:';
					}
				}
				else if ($value !== null)
				{
					$extends[] = $name . '=' . $value;
				}
			}
			if (count($extends))
			{
				$baseTrans .= ',' . implode(',', $extends);
			}
		}
		
		return 'tal:content="' . $baseTrans . '"';
	}
	
	private function convertKey($key)
	{
		$key = str_replace(array('&amp;', '&modules.', '&framework.', '&themes.', ';'), 
				array('&', 'm.', 'f.', 't.', ''), $key);
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
		$first = current($keyPart);
		$last = end($keyPart);
		if ($keyPartCount > 1 && in_array($first, array('m', 'f', 't')))
		{
			$trans = 'trans:';
			$extends = array();
			
			if (preg_match('/^[A-Z][a-z-]+/', $last))
			{
				$extends[] = 'ucf';
			}
			if (preg_match('/[a-z0-9]+label$/i', $last))
			{
				$extends[] = 'lab';
				$keyPart[$keyPartCount - 1] = substr($last, 0, strlen($last) - 5);
			}
			if (preg_match('/[a-z0-9]+ellipsis$/i', $last))
			{
				$extends[] = 'etc';
				$keyPart[$keyPartCount - 1] = substr($last, 0, strlen($last) - 8);
			}
			if (preg_match('/^[A-Z][A-Z]+/', $last))
			{
				$extends[] = 'uc';
			}
			return $trans . strtolower(implode('.', $keyPart)) . (count($extends) ? ',' . implode(',', $extends) : '');
		}
		return 'string:' . $last;
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
			$a = str_replace(';;', ';', $a);
		return $array;
	}
}