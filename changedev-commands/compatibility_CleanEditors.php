<?php
/**
 * commands_compatibility_CleanEditors
 * @package modules.compatibility.command
 * 
 * resume.xml
 *  - clean keys in labeli18n and actiontexti18n attributes
 */
class commands_compatibility_CleanEditors extends commands_AbstractChangeCommand
{
	/**
	 * @return String
	 * @example "<moduleName> <name>"
	 */
	public function getUsage()
	{
		return "[module1 module2 ... moduleN]";
	}

	/**
	 * @return String
	 * @example "initialize a document"
	 */
	public function getDescription()
	{
		return "Clean editors";
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
		foreach (glob(PROJECT_HOME. "/modules/*/config", GLOB_ONLYDIR) as $path)
		{
			$module = dirname($path);
			$components[] = basename($module);
		}	
		
		return $components;
	}

	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== CleanEditors ==");

		$this->loadFramework();
		$allPackages = ModuleService::getInstance()->getPackageNames();
		if (f_util_ArrayUtils::isEmpty($params))
		{
			$packages = $allPackages;
		}
		else
		{
			$packages = array();
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
				
		foreach ($packages as $packageName)
		{
			list (, $moduleName) = explode('_', $packageName);
			echo 'START module ', $moduleName, "\n";
			
			foreach ($this->scanModule($moduleName) as $editorPath)
			{
				echo '  Editor path: ', $editorPath, "\n";
				
				// Clean resume.xml
				$filePath = f_util_FileUtils::buildAbsolutePath($editorPath, 'resume.xml');
				if (file_exists($filePath))
				{
					$this->handleFile($filePath, 'migrateResumeLine');
				}
				else
				{
					echo ' - No resume.xml', "\n";
				}
				
				// Clean panels.xml
				$filePath = f_util_FileUtils::buildAbsolutePath($editorPath, 'panels.xml');
				if (file_exists($filePath))
				{
					$this->handleFile($filePath, 'migratePanelsLine');
				}
				else
				{
					echo ' - No panels.xml', "\n";
				}
				
				// Clean properties.xml
				$filePath = f_util_FileUtils::buildAbsolutePath($editorPath, 'properties.xml');
				if (file_exists($filePath))
				{
					$this->handleFile($filePath, 'migrateFieldpanelLine');
				}
				else
				{
					echo ' - No properties.xml', "\n";
				}
				
				// Clean create.xml
				$filePath = f_util_FileUtils::buildAbsolutePath($editorPath, 'create.xml');
				if (file_exists($filePath))
				{
					$this->handleFile($filePath, 'migrateFieldpanelLine');
				}
				else
				{
					echo ' - No create.xml', "\n";
				}
				
				// Clean localization.xml
				$filePath = f_util_FileUtils::buildAbsolutePath($editorPath, 'localization.xml');
				if (file_exists($filePath))
				{
					$this->handleFile($filePath, 'migrateFieldpanelLine');
				}
				else
				{
					echo ' - No localization.xml', "\n";
				}
			}
			
			echo 'END module ', $moduleName, "\n";
		}

		$this->quitOk("Command successfully executed");
	}
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migrateResumeLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		$line = $this->migrateI18nAttributes($line, $result, array('labeli18n', 'actiontexti18n'));
		
