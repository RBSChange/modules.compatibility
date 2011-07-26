<?php
/**
 * @deprecated
 */
class ComponentTypeObject
{
	const MODULE    = "modules";
	const FRAMEWORK = "framework";
	
	/**
	 * @deprecated
	 */
	public $package;
	
	/**
	 * @deprecated
	 */
	public $packageType;
	
	/**
	 * @deprecated
	 */
	public $packageName;
	
	/**
	 * @deprecated
	 */
	public $componentType;
	
	/**
	 * @deprecated
	 */
	public $fullComponentType;
	
	/**
	 * @deprecated
	 */
	private function __construct($component)
	{
		if ( is_numeric($component) )
		{
			$component = f_persistentdocument_PersistentProvider::getInstance()->getDocumentModelName($component);
		}
		else if ($component instanceof f_persistentdocument_PersistentDocument)
		{
			$component = $component->getDocumentModelName();
		}
		$this->fullComponentType = $component;
		
		$matches = array();
		if (preg_match('#^(.*)/([\w_\-]+)$#', $component, $matches)) {
			$this->componentType = $matches[2];
			$this->package       = $matches[1];
			$tt = explode("_", $this->package);

			if ($tt[0] == "framework")
			{
				$this->packageType = self::FRAMEWORK;
				$this->packageName = "framework";
			}
			else
			{
				$this->packageType = $tt[0];
				$this->packageName = $tt[1];
			}
		}
		else
		{
			$e = new BaseException("invalid_component_type");
			$e->setAttribute('component', $component);
			throw $e;
		}
	}
	
	/**
	 * @deprecated
	 */
	public static function getInstance($componentType)
	{
		return new ComponentTypeObject($componentType);
	}

	/**
	 * @deprecated
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * @deprecated
	 */
	public function getPackageType()
	{
		return $this->packageType;
	}

	/**
	 * @deprecated
	 */
	public function getPackageName()
	{
		return $this->packageName;
	}

	/**
	 * @deprecated
	 */
	public function getComponentType()
	{
		return $this->componentType;
	}

	/**
	 * @deprecated
	 */
	public function getFullComponentType()
	{
		return $this->fullComponentType;
	}
}