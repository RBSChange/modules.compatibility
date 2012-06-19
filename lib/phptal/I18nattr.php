<?php
/**
 * @deprecated
 */
class PHPTAL_Php_Attribute_CHANGE_I18nattr extends PHPTAL_Php_Attribute
{

	/**
	 * @deprecated
	 */
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
    	Framework::error('Deprecated change:i18nattr TAL Attribute ' . TemplateObject::$lastTemplateFileName);
        $this->expression = str_replace('&amp;', '&', $this->expression);
        $attributes = explode(' ', $this->expression);        
        $lang = RequestContext::getInstance()->getLang();
        $substitutions = array();
        for ($i = 0 ; isset($attributes[$i+1]); $i += 2)
        {
        	$name = $attributes[$i];
        	$matches = array();
        	if (preg_match('/^([a-z]+)\[([a-zA-Z]+)\]$/', $name, $matches))
        	{
        		$paramName = $matches[1];
        		if (!isset($substitutions[$paramName]))
        		{
        			$substitutions[$paramName] = array();
        		}
        		$value = $attributes[$i+1];
        		
        		if ($value[0] == '\'')
        		{
        			$value = substr($value, 1, strlen($value)-3);
        		}
        		else
        		{
        			$value = "<?php echo ".$codewriter->evaluateExpression(substr($value, 0, -1))."; ?>";
        		}
        		$substitutions[$paramName][$matches[2]] = $value;
        		continue;
        	}
        	$key = $attributes[$i+1];
        	if (isset($substitutions[$name]))
        	{
        		$value = f_Locale::translate($key, $substitutions[$name], $lang);	
        	}
        	else
        	{
        		$value = f_Locale::translate($key, null, $lang);
        	}
        	$this->phpelement->setAttributeNS('', $name, '');
        	$this->phpelement->getAttributeNode($name)->setValueEscaped($value);
        }
    }

	/**
	 * @deprecated
	 */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
    }
}