		return $result;
	}	
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migratePanelsLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		$line = $this->migrateI18nAttributes($line, $result, array('labeli18n'));
		
		$line = $this->migrateLocalesInJs($line, $result);
		
		return $result;
	}	
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migrateFieldpanelLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		$line = $this->migrateI18nAttributes($line, $result, array('labeli18n', 'shorthelpi18n'));
		
		if (preg_match('/( *)shorthelp="&amp;([^";]*);"( *)/', $line, $matches))
		{
			$key = $this->convertKey($matches[2]);
			$replacement = $matches[1]  . 'shorthelpi18n="'.$key.'"' . $matches[3];
			$line = str_replace($matches[0], $replacement, $line);
			$result = $line;
		}
		
		$line = $this->migrateLocalesInJs($line, $result);
		
		return $result;
	}	
	
	/**
	 * @param string $line
	 * @param boolean $result
	 * @return string
	 */
	private function migrateI18nAttributes($line, &$result, $attrNames)
	{
		foreach ($attrNames as $attrName)
		{
			if (preg_match('/'.$attrName.'="([^";]*)"/', $line, $matches))
			{
				$key = $matches[1];
				if(strpos($key, '.') > 2)
				{
					$formaters = array();
					$key = $this->convertKey($key, $formaters);
					$line = str_replace($matches[0], $attrName.'="'.$key.'"', $line);
					$result = $line;
				}
			}
		}
		
		return $line;
	}
	
	/**
	 * @param string $line
	 * @return string
	 */
	private function migrateLocalesInJs($line, &$result)
	{
		if (preg_match('/"&((modules|framework|themes)[\.a-zA-Z0-9]+);"/', $line, $matches))
		{
			$key = $matches[1];
			if(strpos($key, '.') > 2)
			{
				$formatters = array();
				$key = $this->convertKey($key, $formatters);
				$format = (count($formatters) > 0) ? (','.implode(',', $formatters)) : '';
				$line = str_replace($matches[0], '"${transui:'.$key.',js'.$format.'}"', $line);
				$result = $line;
			}
		}
		
		if (preg_match('/\'&((modules|framework|themes)[\.a-zA-Z0-9]+);\'/', $line, $matches))
		{
			$key = $matches[1];
			if(strpos($key, '.') > 2)
			{
				$formatters = array();
				$key = $this->convertKey($key, $formatters);
				$format = (count($formatters) > 0) ? (','.implode(',', $formatters)) : '';
				$line = str_replace($matches[0], '"${transui:'.$key.',js'.$format.'}"', $line);
				$result = $line;
			}
		}
		
		return $line;
	}
	
	/**
	 * @param string $moduleName
	 * @return string[]
	 */
	private function scanModule($moduleName)
	{
		$baseTemplatePath = f_util_FileUtils::buildWebeditPath('modules', $moduleName, 'forms', 'editor');
		$paths = $this->scanEditors($baseTemplatePath);
		
		$baseTemplatePath = f_util_FileUtils::buildWebeditPath('override', 'modules', $moduleName, 'forms', 'editor');
		$paths = array_merge($paths, $this->scanEditors($baseTemplatePath));
		return $paths;
	}
	
	/**
	 * @param string $moduleName
	 * @return string[]
	 */
	private function scanEditors($basePath)
	{
		$paths = array();
		if (! is_dir($basePath) || ! is_readable($basePath))
		{
			return $paths;
		}
		
		$objects = new RecursiveDirectoryIterator($basePath);
		foreach ($objects as $name => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			if ($splFileInfo->isDir())
			{
				$paths[] = $splFileInfo->getPathname();
			}
		}
		return $paths;
	}
	
	/**
	 * @param string $filePath
	 * @param string $cleanedFilePath
	 * @param string $cleanLineMethod
	 */
	private function handleFile($filePath, $cleanLineMethod)
	{
		$cleanedFilePath = $filePath . '.clean';
		if (file_exists($cleanedFilePath)) {unlink($cleanedFilePath);}
		echo ' - Clean file: ', $filePath, "\n";
		$this->cleanFile($filePath, $cleanedFilePath, $cleanLineMethod);
		if (file_exists($cleanedFilePath))
		{
			rename($filePath, $filePath . '.old');
			rename($cleanedFilePath, $filePath);
			echo "   -> Cleaned successfully\n";
		}
		else
		{
			echo "   -> Already clean\n";
		}
	}
	
	/**
	 * @param string $filePath
	 * @param string $cleanedFilePath
	 * @param string $cleanLineMethod
	 */
	private function cleanFile($filePath, $cleanedFilePath, $cleanLineMethod)
	{
		$updates = array();
		if (is_writeable($filePath))
		{
			$lines = file($filePath);
			$count = count($lines);
			for ($i = 0; $i < $count; $i ++)
			{
				$update = $this->{$cleanLineMethod}($lines[$i], $i + 1);
				if ($update !== false)
				{
					$updates[$i] = array($lines[$i], $update);
					if (trim($update))
					{
						$lines[$i] = $update;
					}
					else
					{
						unset($lines[$i]);
					}
				}
			}
			
			if (count($updates))
			{
				file_put_contents($cleanedFilePath, implode('', $lines));
			}
		}
		else
		{
			echo $filePath, " is not writeable\n";
		}
	}
	
	/**
	 * @param string $key
	 * @param array $formaters
	 * @return $key
	 */
	private function convertKey($key, &$formaters)
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
		$first = current($keyPart);
		if ($keyPartCount > 1 && in_array($first, array('m', 'f', 't')))
		{
			$cleanOldKey = implode('.', $keyPart);
			$formaters = LocaleService::getInstance()->getFormattersByCleanOldKey($cleanOldKey);
		}
		return $cleanOldKey;
	}
}