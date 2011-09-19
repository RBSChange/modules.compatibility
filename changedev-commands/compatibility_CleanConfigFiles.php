<?php
/**
 * commands_compatibility_CleanConfigFiles
 * @package modules.compatibility.command
 * 
 * Delete unused files on modules not visible in BO: 
 *  - config/actions.xml
 *  - config/rights.xml
 *  - config/perspective.xml
 *  - templates/perspectives
 *  - i18n/document/permission
 * 
 * modules.xml:
 * 	- remove usetopic tag if set to false (default value)
 *  - remove enabled tag (not parsed any more always set to "true")
 * 
 * *blocks.xml:
 *  - replace label attributes by labeli18n
 *  - remove display attribute
 *  - remove editable attribute
 *  
 * *perspective.xml: 
 *  - replace label attributes by labeli18n
 *  - refactor column label attributes
 *  - remplace GetDialogTopicTree permission on actions by Update_rootfolder
 *  
 * *.tags.xml: 
 *  - replace label attributes by labeli18n
 *  
 * *rights.xml: 
 *  - delete EditLocale and GetDialogTopicTree permissions 
 */
class commands_compatibility_CleanConfigFiles extends c_ChangescriptCommand
{
	/**
	 * @return String
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
		return "Clean config files in modules";
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
	 * @var string
	 */
	private $currentModule;
	
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== CleanConfigFiles ==");

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
			
			$basePath = f_util_FileUtils::buildModulesPath($moduleName, 'config');
			$this->currentModule = $moduleName;
			$this->errors = array();
			
			// Delete unused files on modules not visible in BO.
			if (!ModuleService::getInstance()->getModule($moduleName)->isVisible())
			{
				$files = array('actions.xml', 'perspective.xml', 'rights.xml');
				foreach ($files as $file)
				{
					$path = f_util_FileUtils::buildModulesPath($moduleName, 'config', $file);
					if (file_exists($path))
					{
						unlink($path);
						echo ' - DELETE ', $path, "\n";
					}
				}
				
				$path = f_util_FileUtils::buildModulesPath($moduleName, 'templates', 'perspectives');
				if (is_dir($path))
				{
					f_util_FileUtils::rmdir($path);
					echo ' - DELETE ', $path, "\n";
				}
				
				if ($moduleName !== 'generic')
				{
					$path = f_util_FileUtils::buildModulesPath($moduleName, 'i18n', 'document', 'permission');
					if (is_dir($path))
					{
						f_util_FileUtils::rmdir($path);
						echo ' - DELETE ', $path, "\n";
					}
				}
			}
			
			$filePath = f_util_FileUtils::buildModulesPath($moduleName, 'config', 'module.xml');
			if (file_exists($filePath))
			{
				$this->handleFile($filePath, 'migrateModuleLine');
			}
			else
			{
				echo ' - No module.xml', "\n";
			}
			
			// Clean blocks.xml
			$filePaths = $this->scanDir($basePath, 'blocks.xml');
			if (count($filePaths))
			{
				foreach ($filePaths as $filePath)
				{
					$this->handleFile($filePath, 'migrateBlockLine');
				}
			}
			else
			{
				echo ' - No blocks.xml nor xxxblocks.xml', "\n";
			}
			
			// Clean perspective.xml
			$filePaths = $this->scanDir($basePath, 'perspective.xml');
			if (count($filePaths))
			{
				foreach ($filePaths as $filePath)
				{
					$this->handleFile($filePath, 'migratePerspectiveLine');
				}
			}
			else
			{
				echo ' - No perspective.xml nor xxxperspective.xml', "\n";
			}
			
			// Clean *.tags.xml
			$filePaths = $this->scanDir($basePath, '.tags.xml');
			if (count($filePaths))
			{
				foreach ($filePaths as $filePath)
				{
					$this->handleFile($filePath, 'migrateTagLine');
				}
			}
			else
			{
				echo ' - No xxx.tags.xml', "\n";
			}
			
			// Clean rights.xml
			$filePaths = $this->scanDir($basePath, 'rights.xml');
			if (count($filePaths))
			{
				foreach ($filePaths as $filePath)
				{
					$this->handleFile($filePath, 'migrateRightsLine');
				}
			}
			else
			{
				echo ' - No rights.xml nor xxxrights.xml', "\n";
			}	
			
