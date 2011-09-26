<?php
/**
 * Migrate Richtext Xml
 * compatibility.migrate-richtext-xml
 * @package compatibility
 */
class commands_compatibility_MigrateRichtextXml extends c_ChangescriptCommand
{
	/**
	 * @return String
	 */
	public function getUsage()
	{
		return "";
	}
	
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 */
	public function _execute($params, $options)
	{
		$this->message("== Migrate richtext configuration ==");

		$this->loadFramework();
		foreach (array('generic', 'website') as $moduleName)
		{
			$paths = FileResolver::getInstance()->setPackageName('modules_' . $moduleName)->setDirectory('config')->getPaths('richtext.xml');
			if (is_array($paths))
			{
				foreach ($paths as $path)
				{
					echo ' - Migrate ', $path, "\n";
					$this->handleFile($path);
				}
			}
		}
		
		if (file_exists(f_util_FileUtils::buildOverridePath('modules', 'generic', 'config', 'richtext.xml')))
		{
			$this->warnMessage('You have richtext style definitions in override/modules/generic/config/richtext.xml. Since 4.0, these definitions should only be done in website module, so you have to move them.');
		}

		$this->quitOk("Command successfully executed");
	}

	/**
	 * @return String
	 * @example "initialize a document"
	 */
	public function getDescription()
	{
		return "Migrate richtext configuration";
	}
	
	/**
	 * @param string $path
	 */
	private function handleFile($path)
	{
		$oldDoc = $this->getNewDomDocument();
		if (!$oldDoc->load($path))
		{
			echo '   ERROR: invalid XML', "\n";
			return;
		}
		
		$doc = $this->getNewDomDocument();
		$doc->loadXML('<?xml version="1.0" encoding="UTF-8"?><styles></styles>');
		$release = $doc->documentElement->getAttribute('version');
		
		$ignored = 0;
		$modified = 0;
		foreach ($oldDoc->getElementsByTagName('style') as $style) 
		{
			/* @var $style DOMElement */
			$node = $doc->createElement('style');
			
			$tagName = $style->getAttribute('tag');
			if (!$tagName) { $ignored++; continue; }
			$node->setAttribute('tag', $tagName);
			
			if (!$node->hasAttribute('class'))
			{
				foreach ($style->getElementsByTagName('attribute') as $attr)
				{
					if ($attr->getAttribute('name') == 'class' && $attr->getAttribute('value'))
					{
						$node->setAttribute('class', $attr->getAttribute('value'));
					}
				}
				if (!$node->hasAttribute('class')) { $ignored++; continue; }
				$modified++;
			}
			
			$block = $style->getAttribute('block');
			if ($block == 'false')
			{
				$node->setAttribute('block', 'false');
			}
			elseif ($block) { $modified++; }

			$label = $style->getAttribute('label');
			if (!$label && !$style->hasAttribute('labeli18n')) { $ignored++; continue; }
			elseif (f_Locale::isLocaleKey($label))
			{
				$node->setAttribute('labeli18n', $this->convertKey($label));
				$modified++;
			}
			else
			{
				$node->setAttribute('label', $label);				
			}
			
			$doc->documentElement->appendChild($node);
		}
		
		if ($modified > 0)
		{
			rename($path, $path . '.old');
			$doc->save($path);
			
			echo '   File migrated ingoring ', $ignored, ' invalid styles', "\n";
		}
		else
		{
			echo '   Already clean', "\n";
		}
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
}