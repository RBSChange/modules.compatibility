<?php
/**
 * @deprecated use change:img
 */
class PHPTAL_Php_Attribute_CHANGE_Webappimage extends PHPTAL_Php_Attribute
{
	public function start()
	{
		$expressions = $this->tag->generator->splitExpression($this->expression);
		$name = null;
		$folder = 'front';
		$urlattribute = 'src';

		// foreach attribute
		foreach ($expressions as $exp)
		{
			list($attribute, $value) = $this->parseSetExpression($exp);
			switch ($attribute)
			{
				case 'name':
					$name = $this->evaluate($value, true);
					break;
				case 'document':
					$folder = $value;
					break;
			}
		}

		$this->tag->attributes[$urlattribute] = '<?php echo PHPTAL_Php_Attribute_CHANGE_Webappimage::render('.$name.', \''.$folder.'\') ?>';
	}

	public function end()
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