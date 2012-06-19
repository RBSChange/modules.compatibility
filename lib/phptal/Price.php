<?php
/**
 * @deprecated
 */
class PHPTAL_Php_Attribute_CHANGE_Price extends PHPTAL_Php_Attribute
{
	/**
	 * @deprecated
	 */
    public function start()
    {
    	$format = "names";

    	$this->expression = $this->extractEchoType($this->expression);

    	$expressions = $this->tag->generator->splitExpression($this->expression);
		$currencyPosition = 'null';
		$currency = 'null';
		$priceValue = 'null';
    	foreach ($expressions as $exp)
    	{
			list($attribute, $value) = $this->parseSetExpression($exp);
			switch ($attribute)
			{
				case 'currencyPosition' :				
					$currencyPosition = $this->evaluate($value);
					break;
				case 'currency' :				
					$currency = $this->evaluate($value);
					break;
				case 'value' :
					$priceValue = $this->evaluate($value);
					break;
    		}
    	}
    	$code = $this->_getCode($priceValue, $currency, $currencyPosition);
		$this->doEcho($code);
    }

    protected function _getCode($priceValue, $currency, $currencyPosition)
    {
		$code = 'PHPTAL_Php_Attribute_CHANGE_price::_getPrice(' . $priceValue . ', ' . $currency . ', ' . $currencyPosition . ')';
		return $code;
    }

	/**
	 * @deprecated
	 */
    public static function _getPrice($priceValue, $currency, $currencyPosition)
    {
		// TODO : Manage different formats based on locale...
    	$priceValue = number_format($priceValue, 2, ',', ' ');
    	
		$price = '';
		if (!empty($currency))
		{
			switch ($currencyPosition)
			{
				case 'left' : 
					$price = $currency . ' ' . $priceValue;
					break;
				
				case 'right' :
				default :
					$price = $priceValue . ' ' . $currency;
					break;
			}
		}
		else
		{
			$price = $priceValue;
		}
	    return $price;
    }

	/**
	 * @deprecated
	 */
    public function end()
    {
    }
}