<?php
/**
 * commands_compatibility_MigrateConfig
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateConfig extends c_ChangescriptCommand
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
			$this->updateProjectProperties('CHANGE_COMMAND', 'framework/bin/change.php', $projectProperties);
		}
		
		if (! $this->hasProjectProperty('ZEND_FRAMEWORK_PATH', $projectProperties))
		{
			$this->updateProjectProperties('ZEND_FRAMEWORK_PATH', PROJECT_HOME . '/libs/zfminimal/library', $projectProperties);
		}
		
		if ($profile !== 'project' && is_dir(PROJECT_HOME . '/build/' . $profile))
		{
			$pathToDelete = PROJECT_HOME . '/build/' . $profile;
			$this->message("Delete folder: " . $pathToDelete);
			$this->rm($pathToDelete);
			
			$pathToDelete = PROJECT_HOME . '/cache/' . $profile;
			$this->message("Delete folder: " . $pathToDelete);
			$this->rm($pathToDelete);
			
			$pathToDelete = PROJECT_HOME . '/cache/autoload';
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
		$projectDoc->formatOutput = true;
		$projectDoc->preserveWhiteSpace = false;
		$projectDoc->load($projectConfig);
		$projectDoc->formatOutput = true;
		$this->migrateXmlConfig($projectDoc, $projectProperties);
		$projectDoc->save($projectConfig);
		
		$profileConfig = PROJECT_HOME . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'project.' . $profile . '.xml';
		if (! file_exists($profileConfig))
		{
			$this->quitError('file : ' . $profileConfig . ' not found');
		}
		$this->log('Check: ' . $profileConfig);
		$profileDoc = new DOMDocument('1.0', 'UTF-8');
		$profileDoc->formatOutput = true;
		$profileDoc->preserveWhiteSpace = false;
		$profileDoc->load($profileConfig);
		$profileDoc->formatOutput = true;
		
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
				case 'PROJECT_ID' :
					$this->log(' -> PROJECT_ID: ' . $v);
					$this->updateProjectProperties($n, $v, $projectProperties);
					$toDelete[] = $defineElem;
					break;
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
					
				case 'SOLR_INDEXER_CLIENT' :
					$this->log(' -> ' . $n . ': ' . $v);
					$this->addProjectConfigurationEntry($xmlDocument, 'config/solr/clientId', $v);
					$toDelete[] = $defineElem;
					break;					
				case 'SOLR_INDEXER_URL' :
					$this->log(' -> ' . $n . ': ' . $v);
					$this->addProjectConfigurationEntry($xmlDocument, 'config/solr/url', $v);
					$serviceName = (strpos($v, 'mysqlindexer') !== false) ? 'mysqlindexer_IndexService' : 'indexer_SolrIndexService';
					$this->addProjectConfigurationEntry($xmlDocument, 'config/injection/class/indexer_IndexService', $serviceName);
					$toDelete[] = $defineElem;
					break;						
				case 'SOLR_INDEXER_DISABLE_BATCH_MODE':
					$this->log(' -> ' . $n . ': ' . $v);
					$this->addProjectConfigurationEntry($xmlDocument, 'config/solr/batch_mode', $v);
					$toDelete[] = $defineElem;
					break;						
				case 'SOLR_USE_POST_QUERIES':
					$this->log(' -> ' . $n . ': ' . $v);
					$this->addProjectConfigurationEntry($xmlDocument, 'config/solr/request_method', $v);
					$toDelete[] = $defineElem;
					break;						
				case 'SOLR_INDEXER_DISABLE_COMMIT':
					$this->log(' -> ' . $n . ': ' . $v);
					$this->addProjectConfigurationEntry($xmlDocument, 'config/solr/disable_commit', $v);
					$toDelete[] = $defineElem;
					break;						
				case 'SOLR_INDEXER_DISABLE_DOCUMENTCACHE':
					$this->log(' -> ' . $n . ': ' . $v);
					$this->addProjectConfigurationEntry($xmlDocument, 'config/solr/disable_document_cache', $v);
					$toDelete[] = $defineElem;
					break;
				case 'MOD_NOTIFICATION_SENDER_HOST':
				case 'DEFAULT_SENDER_HOST':
					$this->log(' -> ' . $n . ': ' . $v);
					$toDelete[] = $defineElem;
					break;
				case 'MOD_NOTIFICATION_SENDER':
				case 'NOREPLY_DEFAULT_EMAIL':
					$this->log(' -> ' . $n . ': ' . $v);
					$toDelete[] = $defineElem;
					$this->addProjectConfigurationEntry($xmlDocument, 'config/modules/notification/noreplySender', $v);
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
		
		// TODO move general/server-fqdn to DEFAULT_HOST
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
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filePath, FilesystemIterator::SKIP_DOTS), 
					RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $path)
			{
				/* @var $path DirectoryIterator */
				if ($path->isDir())
				{
					rmdir($path->getPathname());
				}
				else
				{
					unlink($path->getPathname());
				}
			}
			rmdir($filePath);
		}
		else if (file_exists($filePath))
		{
			unlink($filePath);
		}
	}
	
	/**
	 * @param string $path
	 * @param string $value
	 * @return string old value
	 */
	private function addProjectConfigurationEntry($dom, $path, $value)
	{
		if ($dom->documentElement == null)
		{
			return false;
		}
		if ($value !== null && !is_string($value))
		{
			return false;
		}
		foreach (explode('/', $path) as $name) 
		{
			if (trim($name) != '') {$sections[] = trim($name);}
		}
				
		if (count($sections) < 3) 
		{
			return false;
		}
				
		$entryName = array_pop($sections);
		$oldValue = null;
		$sectionNode = $dom->documentElement;	
		foreach ($sections as $sectionName) 
		{
			$childSectionNode = null;
			foreach ($sectionNode->childNodes as $entryNode) 
			{
				if ($entryNode->nodeType === XML_ELEMENT_NODE && $entryNode->nodeName === $sectionName) 
				{
					$childSectionNode = $entryNode;
					break;
				}
			}
			if ($childSectionNode === null)
			{
				$childSectionNode = $sectionNode->appendChild($dom->createElement($sectionName));
			}
			$sectionNode = $childSectionNode;
		}
		
		foreach ($sectionNode->childNodes as $entryNode) 
		{
			if ($entryNode->nodeType === XML_ELEMENT_NODE && $entryNode->getAttribute('name') === $entryName) 
			{
				$oldValue = $entryNode->textContent;
				break;
			}
		}
		
		if ($oldValue !== $value)
		{
			if ($value === null)
			{
				$sectionNode->removeChild($entryNode);
				
				while (!$sectionNode->hasChildNodes() && $sectionNode->nodeName !== 'config')
				{
					$pnode = $sectionNode->parentNode;
					$pnode->removeChild($sectionNode);
					$sectionNode = $pnode;
				}
			}
			elseif ($oldValue === null)
			{
				$entryNode = $sectionNode->appendChild($dom->createElement('entry'));
				$entryNode->setAttribute('name', $entryName);
				$entryNode->appendChild($dom->createTextNode($value));
			}
			else
			{
				while ($entryNode->hasChildNodes())
				{
					$entryNode->removeChild($entryNode->firstChild);
				}
				$entryNode->appendChild($dom->createTextNode($value));
			}
		}
		return $oldValue;
	}
}