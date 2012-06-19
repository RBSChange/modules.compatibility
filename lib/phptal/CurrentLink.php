<?php
/**
 * @deprecated
 */
class PHPTAL_Php_Attribute_CHANGE_Currentlink extends PHPTAL_Php_Attribute
{

	/**
	 * @deprecated
	 */
	public function start()
	{
		Framework::error('Deprecated change:currentlink TAL Attribute ' . TemplateObject::$lastTemplateFileName);
		$tagName = strtolower($this->tag->name);
		$attrName = null;
		if ($tagName == 'form')
		{
			$attrName = 'action';
		}
		else
		{
			$attrName = 'href';
		}
		
		$extraAttributes = array();

		if (!f_util_StringUtils::isEmpty($this->expression))
		{	
			$expressions = $this->tag->generator->splitExpression($this->expression);
			foreach ($expressions as $exp)
			{
				list($parameterName, $value) = $this->parseSetExpression($exp);
				$extraAttributes[] = "'" . $parameterName ."' => " . $this->evaluate($value);
			}
		}

		$this->tag->attributes[$attrName] = $this->getHrefCode("array(".join(", ", $extraAttributes).")");
		$this->tag->attributes['class'] = 'link';
	}

	/**
	 * @deprecated
	 */
	public function end()
	{
	}

	/**
	 * @deprecated
	 */
	public function getHrefCode($extraAttributes)
	{
		return '<?php echo LinkHelper::getCurrentUrl('. $extraAttributes .'); ?>';
	}
}