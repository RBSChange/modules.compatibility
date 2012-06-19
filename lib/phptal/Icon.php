<?php
/**
 * @deprecated use change:img
 */
class PHPTAL_Php_Attribute_CHANGE_Icon extends PHPTAL_Php_Attribute
{

	/**
	 * @deprecated
	 */
	public function before(PHPTAL_Php_CodeWriter $codewriter)
	{
		Framework::error('Deprecated change:icon TAL Attribute ' . TemplateObject::$lastTemplateFileName);
		$exp = trim($this->expression);
		$expArray = explode(' ', $exp);
		$tagName = $this->phpelement->getLocalName();
		switch (count($expArray))
		{
			case 1 :
				switch (strtolower($tagName))
				{
					case 'img' :
					case 'input' :
					case 'image' :
						$attribute = 'src';
						break;
					
					default :
						$attribute = 'image';
						break;
				}
				list ($icon, $size) = explode('/', $expArray[0]);
				$layout = MediaHelper::LAYOUT_PLAIN;
				break;
			
			case 2 :
				if ($expArray[1] == 'shadow')
				{
					switch (strtolower($tagName))
					{
						case 'img' :
						case 'input' :
						case 'image' :
							$attribute = 'src';
							break;
						
						default :
							$attribute = 'image';
							break;
					}
					list ($icon, $size) = explode('/', $expArray[0]);
					$layout = MediaHelper::LAYOUT_SHADOW;
				}
				else
				{
					$attribute = $expArray[0];
					list ($icon, $size) = explode('/', $expArray[1]);
					$layout = MediaHelper::LAYOUT_PLAIN;
				}
				break;
			
			default :
				$attribute = $expArray[0];
				if (isset($expArray[1]))
					list ($icon, $size) = explode('/', $expArray[1]);
				$layout = MediaHelper::LAYOUT_SHADOW;
				break;
		}
		
		if (empty($size))
		{
			$size = MediaHelper::NORMAL;
		}
		
		if (empty($icon))
		{
			$icon = 'unknown';
		}
		
		$attr = $this->phpelement->getOrCreateAttributeNode($attribute);
		$attr->setValue(MediaHelper::getIcon($icon, $size, null, $layout));
	}

	/**
	 * @deprecated
	 */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
	}
}
