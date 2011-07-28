<?php
/**
 * @deprecated
 */
class FormPropertyInfo
{
	private $name;
	private $controlType;
	private $display;
	private $required;
	private $label;
	private $attributes;

	/**
	 * @deprecated
	 */
	function __construct($name, $controlType, $display, $required, $label, $attributes)
	{
		$this->name = $name;
		$this->controlType = $controlType;
		$this->display = $display;
		$this->required = $required;
		$this->label = $label;
		if (is_string($attributes))
		{
			$this->attributes = unserialize($attributes);
		}
		else if (is_array($attributes))
		{
			$this->attributes = $attributes;
		}
		else
		{
			$this->attributes = array();
		}
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
	public function getControlType()
	{
		return $this->controlType;
	}

	/**
	 * @deprecated
	 */
	public function isHidden()
	{
		return 'hidden' == $this->display;
	}

	/**
	 * @deprecated
	 */
	public function isReadonly()
	{
		return 'readonly' == $this->display;
	}

	/**
	 * @deprecated
	 */
	public function isEditOnce()
	{
		return 'editonce' == $this->display;
	}

	/**
	 * @deprecated
	 */
	public function isRequired()
	{
		return $this->required;
	}

	/**
	 * @deprecated
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @deprecated
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}
}