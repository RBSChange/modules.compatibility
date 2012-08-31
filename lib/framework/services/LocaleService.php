<?php
/**
 * Should inject LocaleService.
 * 
 * @deprecated
 */
class compatibility_LocaleService extends LocaleService
{
	/**
	 * @deprecated use trans
	 */
	public function transFO($cleanKey, $formatters = array(), $replacements = array())
	{
		return $this->formatKey(RequestContext::getInstance()->getLang(), $cleanKey, $formatters, $replacements);
	}
	
	/**
	 * @deprecated use trans
	 */
	public function transBO($cleanKey, $formatters = array(), $replacements = array())
	{
		return $this->formatKey(RequestContext::getInstance()->getUILang(), $cleanKey, $formatters, $replacements);
	}
	
	/**
	 * @deprecated
	 */
	public function cleanOldKey($oldKey)
	{
		$l = strlen($oldKey);
		if ($l > 2 && $oldKey[0] === '&' & $oldKey[$l-1] === ';')
		{
			return str_replace(array('&modules.', '&framework.', '&themes.', ';', '&'), array('m.', 'f.', 't.', '', ''), $oldKey);
		}
		return false;
	}
	
	/**
	 * @deprecated
	 */
	public function getFormattersByCleanOldKey(&$cleanOldKey)
	{
		$formatters = array();
	
		if ($cleanOldKey === false) return $formatters;
		$keyParts = explode('.', $cleanOldKey);
		if (count($keyParts) < 3 || !in_array($keyParts[0], array('m', 'f', 't'))) return $formatters;
	
		$keyId = $keyParts[count($keyParts) - 1];
		if (preg_match('/^[A-Z][a-z-]+/', $keyId))
		{
			$formatters[] = 'ucf';
		}
		elseif (preg_match('/^[A-Z][A-Z]+/', $keyId))
		{
			$formatters[] = 'uc';
		}
	
		if (preg_match('/[a-z0-9]+label$/i', $keyId))
		{
			$formatters[] = 'lab';
			$keyId = substr($keyId, 0, strlen($keyId) - 5);
	
			if (preg_match('/[a-z0-9]+mandatory$/i', $keyId))
			{
				//Ignored
				$keyId = substr($keyId, 0, strlen($keyId) - 9);
			}
		}
		elseif (preg_match('/[a-z0-9]+mandatory$/i', $keyId))
		{
			//Ignored
			$keyId = substr($keyId, 0, strlen($keyId) - 9);
		}
		elseif (preg_match('/[a-z0-9]+spaced$/i', $keyId))
		{
			$formatters[] = 'space';
			$keyId = substr($keyId, 0, strlen($keyId) - 6);
		}
		elseif (preg_match('/[a-z0-9]+ellipsis$/i', $keyId))
		{
			$formatters[] = 'etc';
			$keyId = substr($keyId, 0, strlen($keyId) - 8);
		}
		$keyParts[count($keyParts) - 1]	= $keyId;
		$cleanOldKey = strtolower(implode('.', $keyParts));
	
		return $formatters;
	}
}