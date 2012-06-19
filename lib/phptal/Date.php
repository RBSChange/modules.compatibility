<?php
/**
 * @deprecated use date and datetime prefixes
 */
class PHPTAL_Php_Attribute_CHANGE_Date extends ChangeTalAttribute 
{	
	/**
	 * @see ChangeTalAttribute::getDefaultParameterName()
	 *
	 * @return String
	 */
	protected function getDefaultParameterName()
	{
		return 'date';
	}

	/**
	 * @deprecated
	 */
	public function getDefaultValues()
	{
		return array('format' => 'names');
	}

	/**
	 * @deprecated
	 */
	public function getEvaluatedParameters()
	{
		return array('format', 'date', 'value');
	}

	/**
	 * @deprecated
	 */
	public static function renderDate($params, $dropTimeInfo = true)
	{
		$dateValue = self::getDateFromParams($params);
		if ($dateValue === null)
		{
			return "";
		}
		if ($dateValue === false)
		{
			$date = date_Calendar::getInstance($dateValue);
		}
		else
		{
			$date = date_Calendar::getInstance($dateValue);
		}
		
		$uiDate = date_Converter::convertDateToLocal($date);
		if (isset($params['formatI18n']))
		{
			return date_DateFormat::format($uiDate, f_Locale::translate('&' . $params['formatI18n'] . ';'));
		}
		$format = $params['format'];
    	if ($format == "names")
		{
			if ($dropTimeInfo)
			{
				$dateStr = date_DateFormat::smartFormat($uiDate, date_DateFormat::FORMAT_WITHOUT_TIME);
			}
			else 
			{	
				$dateStr = date_DateFormat::smartFormat($uiDate);
			}
		}
		else
		{
			if ($format == "classic" || $format === null) 
			{
				if ($dropTimeInfo)
				{
					$format = date_Formatter::getDefaultDateFormat(RequestContext::getInstance()->getLang());
				}
				else
				{
					$format = date_Formatter::getDefaultDateTimeFormat(RequestContext::getInstance()->getLang());
				}
			}
			$dateStr = date_Formatter::format($uiDate, $format);
		}
    	return $dateStr;
	}
	
	private static function getDateFromParams($params)
	{
		$rawDate = false;
		if (array_key_exists('value', $params))
		{
			$rawDate = $params['value'];
		}
		
		if (array_key_exists('date', $params))
		{
			$rawDate = $params['date'];
		}
		return $rawDate;
	}
}

/**
 * @deprecated
 */
class PHPTAL_Php_Attribute_CHANGE_Datetime extends PHPTAL_Php_Attribute_CHANGE_date
{

	/**
	 * @deprecated
	 */		
	public static function renderDateTime($params)
	{
		return self::renderDate($params, false);
	}
}