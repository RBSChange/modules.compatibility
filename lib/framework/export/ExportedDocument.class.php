<?php
/**
 * @deprecated
 */
class export_ExportedDocument
{	
	private $properties = array();
	private $lang;

	/**
	 * @deprecated
	 */
	public function setLang($lang)
	{
		$this->lang = $lang;
	}
	
	/**
	 * @deprecated
	 */
	public function getLang()
	{
		return $this->lang;
	}
	
	/**
	 * @deprecated
	 */
	public function setProperty($name, $value)
	{
		$this->properties[0][$name] = $value;
	}
		
	/**
	 * @deprecated
	 */
	public function getProperties()
	{
		return $this->properties;
	}
	
	/**
	 * @deprecated
	 */
	public function addChildProperties($array)
	{
		foreach ($array as $a)
		{
			$this->properties[] = $a;
		}
	}
}