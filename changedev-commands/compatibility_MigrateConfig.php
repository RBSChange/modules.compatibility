<?php
/**
 * commands_compatibility_MigrateConfig
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateConfig extends commands_AbstractChangeCommand
{
	/**
	 * @return String
	 * @example "<moduleName> <name>"
	 */
	public function getUsage()
	{
		return "";
	}
	
	/**
	 * @return String
	 * @example "initialize a document"
	 */
	public function getDescription()
	{
		return "Migrate project xml config file";
	}
	
	/**
	 * This method is used to handle auto-completion for this command.
	 * @param Integer $completeParamCount the parameters that are already complete in the command line
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @return String[] or null
	 */
	//	public function getParameters($completeParamCount, $params, $options, $current)
	//	{
	//		$components = array();
	//		
	//		// Generate options in $components.		
	//		
	//		return $components;
	//	}
	

	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @return boolean
	 */
	//	protected function validateArgs($params, $options)
	//	{
	//	}
	

	/**
	 * @return String[]
	 */
	//	public function getOptions()
	//	{
	//	}
	

	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== MigrateConfig ==");
		
		$profile = trim(file_get_contents(PROJECT_HOME . DIRECTORY_SEPARATOR . 'profile'));
		
		$this->message("Current profile: " . $profile);
		
		$path = PROJECT_HOME . DIRECTORY_SEPARATOR . 'change.properties';
		if (! file_exists($path))
		{
			$this->quitError('file : ' . $path . ' not found');
		}
		$projectProperties = file($path);
		$this->updateProjectProperties('PROJECT_HOME', PROJECT_HOME, $projectProperties);
		if (! $this->hasProjectProperty('TMP_PATH', $projectProperties))
		{
			$this->updateProjectProperties('TMP_PATH', $this->evaluateTmpPath(), $projectProperties);
		}
		
		if (! $this->hasProjectProperty('CHANGE_COMMAND', $projectProperties))
		{
			$this->updateProjectProperties('CHANGE_COMMAND', 'framework/bin/change.php', 
					$projectProperties);
		}
		
		if (! $this->hasProjectProperty('ZEND_FRAMEWORK_PATH', $projectProperties))
		{
			$this->updateProjectProperties('ZEND_FRAMEWORK_PATH', 
					PROJECT_HOME . '/libs/zfminimal/library', $projectProperties);
		}
		
		if ($profile !== 'project')
		{
			$pathToDelete = PROJECT_HOME . '/build/' . $profile;
			$this->message("Delete folder: " . $pathToDelete);
			$this->rm($pathToDelete);
			
			$pathToDelete = PROJECT_HOME . '/cache/' . $profile;
			$this->message("Delete folder: " . $pathToDelete);
			$this->rm($pathToDelete);
			
			$pathToDelete = PROJECT_HOME . 'build/config/project.'.$profile.'.php';
			$this->message("Delete file: " . $pathToDelete);
			$this->rm($pathToDelete);
		}
		
		$projectConfig = PROJECT_HOME . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'project.xml';
		if (! file_exists($projectConfig))
		{
			$this->quitError('file : ' . $projectConfig . ' not found');
		}
		$this->log('Check: ' . $projectConfig);
		$projectDoc = new DOMDocument('1.0', 'UTF-8');
		$projectDoc->load($projectConfig);
		$this->migrateXmlConfig($projectDoc, $projectProperties);
		$projectDoc->save($projectConfig);
		
		$profileConfig = PROJECT_HOME . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'project.' . $profile . '.xml';
		if (! file_exists($profileConfig))
		{
			$this->quitError('file : ' . $profileConfig . ' not found');
		}
		$this->log('Check: ' . $profileConfig);
		$profileDoc = new DOMDocument('1.0', 'UTF-8');
		$profileDoc->load($profileConfig);
		$this->migrateXmlConfig($profileDoc, $projectProperties);
		$profileDoc->save($profileConfig);
		
		$this->log('Save: ' . $path);
		file_put_contents($path, implode('', $projectProperties));
		
		$this->quitOk("Command successfully executed");
	}
	
	private function migrateInjection($xmlDocument)
	{
		$files = f_util_FileUtils::find('project.*.xml', f_util_FileUtils::buildProjectPath('config'));
		$files[] = f_util_FileUtils::buildProjectPath('config', 'project.xml');
		foreach ($files as $path)
		{
			$process = true;
			$xmlDoc = f_util_DOMUtils::fromPath($path);
			$injectElement = $xmlDoc->findUnique('//injection');
			$classElement = $xmlDoc->createElement('class');
			$children = $injectElement->childNodes;
			$childrenCount = $children->length;
			while ($injectElement->childNodes->length)
			{
				$node = $injectElement->firstChild;
				if ($node->nodeName != 'class')
				{
					$injectElement->removeChild($node);
					$classElement->appendChild($node);
				}	
				else
				{
					$process = false;
					break;
				}
			}
			if ($process)
			{
				$injectElement->appendChild($classElement);
				f_util_FileUtils::write($path, $xmlDoc->saveXML(), f_util_FileUtils::OVERRIDE);
			}
			else
			{
				$this->logWarning("It looks like " . $path . " injection section has already been migrated!");
			}
		}
	}
	
	/**
	 * @param DOMDocument $xmlDocument
	 * @param string[] $projectProperties
	 */
	private function migrateXmlConfig($xmlDocument, &$projectProperties)
	{
		$toDelete = array();
		foreach ($xmlDocument->getElementsByTagName('define') as $defineElem)
		{
			/* @var $defineElem DOMElement */
			$n = $defineElem->getAttribute('name');
			$v = $defineElem->textContent;
			switch ($n)
			{
				case 'DOCUMENT_ROOT' :
					$this->log(' -> DOCUMENT_ROOT: ' . $v);
					$this->updateProjectProperties($n, $v, $projectProperties);
					$toDelete[] = $defineElem;
					break;
				case 'DEVELOPMENT_MODE' :
				case 'AG_DEVELOPMENT_MODE' :
					$this->log(' -> ' . $n . ': ' . $v);
					$this->updateProjectProperties('DEVELOPMENT_MODE', $v, $projectProperties);
					$toDelete[] = $defineElem;
					break;
				case 'AG_LOGGING_LEVEL' :
					$this->log(' -> ' . $n . ': ' . $v);
					$defineElem->setAttribute('name', 'LOGGING_LEVEL');
					break;
				case 'TMP_PATH' :
					$this->log(' -> ' . $n . ': ' . $v);
					$this->updateProjectProperties($n, $v, $projectProperties);
					$toDelete[] = $defineElem;
					break;
				case 'FAKE_EMAIL' :
					$this->log(' -> ' . $n . ': ' . $v);
					$this->updateProjectProperties($n, $v, $projectProperties);
					$toDelete[] = $defineElem;
					break;
				case 'CHANGE_COMMAND' :
					$this->log(' -> ' . $n . ': ' . $v);
					$this->updateProjectProperties($n, $v, $projectProperties);
					$toDelete[] = $defineElem;
					break;
				case 'AG_SUPPORTED_LANGUAGES' :
					$this->log(' -> ' . $n . ': ' . $v);
					$defineElem->setAttribute('name', 'SUPPORTED_LANGUAGES');
					break;
				case 'AG_UI_SUPPORTED_LANGUAGES' :
					$this->log(' -> ' . $n . ': ' . $v);
					$defineElem->setAttribute('name', 'UI_SUPPORTED_LANGUAGES');
					break;
				case 'AG_DISABLE_BLOCK_CACHE' :
					$this->log(' -> ' . $n . ': ' . $v);
					$defineElem->setAttribute('name', 'DISABLE_BLOCK_CACHE');
					break;
				case 'AG_DISABLE_SIMPLECACHE' :
				case 'DISABLE_SIMPLECACHE' :
					$this->log(' -> ' . $n . ': ' . $v);
					$defineElem->setAttribute('name', 'DISABLE_DATACACHE');
					break;
				case 'AG_WEBAPP_NAME' :
					$this->log(' -> ' . $n . ': ' . $v);
					$toDelete[] = $defineElem;
					$this->addProjetcName($xmlDocument, $v);
					break;
			}
		}
		
		foreach ($toDelete as $defineElem)
		{
			$defineElem->parentNode->removeChild($defineElem);
		}
		
		$process = false;
		$injections = $xmlDocument->getElementsByTagName('injection');
		foreach ($injections as $injectElement) 
		{
			$classElement = $xmlDocument->createElement('class');
			$children = $injectElement->childNodes;
			$childrenCount = $children->length;
			while ($injectElement->childNodes->length)
			{
				$node = $injectElement->firstChild;
				if ($node->nodeName != 'class')
				{
					$process = true;
					$injectElement->removeChild($node);
					$classElement->appendChild($node);
				}	
				else
				{
					break;
				}
			}
			if ($process)
			{
				$injectElement->appendChild($classElement);
			}
		}
	}
	
	private function updateProjectProperties($name, $value, &$projectProperties)
	{
		foreach ($projectProperties as $index => $line)
		{
			if (strpos($line, $name) === 0)
			{
				$projectProperties[$index] = $name . '=' . $value . "\n";
				return;
			}
		}
		$projectProperties[] = "\n" . $name . '=' . $value . "\n";
	}
	
	private function hasProjectProperty($name, $projectProperties)
	{
		foreach ($projectProperties as $index => $line)
		{
			if (strpos($line, $name) === 0)
			{
				return true;
			}
		}
		return false;
	}
	
	private function evaluateTmpPath()
	{
		if (function_exists('sys_get_temp_dir'))
		{
			$TMP_PATH = sys_get_temp_dir();
		}
		else
		{
			$tmpfile = @tempnam(null, 'loc_');
			if ($tmpfile)
			{
				$TMP_PATH = dirname($tmpfile);
				@unlink($tmpfile);
			}
			else if (DIRECTORY_SEPARATOR === '\\')
			{
				if (isset($_ENV['TMP']))
				{
					$TMP_PATH = $_ENV['TMP'];
				}
				else if (isset($_ENV['TEMP']))
				{
					$TMP_PATH = $_ENV['TEMP'];
				}
				else
				{
					$TMP_PATH = 'C:\\Windows\\Temp';
				}
			}
			else
			{
				$TMP_PATH = '/tmp';
			}
		}
		return $TMP_PATH;
	}
	
	/**
	 * 
	 * @param DOMDocument $document
	 * @param string $projectName
	 */
	private function addProjetcName($document, $projectName)
	{
		$nl = $document->getElementsByTagName('config');
		if ($nl->length == 0)
		{
			$config = $document->documentElement->appendChild($document->createElement('config'));
		}
		else
		{
			$config = $nl->item(0);
		}
		
		$nl = $config->getElementsByTagName('general');
		if ($nl->length == 0)
		{
			$general = $config->appendChild($document->createElement('general'));
		}
		else
		{
			$general = $nl->item(0);
		}
		
		foreach ($general->getElementsByTagName('entry') as $entry)
		{
			if ($entry->getAttribute('name') == 'projectName')
			{
				while ($entry->firstChild)
				{
					$entry->removeChild($entry->firstChild);
				}
				$entry->appendChild($document->createTextNode($projectName));
				return $entry;
			}
		}
		$entry = $general->appendChild($document->createElement('entry'));
		$entry->setAttribute('name', 'projectName');
		$entry->appendChild($document->createTextNode($projectName));
		return $entry;
	}
	
	private function rm($filePath)
	{
		if (is_dir($filePath))
		{
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filePath), RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $path)
			{
				if ($path->isDir())
				{
					rmdir($path->__toString());
				}
				else
				{
					unlink($path->__toString());
				}
			}
			rmdir($filePath);
		}
		else if (file_exists($filePath))
		{
			unlink($filePath);
		}
	}
}