			echo 'END module ', $moduleName, "\n";
		}

		$this->quitOk("Command successfully executed");
	}
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migrateBlockLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		if (preg_match('/( *)display="([^"]*)"( *)/', $line, $matches))
		{
			$replacement = (strlen($matches[1]) > 0 || strlen($matches[2]) > 0) ? ' ' : '';
			$line = str_replace($matches[0], $replacement, $line);
			$result = $line;
		}
		if (preg_match('/( *)editable="([^"]*)"( *)/', $line, $matches))
		{
			$replacement = (strlen($matches[1]) > 0 || strlen($matches[2]) > 0) ? ' ' : '';
			$line = str_replace($matches[0], $replacement, $line);
			$result = $line;
		}
		if (preg_match('/( *)label="&amp;([^";]*);"( *)/', $line, $matches))
		{
			$key = $this->convertKey($matches[2]);
			$replacement = $matches[1]  . 'labeli18n="'.$key.'"' . $matches[3];
			
			$tmatches = array();
			if (preg_match('/type="([^"]*)"/', $line, $tmatches))
			{
				list(, $moduleName, $blockName) = explode('_', strtolower($tmatches[1]));
				if ($key == 'm.' . $moduleName . '.bo.blocks.' . $blockName . '.title')
				{
					$replacement = (strlen($matches[1]) > 0 || strlen($matches[3]) > 0) ? ' ' : '';
				}
			}
				
			$line = str_replace($matches[0], $replacement, $line);
			$result = $line;
		}
		return $result;
	}
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migrateTagLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		if (preg_match('/label="&amp;([^";]*);"/', $line, $matches))
		{
			$key = $this->convertKey($matches[1]);
			$line = str_replace($matches[0], 'labeli18n="'.$key.'"', $line);
			$result = $line;
		}
		return $result;
	}
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migratePerspectiveLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		if (preg_match('/label="&amp;([^";]*);"/', $line, $matches))
		{
			$key = $this->convertKey($matches[1]);
			$line = str_replace($matches[0], 'labeli18n="'.$key.'"', $line);
			$result = $line;
		}
		
		if (strpos('<column ', $line) !== false && preg_match('/name="([a-zA-Z0-9\-]+)"/', $line, $matches) && preg_match('/ label="([a-zA-Z0-9\-]+)"/', $line, $matches2))
		{
			if (strtolower($matches[1]) == strtolower($matches2[1]))
			{
				$line = str_replace($matches2[0], '', $line);
				$result = $line;
			}
			else
			{
				$key = 'm.' . $this->currentModule . '.bo.general.column.' . strtolower($matches2[1]);
				$line = str_replace($matches2[0], ' labeli18n="'.$key.'"', $line);
				$result = $line;
			}
		}
		
		if (strpos('<action ', $line) !== false && preg_match('/permission="GetDialogTopicTree"/', $line, $matches))
		{
			$line = str_replace($matches[0], 'permission="Update_rootfolder"', $line);
			$result = $line;
		}
		
		return $result;
	}
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migrateRightsLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		if (preg_match('#(\s*)<permission(\s+)name="EditLocale"(\s*)/>(\s*)#', $line, $matches))
		{
			$line = str_replace($matches[0], '', $line);
			$result = $line;
		}
		if (preg_match('#(\s*)<permission(\s+)name="GetDialogTopicTree"(\s*)/>(\s*)#', $line, $matches))
		{
			$line = str_replace($matches[0], '', $line);
			$result = $line;
		}
		
		return $result;
	}
	
	/**
	 * @param string $line
	 * @param integer $lineNumber
	 */
	private function migrateModuleLine($line, $lineNumber)
	{
		//echo 'Line:', $line, "\n";
		$result = false;
		$matches = array();
		
		if (preg_match('#(\s*)<usetopic>([^<>]*)</usetopic>(\s*)#', $line, $matches) && $matches[2] != 'true')
		{
			echo $matches[1], "\n";
			$line = str_replace($matches[0], '', $line);
			$result = $line;
		}
		
		if (preg_match('#(\s*)<enabled>[^<>]*</enabled>(\s*)#', $line, $matches))
		{
			$line = str_replace($matches[0], '', $line);
			$result = $line;
		}
		
		return $result;
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
	function cleanFile($filePath, $cleanedFilePath, $cleanLineMethod)
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
	 * @return $key
	 */
	private function convertKey($key)
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
			LocaleService::getInstance()->getFormattersByCleanOldKey($cleanOldKey);
		}
		return $cleanOldKey;
	}
	
	/**
	 * @param string $basePath
	 * @param string $endsWith
	 */
	private function scanDir($basePath, $endsWith)
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
			if (! $splFileInfo->isDir() && f_util_StringUtils::endsWith($splFileInfo->getPathname(), $endsWith))
			{
				$paths[] = $splFileInfo->getPathname();
			}
		}
		return $paths;
	}
}