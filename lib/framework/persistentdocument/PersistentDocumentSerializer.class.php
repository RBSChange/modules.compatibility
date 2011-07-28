<?php
/**
 * @deprecated
 */
class SerialisationContext
{
	const DIRECTION_IN = 'IN';
	const DIRECTION_OUT = 'OUT';

	/**
	 * @deprecated
	 */
	private $document;
	
	/**
	 * @deprecated
	 */
	private $direction = 'OUT';
	
	/**
	 * @deprecated
	 */
	public function __construct($direction)
	{
		$this->direction = $direction;
	}
	
	/**
	 * @deprecated
	 */
	public function getDirection()
	{
		return $this->direction;
	}
	
	/**
	 * @deprecated
	 */
	public function setDocument($document)
	{
		$this->document = $document;
	}
	
	/**
	 * @deprecated
	 */
	public function getDocument()
	{
		if (is_null($this->document))
		{
			$this->document = new DOMDocument('1.0', 'UTF-8');
		}
		return $this->document;
	}
}

/**
 * @deprecated
 */
class f_persistentdocument_PersistentDocumentSerializer
{
	/**
	 * @deprecated
	 */
	private $document;
	
	/**
	 * @deprecated
	 */
	private $element;
	
	/**
	 * @deprecated
	 */
	public function serialize($persistentDocument, $context)
	{
		$this->document = $context->getDocument();
		$this->element = $this->createProperty('document');
		$lang = RequestContext::getInstance()->getLang();
		
		$properties = $persistentDocument->getPersistentModel()->getPropertiesInfo();
		$this->setAttribute($this->element, 'id', $persistentDocument->getId());
		$this->setAttribute($this->element, 'model', $persistentDocument->getDocumentModelName());
		$this->setAttribute($this->element, 'lang', $lang);
		
		foreach ($properties as $propertyName => $propertyInfo)
		{
			if ($propertyName == 'id' || $propertyName == 'model' || $propertyName == 'lang')
			{
				continue;
			}
			
			$propertyName = ucfirst($propertyName);
			if (!$propertyInfo->isDocument())
			{
				if (DocumentHelper::isLobProperty($propertyInfo->getType()))
				{
					$element = $this->addProperty($this->createLobProperty($propertyInfo->name, $this->{'get'.$propertyName}()));
				}
				else
				{
					$element = $this->addProperty($this->createProperty($propertyInfo->name, $this->{'get'.$propertyName}()));
				}
				
				
			}
			else
			{
				if (!$propertyInfo->isArray())
				{
					$value = $this->{'get'.$propertyName}();
					if (!is_null($value))
					{
						$this->createDocumentProperty($propertyInfo->name, $value);
					}
				}
				else
				{
					$array = $this->{'get'.$propertyName.'Array'}();
					$this->createDocumentPropertyArray($propertyInfo->name, $array);	
				}
			}
		}			
		
		return $doc->saveXML();
	}
	
	/**
	 * @deprecated
	 */
	private function setAttribute($element, $name, $value)
	{
		$element->setAttribute($name, $value);
		return $element;
	}
	
	/**
	 * @deprecated
	 */
	private function addProperty($property)
	{
		$this->element->appendChild($property);
		return $property;
	}
	
	/**
	 * @deprecated
	 */
	private function createProperty($name, $value)
	{
		$element = $this->document->createElement('component', $value);
		return $this->setAttribute($element, 'name', $name);
	}
	
	/**
	 * @deprecated
	 */
	private function createLobProperty($name, $value)
	{
		$element = $this->createProperty($name, null);
		$element->appendChild($this->document->createCDATASection($value));
	}
	
	/**
	 * @deprecated
	 */
	private function createDocumentProperty($name, $value)
	{
		$element = $this->createProperty($name, null);
		if (!is_null($value))
		{
			$doc = $this->createProperty('document', $value->getLabel());
			$this->setAttribute($doc, 'id', $value->getId());
			$this->setAttribute($doc, 'model', $value->getDocumentModelName());
			$element->appendChild($doc);
		}
		return $element;
	}
	
	/**
	 * @deprecated
	 */
	private function createDocumentPropertyArray($name, $array)
	{
		$element = $this->createProperty($name, null);
		foreach ($array as $value)
		{
			$doc = $this->createProperty('document', $value->getLabel());
			$this->setAttribute($doc, 'id', $value->getId());
			$this->setAttribute($doc, 'model', $value->getDocumentModelName());
			$element->appendChild($doc);			
		}
		return $element;
	}	
}