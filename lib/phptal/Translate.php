<?php
/**
 * <p change:translate="modules_test.toto.tiltle"
 * <p change:translate="modules_test.toto.tiltle2;remp1 'toto';remp2 item/label"
 * <p change:translate="modules_test.toto.tiltle; ui true"
 */
class PHPTAL_Php_Attribute_CHANGE_Translate extends PHPTAL_Php_Attribute
{
	
	private $ui = false;
	
	/**
     * Called before element printing.
     */
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
    	Framework::error('Deprecated change:translate TAL Attribute ' . TemplateObject::$lastTemplateFileName);
    	
    	$expression = trim(str_replace('&amp;', '&', $this->expression));

    	$matchs = array();
    	if (!preg_match('/^' . f_Locale::LOOSE_LOCALE_KEY_REGEXP . '/', $expression, $matchs))
    	{
    		return;
    	}
    	$key = '&'.$matchs[1].';';
    	$remplacement = substr($expression, strlen($matchs[0]));

    	$remplacementArray = array();

    	if (!empty($remplacement))
    	{
    		$expressions = $codewriter->splitExpression($remplacement);
	         // foreach attribute
	        foreach ($expressions as $exp)
	        {
	        	if (empty($exp)) { continue;}

	            list($name, $value) = $this->parseSetExpression($exp);
	            if ($name === 'ui')
	            {
	            	$this->ui = $value === 'true';	
	            }
	            else
	            {
	            	$remplacementArray[$name] = $codewriter->evaluateExpression($value);
	            }
	        }
    	}

    	if (count($remplacementArray) == 0)
    	{
    		if ($this->ui)
    		{
    			$codewriter->pushHtml(f_Locale::translateUI($key));
    		}
    		else
    		{
    			$codewriter->pushHtml(f_Locale::translate($key));
    		}
    	}
    	else
    	{
  			if ($this->ui)
  			{
  				$code = 'echo PHPTAL_Php_Attribute_CHANGE_Translate::_translateUI(\''. f_util_StringUtils::quoteSingle($key) .'\', array(';
  			}
  			else
  			{
				$string = f_Locale::translate($key);
				$code = 'echo PHPTAL_Php_Attribute_CHANGE_Translate::_translate(\''. f_util_StringUtils::quoteSingle($string) .'\', array(';
  			}
  			
			foreach ($remplacementArray as $key => $value)
			{
			    $code .= '\''.$key.'\' => '. $value .', ';
			}
			$code .= '));';
			$codewriter->pushCode($code);
    	}
    }

	/**
     * Called after element printing.
     */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
    }

    public static function _translate($string, $remplacements)
    {	
	   foreach ($remplacements as $key => $value)
	   {
	        $string = str_replace('{'.$key.'}', $value, $string);
	   }
	   return $string;
    }
    
    public static function _translateUI($key, $remplacements)
    {
       $string = f_Locale::translateUI($key);
	   foreach ($remplacements as $key => $value)
	   {
	        $string = str_replace('{'.$key.'}', $value, $string);
	   }
	   return $string;
    }
}