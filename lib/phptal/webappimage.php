<?php
/**
 * @deprecated use change:img
 */
class PHPTAL_Php_Attribute_CHANGE_Webappimage extends PHPTAL_Php_Attribute
{
    /**
     * Called before element printing.
     */
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
    	Framework::error('Deprecated change:webappimage TAL Attribute ' . TemplateObject::$lastTemplateFileName);
		$expressions = $codewriter->splitExpression($this->expression);
		$name = 'null';
		$folder = 'front';
		$urlattribute = 'src';

		// foreach attribute
		foreach ($expressions as $exp)
		{
			list($attribute, $value) = $this->parseSetExpression($exp);
			switch ($attribute)
			{
				case 'name':
					$name = $codewriter->evaluateExpression($value);
					break;
				case 'document':
					$folder = $value;
					break;
			}
		}
		$this->phpelement->getOrCreateAttributeNode($urlattribute)
			->setValueEscaped('<?php echo PHPTAL_Php_Attribute_CHANGE_Webappimage::render('.$name.', \''.$folder.'\') ?>');
	}

	/**
     * Called after element printing.
     */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
	}

	public static function render($name, $folder)
	{
		$imgname = str_replace("{lang}", RequestContext::getInstance()->getLang() , $name);
		switch ($folder)
		{
			case 'front':
				$result = MediaHelper::getFrontofficeStaticUrl($imgname, K::XML);
				break;
			case 'back':
				$result = MediaHelper::getBackofficeStaticUrl($imgname, K::XML);
				break;
			default:
				$result = MediaHelper::getStaticUrl($imgname, K::XML);
				break;
		}
		return $result;
	}
}