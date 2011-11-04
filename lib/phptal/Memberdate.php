<?php
/**
 * @deprecated use date and datetime prefixes instead
 */
class PHPTAL_Php_Attribute_CHANGE_Memberdate extends ChangeTalAttribute 
{	
	/**
	 * @deprecated
	 */
	protected function getDefaultParameterName()
	{
		return 'value';
	}
	
	/**
	 * @deprecated
	 */
	public function getEvaluatedParameters()
	{
		return array('mode', 'value');
	}
	
	/**
	 * @deprecated
	 */
	public static function renderMemberdate($params)
	{
		$date = date_Calendar::getInstance(self::getDateFromParams($params));		
		$uiDate = date_Converter::convertDateToLocal($date);
		$mode = (array_key_exists('mode', $params)) ? $params['mode'] : 'long';
		if ($mode === 'long')
		{
			return date_Formatter::toDefaultDateTime($uiDate);
		}
    	return date_Formatter::toDefaultDate($uiDate);
	}

	/**
	 * @deprecated
	 */
	private static function getDateFromParams($params)
	{
		return (array_key_exists('value', $params)) ? $params['value'] : null;
	}
}