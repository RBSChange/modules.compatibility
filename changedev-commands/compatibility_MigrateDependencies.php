<?php
/**
 * commands_compatibility_MigrateDependencies
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateDependencies extends commands_AbstractChangeCommand
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
		return "Migrate change.xml dependencies file";
	}
	
	
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== Migrate Dependencies ==");
		
		$paths = glob(PROJECT_HOME. '/themes/*/install.xml');
		foreach ($paths as $path) 
		{
			$themeName = basename(dirname($path));
			$doc = $this->getNewDomDocument();
			$doc->load($path);
			if ($doc->documentElement->nodeName === 'script')
			{
				$this->migrateTheme($themeName, $doc);
			}
		}
		
		$paths = glob(PROJECT_HOME. '/modules/*/change.xml');
		foreach ($paths as $path) 
		{
			$moduleName = basename(dirname($path));
			$doc = $this->getNewDomDocument();
			$doc->load($path);
			$this->migrateModule($moduleName, $doc);
		}

		$doc = $this->getNewDomDocument();
		$doc->load(PROJECT_HOME. '/framework/install.xml');
		$release = $doc->documentElement->getAttribute('version');
		
		$packages = $this->loadDependencies();

		$install = $this->getNewDomDocument();
		$install->loadXML('<?xml version="1.0" encoding="UTF-8"?><install></install>');
		$install->documentElement->setAttribute('version', $release);
		$installNode = $install->documentElement;
		$lastType = null;
		foreach ($packages as $p) 
		{
			/* @var $p c_Package */
			$type = $p->getType() ? $p->getType() : $p->getName();
			
			if ($type !== $lastType)
			{
				$lastType = $type;
				$installNode->appendChild($install->createComment('Packages : ' . $lastType));
			}
			$package = $installNode->appendChild($install->createElement('package'));
			$p->populateNode($package);		
		}
		
		$install->save(PROJECT_HOME. '/install.xml');
		$this->quitOk("Command successfully executed");
	}
	
	private function loadDependencies()
	{
		$packages = array();	
		//Framework
		$path = PROJECT_HOME. '/framework/install.xml';
		$addLocaly = $this->copyInProject(dirname($path), false);
		
		$doc = $this->getNewDomDocument();
		$doc->load($path);
		$p = c_Package::getInstanceFromPackageElement($doc->documentElement, PROJECT_HOME);
		$packages[] = $p;

		//Libs
		$paths = glob(PROJECT_HOME. '/libs/*/install.xml');
		foreach ($paths as $path) 
		{
			$copied = $this->copyInProject(dirname($path));
			$doc = $this->getNewDomDocument();
			$doc->load($path);
			$p = c_Package::getInstanceFromPackageElement($doc->documentElement, PROJECT_HOME);		
			$packages[] = $p;
			
			if ($addLocaly && !$copied && !$p->getDownloadURL())
			{
				$this->log('Mark ' . $p->getName() . ' as local lib');
				$p->setDownloadURL('none');
				$doc->documentElement->setAttribute('downloadURL', 'none');
				$doc->save($path);
			}
		}
		
		//Modules
		$paths = glob(PROJECT_HOME. '/modules/*/install.xml');
		foreach ($paths as $path) 
		{
			$copied = $this->copyInProject(dirname($path));
			$doc = $this->getNewDomDocument();
			$doc->load($path);
			$p = c_Package::getInstanceFromPackageElement($doc->documentElement, PROJECT_HOME);		
			$packages[] = $p;
			
			if ($addLocaly && !$copied && !$p->getDownloadURL())
			{
				$this->log('Mark ' . $p->getName() . ' as local module');
				$p->setDownloadURL('none');
				$doc->documentElement->setAttribute('downloadURL', 'none');
				$doc->save($path);
			}
		}
		
		//Themes
		$paths = glob(PROJECT_HOME. '/themes/*/install.xml');
		foreach ($paths as $path) 
		{
			$copied = $this->copyInProject(dirname($path));
			$doc = $this->getNewDomDocument();
			$doc->load($path);
			$p = c_Package::getInstanceFromPackageElement($doc->documentElement, PROJECT_HOME);		
			$packages[] = $p;
			
			if ($addLocaly && !$copied && !$p->getDownloadURL())
			{
				$this->log('Mark ' . $p->getName() . ' as local theme');
				$p->setDownloadURL('none');
				$doc->documentElement->setAttribute('downloadURL', 'none');
				$doc->save($path);
			}
		}
		
		if ($addLocaly)
		{
			$this->log('Update autoload ...');
			$this->getParent()->executeCommand('update-autoload');
		}
		return $packages;
	}
	
	/**
	 * 
	 * @param string $themeName
	 * @param DOMDocument $doc
	 */
	private function migrateTheme($themeName, $doc)
	{
		$installPath = PROJECT_HOME. '/themes/'.$themeName.'/install.xml';
		if ($doc->documentElement->nodeName !== 'script') {return;}
		echo "Migrate theme  : ", $themeName , PHP_EOL;
		
		$initPath = PROJECT_HOME. '/themes/'.$themeName.'/setup/init.xml';
		f_util_FileUtils::writeAndCreateContainer($initPath, $doc->saveXML());
		unlink($installPath);	
		$installXML = $this->getNewDomDocument();
		$installXML->loadXML('<?xml version="1.0" encoding="UTF-8"?><install type="themes"></install>');
		$installXML->documentElement->setAttribute('name', $themeName);
		$installXML->documentElement->setAttribute('version', '4.0');
		$installXML->save($installPath);
	}
	
	
	/**
	 * 
	 * @param string $moduleName
	 * @param DOMDocument $doc
	 */
	private function migrateModule($moduleName, $doc)
	{
		$installPath = PROJECT_HOME. '/modules/'.$moduleName.'/install.xml';	
		if (file_exists($installPath)) {return;}
		
		echo "Migrate module  : ", $moduleName , PHP_EOL;
		$version = '4.0.0';
		$dependenciesNode = null;
		foreach ($doc->documentElement->childNodes as $childNode) 
		{
			/* @var $childNode DOMElement */
			
			if ($childNode->nodeType === XML_ELEMENT_NODE)
			{
				if ($childNode->nodeName === 'version')
				{
					$version = trim($childNode->textContent);
				}
				elseif ($childNode->nodeName === 'dependencies')
				{
					$dependenciesNode = $childNode;
				}
			}
		}
		
		$installXML = $this->getNewDomDocument();
		$installXML->loadXML('<?xml version="1.0" encoding="UTF-8"?><install type="modules"></install>');
		$installXML->documentElement->setAttribute('name', $moduleName);
		$installXML->documentElement->setAttribute('version', $version);
		if ($dependenciesNode !== null)
		{
			foreach ($dependenciesNode->getElementsByTagNameNS('*', 'name') as $dependencyNameNode) 
			{
				/* @var $dependencyNameNode DOMElement */
				$name = trim($dependencyNameNode->textContent);
				if (strpos($name, 'change-module/') === 0)
				{
					$package = $installXML->documentElement->appendChild($installXML->createElement('package'));
					$package->setAttribute('type', 'modules');
					$package->setAttribute('name', str_replace('change-module/', '', $name));
				}
			}
		}

		$installXML->save($installPath);
	}	

	/**
	 * @return DOMDocument
	 */
	private function getNewDomDocument()
	{
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;
		$doc->preserveWhiteSpace = false;
		return $doc;
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
	
	/**
	 * @param string $packagePath
	 * @return boolean
	 */
	protected function copyInProject($packagePath)
	{
		if (is_link($packagePath))
		{
			$src = realpath(readlink($packagePath));
			$this->log("Copy $src in $packagePath");
			unlink($packagePath);
			f_util_FileUtils::cp($src, $packagePath);
			return true;
		}
		return false;
	}
}