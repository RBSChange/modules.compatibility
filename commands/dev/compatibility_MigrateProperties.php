<?php
/**
 * commands_compatibility_MigrateProperties
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateProperties extends c_ChangescriptCommand
{
	/**
	 * @return String
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
		return "Migrate document properties and constraints";
	}
	
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== Migrate document properties ==");
		$this->loadFramework();
		$packages = $this->getBootStrap()->getProjectDependencies();
		
		foreach ($packages as $cPackage)
		{
			/* @var $cPackage c_Package */
			if ($cPackage->isModule())
			{
				$basePath = f_util_FileUtils::buildPath($cPackage->getPath(), 'persistentdocument');
				$paths = $this->scanDir($basePath);
				foreach ($paths as $fullpath)
				{
					$this->replaceProperties($cPackage, $fullpath);
				}
				
				$fullpath = f_util_FileUtils::buildModulesPath($cPackage->getName(), 'config', 'blocks.xml');
				if (is_readable($fullpath))
				{
					$this->migrateBlockConstraints($fullpath);
				}
								
				$fullpath = f_util_FileUtils::buildOverridePath('modules', $cPackage->getName(), 'config', 'blocks.xml');
				if (is_readable($fullpath))
				{
					$this->migrateBlockConstraints($fullpath);
				}
				

			}
		}
		
		$this->quitOk("Command successfully executed");
	}
	
	private function scanDir($basePath, $scanExt = 'xml')
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
				if ($ext === $scanExt)
				{
					$paths[] = $splFileInfo->getPathname();
				}
			}
		}
		return $paths;
	}
	
	private function migrateBlockConstraints($fullpath)
	{
		try
		{
			$domDoc = f_util_DOMUtils::fromPath($fullpath);
			$list = $domDoc->getElementsByTagName('constraints');
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
				$domDoc->save($fullpath);
				echo "modified: ", $fullpath, PHP_EOL;
			}
		}
		catch (Exception $e)
		{
			$this->errorMessage($e->getMessage());
		}
	}
	
	/**
	 * @param c_Package $cPackage
	 * @param string $fullpath
	 */
	private function replaceProperties($cPackage, $fullpath)
	{
		try
		{
			$domDoc = f_util_DOMUtils::fromPath($fullpath);
			if ($domDoc->documentElement->localName !== 'document') 
			{
				return;
			}
			$modified = false;
			//http://www.w3.org/2001/XMLSchema-instance
			$schema = "http://www.rbs.fr/schema/change-document/1.0 http://www.rbschange.fr/static/schema/change-document/4.0.xsd";
			$oldSchema = $domDoc->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation');
			
			if ($schema !== $oldSchema)
			{
				$domDoc->documentElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', $schema);
				$modified = true;
			}
			
			if ($domDoc->documentElement->hasAttribute('inject'))
			{
				$modelName = $domDoc->documentElement->getAttribute('inject');
			}
			else
			{
				$modelName = 'modules_' . $cPackage->getName() . '/' . basename($fullpath, '.xml');
			}
			
			$properties = $domDoc->getElementsByTagName('properties');
			$modified = $this->replacePropertiList($properties, $modelName) || $modified;
			 
			$properties = $domDoc->getElementsByTagName('serializedproperties');
			$modified = $this->replacePropertiList($properties, $modelName) || $modified;
			
			if ($modified)
			{
				$domDoc->save($fullpath);
				echo "modified: ", $fullpath, PHP_EOL;
			}
		}
		catch (Exception $e)
		{
			$this->errorMessage($e->getMessage());
		}
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
	 * 
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
}