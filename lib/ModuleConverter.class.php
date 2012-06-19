<?php
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
		
		// convert: patch
		
		// convert: persistentdocument
		$this->convertPersistentDocument();
		
		// convert: setup
		
		// convert: style
		
		// convert: templates
		$this->convertTemplates();
		
		// convert: views
		
		// convert: webapp
		
		// convert: lib
		
		// convert: lib/bindings
		
		// convert: lib/blocks
		
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
				if ($element->hasAttribute('label'))
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
			$content = str_replace(array('  ', '"/>'), array("\t", '" />'), $doc->saveXML());
			
			$content = preg_replace_callback('/&(modules|framework|themes|m|f|t)(\.[a-zA-Z0-9]+)+;/', array($this, 'panelsMatchesKey'), $content);
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
		$phpFiles = $this->scanDir($this->srcDirectory, '.xml');
		$classReplacer = new compatibility_ClassReplacer(array(), $this->logger);
		foreach ($phpFiles as $path => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			$classReplacer->convertXMLFile($splFileInfo->getPathname());
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
	}
	
	/**
	 * @param SplFileInfo $splFileInfo
	 */
	private function convertXMLDocument($splFileInfo)
	{	
		$modelName = 'modules_' . $this->moduleName . '/' . $splFileInfo->getBasename('.xml');
		$this->logger->logInfo('Convert xml document: ' . $modelName);
		$doc = $this->loadFormattedXMLDocument($splFileInfo->getPathname());
		
		if ($this->replaceDocumentProperties($doc, $modelName))
		{
			$this->saveFormattedXMLDocument($doc, $splFileInfo->getPathname());
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

			$schema = "http://www.rbs.fr/schema/change-document/1.0 http://www.rbschange.fr/static/schema/change-document/4.0.xsd";
			$doc->documentElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', $schema);

			if ($doc->documentElement->hasAttribute('inject'))
			{
				$modelName = $doc->documentElement->getAttribute('inject');
			}
			
			$forms = $doc->getElementsByTagName('form');
			if ($forms->length == 1 ) 
			{
				$forms = $forms->item(0);
				$forms->parentNode->removeChild($forms);
			}
				
			$properties = $doc->getElementsByTagName('properties');
			$this->replacePropertiList($properties, $modelName);
	
			$properties = $doc->getElementsByTagName('serializedproperties');
			$this->replacePropertiList($properties, $modelName);
				
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
	private function replacePropertiList($properties, $modelName)
	{
		if ($properties->length == 0)
		{
			return false;
		}
		$list = $properties->item(0)->getElementsByTagName('add');
		if ($list->length == 0)
		{
			return false;
		}
	
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
		return true;
	}
	
	/**
	 * @param DOMDocument $doc
	 * @return boolean;
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
			return true;
		}
		catch (Exception $e)
		{
			$this->logger->logError($e->getMessage());
		}
		return false;
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
	
	
	protected function convertTemplates()
	{
		$PHPTALClassPath = PROJECT_HOME . '/libs/phptal/PHPTAL.php';
		require_once($PHPTALClassPath);
		
		if (!PHPTAL_Dom_Defs::getInstance()->isHandledNamespace(PHPTAL_Namespace_CHANGE::NAMESPACE_URI))
		{
			PHPTAL_Dom_Defs::getInstance()->registerNamespace(new PHPTAL_Namespace_CHANGE());
			$registry = PHPTAL_TalesRegistry::getInstance();
			foreach (Framework::getConfigurationValue('tal/prefix') as $prefix => $class)
			{
				$registry->registerPrefix($prefix, array($class, $prefix));
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
		
		$pathInfos = $this->scanDir($this->srcDirectory . '/templates', '.html');
		foreach ($pathInfos as $path => $splFileInfo)
		{
			if (!$templateReplacer->migrateTemplate($splFileInfo->getPathname()))
			{
				$this->logger->logError('Invalid Template: ' . $path);
			}
		}
		
		$pathInfos = $this->scanDir($this->srcDirectory . '/lib/bindings', '.xml');
		foreach ($pathInfos as $path => $splFileInfo)
		{
			if (!$templateReplacer->migrateTemplate($splFileInfo->getPathname()))
			{
				$this->logger->logError('Invalid Template: ' . $path);
			}
		}
				
		$overPath = PROJECT_HOME . '/override/modules/'. $this->moduleName .'/templates';
		if (is_dir($overPath))
		{
			foreach ($this->scanDir($overPath, '.html') as $path => $splFileInfo)
			{
				if (!$templateReplacer->migrateTemplate($splFileInfo->getPathname()))
				{
					$this->logger->logError('Invalid Template: ' . $path);
				}
			}
		}
		
		$overPath = PROJECT_HOME . '/override/modules/'. $this->moduleName .'/lib/bindings';
		if (is_dir($overPath))
		{
			foreach ($this->scanDir($overPath, '.xml') as $path => $splFileInfo)
			{
				if (!$templateReplacer->migrateTemplate($splFileInfo->getPathname()))
				{
					$this->logger->logError('Invalid Template: ' . $path);
				}
			}
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
		}
		return $doc;
	}
	
	/**
	 * @param DOMDocument $doc
	 * @param string $filePath
	 */
	private function saveFormattedXMLDocument($doc, $filePath)
	{
		$content = str_replace(array('  ', '"/>'), array("\t", '" />'), $doc->saveXML());
		file_put_contents($filePath, $content);
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