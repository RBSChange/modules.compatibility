<?php
/**
 * @todo
 * Virer les tags contextuels en "_page-detail"
 */
interface compatibility_Logger
{
	public function logInfo($message);
	public function logWarn($message);
	public function logError($message);
}

class compatibility_ModuleConverter
{
	/**
	 *
	 * @var string
	 */
	private $moduleName;
	
	/**
	 *
	 * @var string
	 */
	private $srcDirectory;
	
	/**
	 *
	 * @var compatibility_Logger
	 */
	private $logger;
	
	/**
	 *
	 * @param string $moduleName
	 * @param string $srcDirectory
	 * @param compatibility_Logger $logger
	 */
	public function __construct($moduleName, $srcDirectory, $logger)
	{
		$this->moduleName = $moduleName;
		$this->srcDirectory = $srcDirectory;
		$this->logger = $logger;
	}
	
	public function convert()
	{
		$this->logger->logInfo('Converting Module ' . $this->moduleName . '...');
		// convert: .
		$this->convertRoot();
		
		// convert: config
		$this->convertConfig();
		
		// convert: patch
		$this->convertPatch();
		
		// convert global : php files
		$this->convertPHP();
		
		// convert global : xml files
		$this->convertXML();
		
		// convert: apache
		
		// convert: actions
		
		// convert: change-commands
		// convert: changedev-commands
		$this->convertChangeCommands();
		
		
		// convert: forms
		$this->convertForms();
		
		// convert: i18n
		$this->convertI18n();
				
		// convert: persistentdocument
		$this->convertPersistentDocument();
		
		// convert: setup
		$this->convertSetup();
		
		// convert: style
		$this->convertStyle();
		
		// convert: templates
		$this->convertTemplates();
		
		// convert: views
		$this->convertViews();
		
		// convert: webapp
		
		// convert: lib
		
		// convert: lib/bindings
		
		// convert: lib/blocks
		$this->convertBlocks();
		
		// convert: lib/services
		$this->convertServices();
		
		// convert: lib/phptal
		
		// convert: lib/workflowActions
		
		// convert: lib/js
		
		$this->logger->logInfo('Module ' . $this->moduleName . ' Converted');
	}
	
	
	protected function convertRoot()
	{
		if (!file_exists($this->srcDirectory . '/install.xml'))
		{
			if (!file_exists($this->srcDirectory . '/change.xml'))
			{
				$this->logger->logError('file /change.xml not found');
				return;
			}
			$this->logger->logInfo('Migrate: /change.xml');
			$install = $this->loadFormattedXMLDocument();
			$install->loadXML('<?xml version="1.0" encoding="UTF-8"?>
			<install type="modules" name="' . $this->moduleName . '" version="4.0.0" />');
			
			$doc = $this->loadFormattedXMLDocument($this->srcDirectory . '/change.xml');
			foreach ($doc->getElementsByTagNameNS('*', 'dependency') as $dependency)
			{
				/* @var $dependency DOMElement */
				
				$names = $dependency->getElementsByTagNameNS('*', 'name');
				if ($names->length != 1)
				{
					$this->logger->logWarn('Unable to migrate : ' . $doc->saveXML($dependency));
					continue;
				}
				$nameParts = explode('/', $names->item(0)->textContent);
				if ($nameParts[0] === 'change-module' && count($nameParts) == 2)
				{
					$package = $install->createElement('package');
					$install->documentElement->appendChild($package);
					$package->setAttribute('type', 'modules');
					$package->setAttribute('name', $nameParts[1]);
				}
				else
				{
					$this->logger->logWarn('Unable to migrate dependency : ' . $names->item(0)->textContent);
				}
			}
			$this->saveFormattedXMLDocument($install, $this->srcDirectory . '/install.xml');
			unlink($this->srcDirectory . '/change.xml');
			$this->logger->logInfo('Replace /change.xml by /install.xml');
		}
	}
	
	protected function convertSetup()
	{
		$baseDir = $this->srcDirectory . '/setup';
		if (!is_dir($baseDir))
		{
			return;
		}
		
		$xmlFiles = $this->scanDir($baseDir, '.xml');
		foreach ($xmlFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$doc = $this->loadFormattedXMLDocument($splFileInfo->getPathname());
			$content = $this->formattedXMLString($doc->saveXML());
			
			$content = preg_replace_callback('/&amp;((modules|framework|themes|m|f|t)(\.[a-zA-Z0-9_-]+)+;)/', array($this, 'xmlMatchesKey'), $content);
			
			file_put_contents($splFileInfo->getPathname(), $content);
		}
	}
	
	/**
	 * @param string[] $matches
	 * @return string
	 */
	private function xmlMatchesKey($matches)
	{
		return $this->convertKeyIgnoreFormat('&' . $matches[1]);
	}
	
	protected function convertConfig()
	{
		if (!is_dir($this->srcDirectory . '/config'))
		{
			$this->logger->logError('folder /config not found');
			return;
		}
		if (!file_exists($this->srcDirectory . '/config/module.xml'))
		{
			$this->logger->logError('file /config/module.xml not found');
			return;
		}
		
		$hiddenModule = $this->convertConfigModule();
		if ($hiddenModule)
		{
			$directory = $this->srcDirectory . '/templates/perspectives';
			$this->rmdir($directory);	
			if ($this->moduleName !== 'generic')
			{
				$directory = $this->srcDirectory . '/i18n/document/permission';
				$this->rmdir($directory);
			}
		}
		
		$iterator = new DirectoryIterator($this->srcDirectory . '/config');
		foreach ($iterator as $fileinfo)
		{
			/* @var $fileinfo SplFileInfo */
			if ($fileinfo->isFile())
			{
				
				$name = $fileinfo->getFilename();					
				if ($name === 'actions.xml')
				{
					if ($hiddenModule)
					{
						unlink($fileinfo->getRealPath());
						$this->logger->logInfo('remove unused /config/' . $name . ' file');
					}
				}
				elseif ($name === 'perspective.xml')
				{
					if ($hiddenModule)
					{
						unlink($fileinfo->getRealPath());
						$this->logger->logInfo('remove unused /config/' . $name . ' file');
					}
					else
					{
						$this->convertConfigPerspective();
					}
				}
				elseif ($name === 'rights.xml')
				{
					if ($hiddenModule)
					{
						unlink($fileinfo->getRealPath());
						$this->logger->logInfo('remove unused /config/' . $name . ' file');
					}
					else
					{
						$this->convertConfigRight();
					}
				}
				elseif ($name === 'blocks.xml')
				{
					$this->convertConfigBlocks();
				}
				elseif (substr($name, -9) === '.tags.xml')
				{
					if ($hiddenModule)
					{
						unlink($fileinfo->getRealPath());
						$this->logger->logInfo('remove unused /config/' . $name . ' file');
					}
					else
					{
						$this->convertConfigTags($name);
					}
				}
				elseif (substr($name, -4) !== '.xml')
				{
					unlink($fileinfo->getRealPath());
					$this->logger->logInfo('remove unused /config/' . $name . ' file');
				}
			}
		}
	}
	
	protected function convertPatch()
	{
		$baseDir = $this->srcDirectory . '/patch';
		if (!is_dir($baseDir))
		{
			return;
		}
		
		$deleteBaseDir = true;
		$panelPaths = $this->scanDir($baseDir, '.php');
		foreach ($panelPaths as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			if ($splFileInfo->getFilename() !== 'install.php')
			{
				continue;
			}
			
			$parentInfo = $splFileInfo->getPathInfo();
			if (substr($parentInfo->getFilename(), 0, 2) !== '04')
			{
				$this->rmdir($parentInfo->getPathName());
				$this->logger->logInfo('Delete patch ' . $parentInfo->getFilename());
			}
			else
			{
				$deleteBaseDir = false;
			}
		}
		
		if ($deleteBaseDir)
		{
			$this->rmdir($baseDir);
			$this->logger->logInfo('Delete empty patch folder');
		}
	}
	
	/**
	 * @return boolean
	 */
	private function convertConfigModule()
	{
		$filePath = $this->srcDirectory . '/config/module.xml';
		$result = false;
		$doc = $this->loadFormattedXMLDocument($filePath);
		$moduleNode = $doc->getElementsByTagName('module')->item(0);
		if ($moduleNode !== null)
		{
			$dn = $moduleNode->getElementsByTagName('enabled')->item(0);
			if ($dn)
			{
				$dn->parentNode->removeChild($dn);
			}
			
			$dn = $moduleNode->getElementsByTagName('usetopic')->item(0);
			if ($dn && $dn->textContent == 'true')
			{
				$dn->setAttribute('deprecated', 'deprecated');
				$this->logger->logError('usetopic is deleted');
			}
			elseif ($dn)
			{
				$dn->parentNode->removeChild($dn);
			}
			
			$dn = $moduleNode->getElementsByTagName('visible')->item(0);
			if ($dn && $dn->textContent == 'false')
			{
				$result = true;
			}
		}
		else
		{
			$this->logger->logError('invalid /config/module.xml file');
		}
		$this->saveFormattedXMLDocument($doc, $filePath);
		return $result;
	}
	
	/**
	 * @return void
	 */
	private function convertConfigRight()
	{
		$filePath = $this->srcDirectory . '/config/rights.xml';
		$doc = $this->loadFormattedXMLDocument($filePath);
		foreach ($doc->getElementsByTagName('permission') as $permission)
		{
			/* @var $permission DOMElement */
			if (in_array($permission->getAttribute('name'), array('EditLocale', 'GetDialogTopicTree')))
			{
				$permission->parentNode->removeChild($permission);
			}
		}
		$this->saveFormattedXMLDocument($doc, $filePath);
	}
	
	/**
	 *
	 * @return void
	 */
	private function convertConfigTags($name)
	{
		$filePath = $this->srcDirectory . '/config/' . $name;
		$doc = $this->loadFormattedXMLDocument($filePath);
		if ($doc->documentElement->getAttribute('extends'))
		{
			$doc->documentElement->removeAttribute('extends');
		}
		foreach ($doc->getElementsByTagName('tag') as $tag)
		{
			/* @var $tag DOMElement */
			if ($tag->hasAttribute('label'))
			{
				$string = $tag->getAttribute('label');
				$key = $this->convertKeyIgnoreFormat($string);
				if ($string != $key)
				{
					$tag->removeAttribute('label');
					$tag->setAttribute('labeli18n', $key);
				}
			}
		}
		$this->saveFormattedXMLDocument($doc, $filePath);
	}
	
	private function convertConfigBlocks()
	{
		$filePath = $this->srcDirectory . '/config/blocks.xml';
		$doc = $this->loadFormattedXMLDocument($filePath);
		
		$this->migrateBlockConstraints($doc);
		foreach ($doc->getElementsByTagName('block') as $block)
		{
			$this->migrateBlockParameters($block);
			
			/* @var $block DOMElement */
			if (!$block->hasAttribute('type'))
			{
				$this->logger->logWarn('Config block with no type : ' . $doc->saveXML($block));
				$block->parentNode->removeChild($block);
				continue;
			}
			$bt = $block->getAttribute('type');
			
			if ($block->hasAttribute('editable'))
			{
				$this->logger->logInfo('Remove editable (' . $block->getAttribute('editable'). ') attribute on block: ' . $bt);
				$block->removeAttribute('editable');
			}
			
			if ($block->hasAttribute('display'))
			{
				$this->logger->logInfo('Remove display (' . $block->getAttribute('display'). ') attribute on block: ' . $bt);
				$block->removeAttribute('display');
			}
			
			if ($block->hasAttribute('label'))
			{
				
				$string = $block->getAttribute('label');
				$key = $this->convertKeyIgnoreFormat($string);
				if ($string != $key)
				{
					$this->logger->logInfo('Add labeli18n (' . $key. ') attribute on block: ' . $bt);
					$block->removeAttribute('label');
					$block->setAttribute('labeli18n', $key);
				}
			}
		}
		$this->saveFormattedXMLDocument($doc, $filePath);
	}
	
	private function convertConfigPerspective()
	{
		$filePath = $this->srcDirectory . '/config/perspective.xml';
		$doc = $this->loadFormattedXMLDocument($filePath);
		
		foreach ($doc->getElementsByTagName('*') as $element)
		{
			/* @var $element DOMElement */
			if ($element->hasAttribute('name'))
			{
				if ($element->hasAttribute('labeli18n'))
				{
					$element->setAttribute('labeli18n', strtolower($element->getAttribute('labeli18n')));
					if ($element->hasAttribute('label'))
					{
						$element->removeAttribute('label');
					}
				}
				elseif ($element->hasAttribute('label'))
				{
					$string = $element->getAttribute('label');
					$key = $this->convertKeyIgnoreFormat($string);
					if ($string != $key)
					{
						$this->logger->logInfo('Add labeli18n (' . $key. ') attribute on: ' . $element->localName . '/' . $element->getAttribute('name'));
						$element->removeAttribute('label');
						$element->setAttribute('labeli18n', $key);
					}
				}
				
				if ($element->localName == 'column' && $element->hasAttribute('label'))
				{
					if (strtolower($element->getAttribute('name')) == strtolower($element->getAttribute('label')))
					{
						$key = strtolower('m.' . $this->moduleName . '.bo.general.column.' . $element->getAttribute('name'));
						$element->removeAttribute('label');
						$element->setAttribute('labeli18n', $key);
					}
				}
				
				if ($element->localName == 'action' && $element->getAttribute('permission') == 'GetDialogTopicTree')
				{	
					$element->setAttribute('permission', 'Update_rootfolder');
				}
			}
		}
		$this->saveFormattedXMLDocument($doc, $filePath);
	}
	
	protected function convertChangeCommands()
	{
		$directory = $this->srcDirectory . '/change-commands';
		$newBaseName =  $this->srcDirectory . '/commands';
		if (is_dir($directory))
		{
			$this->logger->logInfo('Rename: ' . $directory . ' to ' . $newBaseName);
			rename($directory, $newBaseName);
		}
		
		$directory = $this->srcDirectory . '/changedev-commands';
		if (is_dir($directory))
		{
			if (!is_dir($newBaseName))
			{
				mkdir($newBaseName, 0777, true);
			}
			$this->logger->logInfo('Rename: ' . $directory . ' to ' . $newBaseName . '/dev');
			rename($directory, $newBaseName . '/dev');
		}
		
		if (is_dir($newBaseName))
		{
			$phpFiles = $this->scanDir($newBaseName, '.php');
			$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
			foreach ($phpFiles as $path => $splFileInfo)
			{
				/* @var $splFileInfo SplFileInfo */
				$classReplacer->convertPHPCommand($splFileInfo->getPathname());
			}
		}
	}
	
	protected function convertForms()
	{
		$directory = $this->srcDirectory . '/forms/editor';
		$panelPaths = $this->scanDir($directory, '.xml');
		foreach ($panelPaths as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$doc = $this->loadFormattedXMLDocument($splFileInfo->getPathname());
			foreach ($doc->getElementsByTagName('*') as $node)
			{
				/* @var $node DOMElement */
				foreach (array('label', 'actiontext', 'shorthelp') as $attrName)
				{
					$attrI18nName = $attrName. 'i18n';
					if ($node->hasAttribute($attrI18nName))
					{
						$node->setAttribute($attrI18nName, $this->convertKeyIgnoreFormat($node->getAttribute($attrI18nName)));
						if ($node->hasAttribute($attrName)) {$node->removeAttribute($attrName);}
					}
					elseif ($node->hasAttribute($attrName))
					{
						$string = $node->getAttribute($attrName);
						$key = $this->convertKeyIgnoreFormat($string);
						if ($string !== $key)
						{
							$node->setAttribute($attrI18nName, $key);
							$node->removeAttribute($attrName);
						}
					}
				}
			}
			$content = $this->formattedXMLString($doc->saveXML());
			
			$content = preg_replace_callback('/&(modules|framework|themes|m|f|t)(\.[a-zA-Z0-9_-]+)+;/', array($this, 'panelsMatchesKey'), $content);
			$content = str_replace('${transui:', '${trans:', $content);
			
			file_put_contents($splFileInfo->getPathname(), $content);
		}
	}
	
	protected function convertPHP()
	{
		$phpFiles = $this->scanDir($this->srcDirectory, '.php');
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		foreach ($phpFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$classReplacer->convertPHPFile($splFileInfo->getPathname());
		}
	}
	
	protected function convertXML()
	{
		$xmlFiles = $this->scanDir($this->srcDirectory, '.xml');
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		foreach ($xmlFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$classReplacer->convertXMLFile($splFileInfo->getPathname());
		}
	}
	
	protected function convertI18n()
	{
		$baseDir = $this->srcDirectory . '/i18n';
		if (!is_dir($baseDir))
		{
			return;
		}
		$xmlFiles = $this->scanDir($baseDir, '.xml');
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		foreach ($xmlFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$doc = $this->loadFormattedXMLDocument($splFileInfo->getPathname());
			$parts = explode('/', $path);
			list($lcId, ) = explode('.', array_pop($parts));
			$subKeyParts = implode('.', $parts);
			if ($subKeyParts != strtolower($subKeyParts))
			{
				$this->logger->logError('Invalid i18n file path : ' . $subKeyParts);
			}
			
			/* @var $de DOMElement */
			$de = $doc->documentElement;
			$de->setAttribute('baseKey', 'm.' . $this->moduleName . $subKeyParts);
			$de->setAttribute('lcid', $lcId);
			$ids = array();
			$toDelete = array();
			foreach ($doc->getElementsByTagName('key') as $element)
			{
				/* @var $element DOMElement */
				$id = strtolower($element->getAttribute('id'));
				$element->setAttribute('id', $id);
				if ($element->hasAttribute('updated'))
				{
					$element->removeAttribute('updated');
				}
				if (isset($ids[$id]))
				{
					$this->logger->logError('Duplicate id: ' . $id . ' in ' . $path);
					$toDelete[] = $ids[$id];
				}	
				$ids[$id] = $element;
			}
			
			foreach ($toDelete as $element)
			{
				/* @var $element DOMElement */
				$element->parentNode->replaceChild($doc->createComment($doc->saveXML($element)), $element);
			}
			
			$this->saveFormattedXMLDocument($doc, $splFileInfo->getPathname());
		}
	}
	
	protected function convertPersistentDocument()
	{
		$directory = $this->srcDirectory . '/persistentdocument';
		$xmlPaths = $this->scanDir($directory, '.xml');
		foreach ($xmlPaths as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			if (count(explode(DIRECTORY_SEPARATOR, $path)) > 2) {continue;}
			$this->convertXMLDocument($splFileInfo);
		}
		
		$phpFiles = $this->scanDir($directory, '.php');	
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		$classes = array(
			'getIndexedDocument' => array('t' => 'err'),
			'indexer_IndexableDocument' => array('t' => 'warn'),
			'indexer_BackofficeIndexedDocument' => array('t' => 'warn'),
			'addTreeAttributes' => array('t' => 'err'),
			'addFormProperties' => array('t' => 'err'),
		);
		foreach ($phpFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$classReplacer->setClasses($classes);
			$classReplacer->checkFile($splFileInfo->getPathname());
		}
	}
	
	/**
	 * @param SplFileInfo $splFileInfo
	 */
	private function convertXMLDocument($splFileInfo)
	{	
		$modelName = 'modules_' . $this->moduleName . '/' . $splFileInfo->getBasename('.xml');
		$km = md5_file($splFileInfo->getPathname());
		$doc = $this->loadFormattedXMLDocument($splFileInfo->getPathname());
		$this->replaceDocumentProperties($doc, $modelName);
		$this->saveFormattedXMLDocument($doc, $splFileInfo->getPathname());
		
		if ($km !== md5_file($splFileInfo->getPathname()))
		{
			$this->logger->logInfo('Convert xml document: ' . $modelName);
		}
	}
	
	/**
	 * @param DOMDocument $doc
	 */
	private function replaceDocumentProperties($doc, $modelName)
	{
		try
		{
			if ($doc->documentElement->localName !== 'document')
			{
				$this->logger->logError('Invalid xml document');
				return;
			}

			$schema = "http://www.rbs.fr/schema/change-document/1.0 http://www.rbschange.fr/schema/persistentdocument-4.0.xsd";
			$doc->documentElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', $schema);

			if ($doc->documentElement->hasAttribute('inject'))
			{
				$modelName = $doc->documentElement->getAttribute('inject');
			}
			if ($doc->documentElement->hasAttribute('linked-to-root-module'))
			{
				if ($doc->documentElement->getAttribute('linked-to-root-module') == 'false')
				{
					$doc->documentElement->removeAttribute('linked-to-root-module');
				}
				else
				{
					$this->logger->logError('Old "linked-to-root-module" attribute found on model ' . $modelName . '. It is not handeled any more, update your code to do it manually. Example in form_RecipientGroupService::postInsert().');
				}
			}
			if ($doc->documentElement->hasAttribute('publish-on-day-change'))
			{
				$doc->documentElement->setAttribute('use-publication-dates', $doc->documentElement->getAttribute('publish-on-day-change'));
				$doc->documentElement->removeAttribute('publish-on-day-change');
			}
			
			$forms = $doc->getElementsByTagName('form');
			if ($forms->length == 1 ) 
			{
				$forms = $forms->item(0);
				$forms->parentNode->removeChild($forms);
			}
				
			$properties = $doc->getElementsByTagName('properties');
			$this->replacePropertyList($properties, $modelName);
	
			$properties = $doc->getElementsByTagName('serializedproperties');
			$this->replacePropertyList($properties, $modelName);
				
			return true;
		}
		catch (Exception $e)
		{
			$this->logger->logError($e->getMessage());
		}
		return false;
	}
	
	/**
	 * @param DOMNodeList $properties
	 * @param string $modelName
	 */
	private function replacePropertyList($properties, $modelName)
	{
		if ($properties->length == 0)
		{
			return false;
		}
		$container = $properties->item(0);
		
		$list = $container->getElementsByTagName('add');
		while ($list->length > 0)
		{
			$element = $list->item(0);
				
			/* @var $element DOMElement */
			$domDoc = $element->ownerDocument;
				
			$property = $domDoc->createElement('property');
			foreach ($element->attributes as $attr)
			{
				$property->setAttribute($attr->nodeName, $attr->nodeValue);
			}
						
			$constraints = $element->getElementsByTagName('constraints');
			if ($constraints->length === 1)
			{
				$this->convertConstraints($constraints->item(0)->nodeValue, $property, $modelName);
			}
			$element->parentNode->replaceChild($property, $element);
		}
		
		$list = $container->getElementsByTagName('property');
		foreach ($list as $property)
		{
			/* @var $property DOMElement */
			if ($property->hasAttribute('type'))
			{
				$documentType = $property->getAttribute('type');
				if (strpos($documentType, 'modules_') === 0)
				{
					$property->setAttribute('document-type', $documentType);
					$maxOccurs = $property->hasAttribute('max-occurs') ? intval($property->getAttribute('max-occurs')) : 1;
					if ($maxOccurs == 1)
					{
						$property->setAttribute('type', 'Document');
						$property->removeAttribute('max-occurs');
					}
					else
					{
						$property->setAttribute('type', 'DocumentArray');
						if ($maxOccurs == -1)
						{
							$property->removeAttribute('max-occurs');
						}
					}
				}
			}
			
			if ($property->hasAttribute('min-occurs'))
			{
				$value = intval($property->getAttribute('min-occurs'));
				if ($value > 0 && $property->getAttribute('type') != 'Boolean')
				{
					$property->setAttribute('required', 'true');
				}
				
				if ($value < 2)
				{
					$property->removeAttribute('min-occurs');
				}
				elseif ($property->hasAttribute('type') && $property->getAttribute('type') != 'DocumentArray')
				{
					$property->removeAttribute('min-occurs');
				}
			}
			if ($property->hasAttribute('max-occurs'))
			{
				if ($property->hasAttribute('type') && $property->getAttribute('type') != 'DocumentArray')
				{
					$property->removeAttribute('max-occurs');
				}
			}
		}
		return true;
	}
	
	/**
	 * @param DOMElement $block
	 */
	private function migrateBlockParameters($block)
	{
		foreach ($block->getElementsByTagName('parameter') as $parameter)
		{
			/* @var $parameter DOMElement */
			if ($parameter->hasAttribute('type'))
			{
				$oldType = $parameter->getAttribute('type');
				if (strpos($oldType, 'modules_') === 0)
				{
					$parameter->setAttribute('document-type', $oldType);
					if (!$parameter->hasAttribute('max-occurs') || intval($parameter->getAttribute('max-occurs')) == 1)
					{
						$parameter->setAttribute('type', 'Document');
						$parameter->removeAttribute('max-occurs');
					}
					else
					{
						$parameter->setAttribute('type', 'DocumentArray');
						if (intval($parameter->getAttribute('max-occurs')) == -1)
						{
							$parameter->removeAttribute('max-occurs');
						}
					}
				}
			}
			else
			{
				$parameter->setAttribute('type', 'String');
			}
			
			if ($parameter->hasAttribute('list-id'))
			{
				if (!$parameter->hasAttribute('from-list'))
				{
					$parameter->setAttribute('from-list', $parameter->getAttribute('list-id'));
				}
				$parameter->removeAttribute('list-id');
			}
		}
	}
	
	/**
	 * @param DOMDocument $doc
	 */
	private function migrateBlockConstraints($doc)
	{
		try
		{
			$list = $doc->getElementsByTagName('constraints');
			$toRemove = array();
				
			foreach ($list as $constraints)
			{
				/* @var $constraints DOMElement */
				$parameter = $constraints->parentNode;
				if ($constraints->parentNode->localName === 'parameter')
				{
					$toRemove[] = $constraints;
					$this->convertConstraints($constraints->textContent, $parameter, null);
				}
			}
				
			if (count($toRemove))
			{
				foreach ($toRemove as $constraints)
				{
					$constraints->parentNode->removeChild($constraints);
				}
			}
		}
		catch (Exception $e)
		{
			$this->logger->logError($e->getMessage());
		}
	}
	
	/**
	 * @param string $definition
	 * @param DOMElement $property
	 * @param string $modelName
	 */
	private function convertConstraints($definition, $property, $modelName)
	{
		$cp = new validation_ContraintsParser();
		$defs = $cp->getConstraintArrayFromDefinition($definition);
		foreach ($defs as $name => $parameter)
		{
			if ($name === 'blank')
			{
				continue;
			}
				
			switch ($name)
			{
				case "email" :
					$params = array();
					break;
				case "min" :
				case "max" :
					$params = array($name => $parameter);
					break;
				case "maxSize" :
					$params = array("max" => $parameter);
					break;
				case "mixSize" :
					$params = array("mix" => $parameter);
					break;
				case "unique" :
					$params = array("propertyName" => $property->getAttribute('name'),
					"modelName" => $modelName);
					break;
				default :
					if ($parameter !== 'true')
					{
						$params = array('parameter' => $parameter);
					}
					else
					{
						$params = array();
					}
					break;
			}
				
			if ($name{0} == '!')
			{
				$name = substr($name, 1);
				$params['reversed'] = true;
			}
			$domDoc = $property->ownerDocument;
			$cn = $domDoc->createElement('constraint');
			$cn->setAttribute('name', $name);
			foreach ($params as $n => $v)
			{
				$cn->setAttribute($n, $v);
			}
			$property->appendChild($cn);
		}
	}
	
	protected function convertStyle()
	{
		$bocss = $this->srcDirectory . '/style/backoffice.css';
		if (file_exists($bocss))
		{
			$css = website_CSSStylesheet::getInstanceFromFile($bocss);
			foreach ($css->getCSSRules() as $rule)
			{
				/* @var $rule website_CSSRule */
				$matches = array();
				if (preg_match('/treechildren::-moz-tree-image\(modules_[a-zA-Z0-9_]+\)/', $rule->getSelectorText(), $matches))
				{
					$this->logger->logWarn('Style declaration for document icon detected in: ' . $bocss);
				}
			}
		}
	}
	
	protected function convertTemplates()
	{
		$filePaths = array_merge(
			$this->scanDir($this->srcDirectory . '/templates'),
			$this->scanDir(PROJECT_HOME . '/override/modules/'. $this->moduleName .'/templates'));
		
		foreach ($filePaths as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$name = $splFileInfo->getBasename();
			$p = explode('.', $name);
			$i = count($p);
			if ($i >= 4 && $p[$i-2] === 'all')
			{
				if ($p[$i-3] === 'all')
				{
					$newName = str_replace('.all.all.', '.', $splFileInfo->getPathname());
					rename($splFileInfo->getPathname(), $newName);
				}
				else
				{
					$this->logger->logWarn('This template need compatibility strategy: ' . $splFileInfo->getPathname());
				}
			}
		}	
		
		$PHPTALClassPath = PROJECT_HOME . '/libs/phptal/PHPTAL.php';
		require_once($PHPTALClassPath);
		
		if (!PHPTAL_Dom_Defs::getInstance()->isHandledNamespace(PHPTAL_Namespace_CHANGE::NAMESPACE_URI))
		{
			$changeNS = new PHPTAL_Namespace_CHANGE();
			PHPTAL_Dom_Defs::getInstance()->registerNamespace($changeNS);
			$registry = PHPTAL_TalesRegistry::getInstance();
			foreach (Framework::getConfigurationValue('tal/prefix') as $prefix => $class)
			{
				$registry->registerPrefix($prefix, array($class, $prefix));
			}
			$talpath  = $this->srcDirectory . '/lib/phptal';
			if (is_dir($talpath))
			{
				foreach ($this->scanDir($this->srcDirectory . '/lib/phptal', '.php') as $splFileInfo)
				{
					/* @var $splFileInfo SplFileInfo */
					require_once $splFileInfo->getPathname();
				}
				call_user_func(array($this->moduleName . '_PHPTAL_CHANGE', 'addAttributes'), $changeNS);
			}
		}
		
		$tempTalPath = PROJECT_HOME . '/cache/project/template/xx';
		if (is_dir($tempTalPath))
		{
			$this->rmdir($tempTalPath);
		}
		if (!@mkdir($tempTalPath, 0777, true))
		{
			$this->logger->logError('Unable to create tmp dir: ' . $tempTalPath);
			return;
		}

		$templateReplacer = new compatibility_TemplateReplacer($tempTalPath, $this->logger);
		
		$pathInfos = array_merge(
			$this->scanDir($this->srcDirectory . '/templates', '.html'),
			$this->scanDir($this->srcDirectory . '/lib/bindings', '.xml'),
			$this->scanDir(PROJECT_HOME . '/override/modules/'. $this->moduleName .'/templates', '.html'),
			$this->scanDir(PROJECT_HOME . '/override/modules/'. $this->moduleName .'/lib/bindings', '.xml'));
			
			
		foreach ($pathInfos as $path => $splFileInfo)
		{
			$templateReplacer->migrateTemplate($splFileInfo->getPathname());
		}
		
		/**
		 * @todo THEMES TEMPLATE
		 */
	}
	
	protected function convertServices()
	{
		$directory = $this->srcDirectory . '/lib/services';
		$phpFiles = $this->scanDir($directory, '.php');
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		foreach ($phpFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$classReplacer->convertPHPService($splFileInfo->getPathname());
		}		
	}
	
	protected function convertViews()
	{
		$directory = $this->srcDirectory . '/views';
		$phpFiles = $this->scanDir($directory, '.php');
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		foreach ($phpFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$classReplacer->convertPHPView($splFileInfo->getPathname());
		}		
	}
	
	
	protected function convertBlocks()
	{
		$directory = $this->srcDirectory . '/lib/blocks';
		$phpFiles = $this->scanDir($directory, '.php');
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		foreach ($phpFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$classReplacer->convertPHPBlock($splFileInfo->getPathname());
		}		
	}
	
	/**
	 * @param string[] $matches
	 * @return string
	 */
	private function panelsMatchesKey($matches)
	{
		$formatters = array('js');
		$key = $this->convertKey($matches[0], $formatters);
		return '${trans:' . $key . ',' . implode(',', $formatters) .'}';
	}
	
	/**
	 * @param string $filePath
	 * @return DOMDocument
	 */
	private function loadFormattedXMLDocument($filePath = null)
	{
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;
		$doc->preserveWhiteSpace = false;
		if ($filePath !== null)
		{
			$doc->load($filePath);
			$doc->encoding = 'UTF-8';
		}
		return $doc;
	}
	
	/**
	 * @param DOMDocument $doc
	 * @param string $filePath
	 */
	private function saveFormattedXMLDocument($doc, $filePath)
	{
		$content = $this->formattedXMLString($doc->saveXML());
		file_put_contents($filePath, $content);
	}
	
	/**
	 * @param string $xmlString
	 * @return string
	 */
	private function formattedXMLString($xmlString)
	{
		$xmlString = str_replace(array('  ', '"/>'), array("\t", '" />'), $xmlString);
		$xmlString = preg_replace('/\s+$/D', '', $xmlString);
		return $xmlString;
	}
	
	/**
	 * @param string $key
	 * @return $key
	 */
	private function convertKeyIgnoreFormat($key)
	{
		$formatters = array();
		return $this->convertKey($key, $formatters);
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
	 * @return string keyId
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
	
	/**
	 * @param string $basePath   
	 * @param string $ext
	 * @return array<string => SplFileInfo>
	 */
	private function scanDir($basePath, $ext = null)
	{
		$paths = array();
		if (!is_dir($basePath))
		{
			return $paths;
		}
		$d = new SplFileInfo($basePath);
		$basePath = $d->getPathname();
		$basePathLength = strlen($basePath);
		$extLength = ($ext !== null) ? strlen($ext) : 0;
		
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS));
		foreach ($objects as $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			if ($splFileInfo->isFile())
			{
				if ($extLength === 0 || substr($splFileInfo->getFilename(), -$extLength) === $ext)
				{
					$paths[substr($splFileInfo->getPathname(), $basePathLength)] = $splFileInfo;
				}
			}
		}
		return $paths;
	}
	
	/**
	 * @param string $directory
	 */
	private function rmdir($directory)
	{
		if (is_dir($directory))
		{
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $splFileInfo)
			{
				/* @var $splFileInfo SplFileInfo */
				if ($splFileInfo->isFile())
				{
					unlink($splFileInfo->getPathname());
				}
				else
				{
					rmdir($splFileInfo->getPathname());
				}
			}
			rmdir($directory);
		}
	}
}