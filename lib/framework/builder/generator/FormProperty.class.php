<?php
/**
 * @deprecated
 */
class generator_FormProperty
{
	private $model;
	private $name;
	private $controlType;
	private $required;
	private $display;
	private $attributes   = array();

	/**
	 * @deprecated
	 */
	private $parentProperty;

	/**
	 * @deprecated
	 */
	public function __construct($model)
	{
		$this->setModel($model);
	}

	/**
	 * @deprecated
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}

	/**
	 * @deprecated
	 */
	public function initialize($xmlElement)
	{
		foreach($xmlElement->attributes as $attribute)
		{
			$name = $attribute->nodeName;
			$value = $attribute->nodeValue;
			switch ($name)
			{
				case "name":
					$this->name = $value;
					break;
				case "display":
					switch (strtolower($value))
					{
						case 'hidden':
							$this->display = 'hidden';
							break;
						case 'readonly':
							$this->display = 'readonly';
							break;
						case 'editonce':
							$this->display = 'editonce';
							break;
						default:
							$this->display = 'edit';
							break;
					}

					break;
				case "required":
					$this->required = generator_PersistentModel::getBoolean($value);
					break;
				case "control-type":
					$this->controlType = $value;
					break;
				case "hidden":
					generator_PersistentModel::addMessage("Obsolete form attribute ". $this->model->getName() . " : '$name' => $value ");
					if (generator_PersistentModel::getBoolean($value))
					{
						$this->display = 'hidden';
					}
					break;
				case "readonly":
					generator_PersistentModel::addMessage("Obsolete form attribute ". $this->model->getName() . " : '$name' => $value ");
					if (generator_PersistentModel::getBoolean($value))
					{
						$this->display = 'readonly';
					}
					break;
				default:
					$this->attributes[$name] = $value;
					break;
			}
		}
	}

	/**
	 * @deprecated
	 */
	public function mergeGeneric($property)
	{
		if (is_null($this->display)) {$this->display = $property->display;}
		if (is_null($this->controlType)) {$this->controlType = $property->controlType;}
		foreach ($property->attributes as $name => $value)
		{
			if (!array_key_exists($name, $this->attributes))
			{
				$this->attributes[$name] = $value;
			}
		}
	}

	/**
	 * @deprecated
	 */
	public function setParentProperty($parentProperty)
	{
		$this->parentProperty = $parentProperty;
	}

	/**
	 * @deprecated
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @deprecated
	 */
	public function getSerializedAttributes()
	{
		return serialize($this->attributes);
	}

	/**
	 * @deprecated
	 */
	public function getLabel()
	{
		return '&modules.'.$this->model->getFinalModuleName().'.document.'.$this->model->getFinalDocumentName().'.'. ucfirst($this->name).';';
	}

	/**
	 * @deprecated
	 */
	public function override($property)
	{
		if (!is_null($property->display)) {$this->display = $property->display;}
		if (!is_null($property->controlType)) {$this->controlType = $property->controlType;}
		if (!is_null($property->required)) {$this->required = $property->required;}
		foreach ($property->attributes as $name => $value)
		{
			$this->attributes[$name] = $value;
		}
	}

	/**
	 * @deprecated
	 */
	public function linkTo($property)
	{
		$this->name = $property->getName();

		$fromList = $property->getFromList();

		// intcours - 03/07/2007 - don't override controlType if it's already set :
		if (!is_null($fromList))
		{
			$this->attributes['list-id'] = $fromList;
			if (!$this->controlType)
			{
			    $this->controlType = 'list';
			}
		}
		else if (!$this->controlType)
		{
    		if ($property->isDocument())
    		{
    			$this->controlType = 'picker';
    		}
    		else
    		{
    			switch ($property->getType())
    			{
    				case f_persistentdocument_PersistentDocument::PROPERTYTYPE_DATETIME:
    					$this->controlType = 'date';
    					break;
    				case f_persistentdocument_PersistentDocument::PROPERTYTYPE_BOOLEAN:
    					$this->controlType = 'boolean';
    					break;
    				case f_persistentdocument_PersistentDocument::PROPERTYTYPE_DOUBLE :
    				case f_persistentdocument_PersistentDocument::PROPERTYTYPE_INTEGER:
    					$this->controlType = 'number';
    					break;
    				default:
    					$this->controlType = 'text';
    					break;
    			}
    		}
		}
	}

	/**
	 * @deprecated
	 */
	public function getControlType()
	{
		return $this->controlType;
	}

	/**
	 * @deprecated
	 */
	public function getDisplay()
	{
		if (is_null($this->display))
		{
			if ($this->name == 'correctionid' || $this->name == 'correctionofid')
			{
				return 'hidden';
			}
			return 'edit';
		}
		return $this->display;
	}

	/**
	 * @deprecated
	 */
	public function isRequired()
	{
		return $this->required === true;
	}
}