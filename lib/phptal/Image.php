<?php
/**
 * @deprecated use change:img
 */
class PHPTAL_Php_Attribute_CHANGE_Image extends PHPTAL_Php_Attribute
{
	/**
	 * Called before element printing.
	 */
	public function before(PHPTAL_Php_CodeWriter $codewriter)
	{
		Framework::error('Deprecated change:image TAL Attribute ' . TemplateObject::$lastTemplateFileName);
		$exp = trim($this->expression);
		$p = strpos($exp, ' ');
		$contentType = null;
		$tagName = $this->phpelement->getLocalName();
		switch (strtolower($tagName))
		{
			case 'img':
			case 'input':
				$contentType = 'html';
				break;

			default:
				$contentType = 'xul';
				break;
		}

		if ($p !== false)
		{
			$attribute = substr($exp, 0, $p);
			$properties = explode(' ', trim(substr($exp, $p+1)));
		}
		else
		{
			$attribute = 'src';
			$properties = explode(' ', $exp);
		}

		if (is_numeric($properties[0]))
		{
			$args = array(intval(trim($properties[0])));

			for ($i = 1; $i < count($properties); $i++)
			{
				if (isset($properties[$i]))
				{
					$args[] = trim($properties[$i]);

					if (strtoupper(trim($properties[$i])) == 'xml')
					{
						$contentType = 'xul';
					}
				}
			}

			$args[] = $contentType;
			$attr = $this->phpelement->getOrCreateAttributeNode($attribute);
			$attr->setValue(f_util_ClassUtils::callMethodArgs('MediaHelper', 'getUrl', $args));
		}
		else
		{

			$codewriter->pushCode('$__prop__changeimg__ = str_replace("{lang}", RequestContext::getInstance()->getLang(),' . "'$properties[0]'" . ');');
			$properties[0] = str_replace('{lang}', RequestContext::getInstance()->getLang(), $properties[0]);
			if (f_util_StringUtils::beginsWith($properties[0], 'front/'))
			{
				for ($i = 1; $i < count($properties); $i++)
				{
					if (isset($properties[$i]) && (strtoupper(trim($properties[$i])) == 'xml'))
					{
						$contentType = 'xul';
						break;
					}
				}
				
				if (!is_null($contentType) && is_string($contentType))
				{
					$argContentType = "'$contentType'";
				}
				else
				{
					$argContentType = 'null';
				}

				$attr = $this->phpelement->getOrCreateAttributeNode($attribute);
				$attr->setValueEscaped('<?php echo MediaHelper::getFrontofficeStaticUrl( substr($__prop__changeimg__  , 6), ' . $argContentType . '); ?>');
			}
			else if (f_util_StringUtils::beginsWith($properties[0], '/front/'))
			{
				for ($i = 1; $i < count($properties); $i++)
				{
					if (isset($properties[$i]) && (strtoupper(trim($properties[$i])) == 'xml'))
					{
						$contentType = 'xul';
						break;
					}
				}

				if (!is_null($contentType) && is_string($contentType))
				{
					$argContentType = "'$contentType'";
				}
				else
				{
					$argContentType = 'null';
				}
				$attr = $this->phpelement->getOrCreateAttributeNode($attribute);
				$attr->setValueEscaped('<?php echo MediaHelper::getFrontofficeStaticUrl( substr($__prop__changeimg__  , 7), ' . $argContentType . '); ?>');
			}
			else
			{
				for ($i = 1; $i < count($properties); $i++)
				{
					if (isset($properties[$i]) && (strtoupper(trim($properties[$i])) == 'xml'))
					{
						$contentType = 'xul';
						break;
					}
				}
				$attr = $this->phpelement->getOrCreateAttributeNode($attribute);
				$attr->setValue(MediaHelper::getBackofficeStaticUrl($properties[0], $contentType));
			}
		}
	}

	/**
     * Called after element printing.
     */
    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
	}
}
