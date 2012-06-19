<?php
/**
 * @deprecated
 */
abstract class compatibility_InjectedStringUtils extends f_util_StringUtils
{
	/**
	 * @deprecated
	 */
	public static function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val) - 1});
		switch ($last)
		{
			// Le modifieur 'G' est disponible depuis PHP 5.1.0
			case 'g' :
				$val *= 1024;
			case 'm' :
				$val *= 1024;
			case 'k' :
				$val *= 1024;
		}
		
		return $val;
	}
	
	/**
	 * @deprecated
	 */
	public static function array_to_string($array)
	{
		$string = "";
		foreach ($array as $key => $value)
		{
			if (trim($key))
			{
				$string .= sprintf("%s: %s; ", trim($key), trim($value));
			}
		}
		return trim($string);
	}
	
	/**
	 * @deprecated
	 */
	public static function jsquote($string)
	{
		return self::stripnl(self::quoteDouble($string));
	}
	
	/**
	 * @deprecated
	 */
	public static function stripnl($string)
	{
		return trim(preg_replace('/\s+/', ' ', preg_replace('/[\r\n]/', ' ', $string)));
	}
	
	/**
	 * @deprecated
	 */
	public static function php_to_js($mixed, $convertArrayAsObject = false)
	{
		if (is_numeric($mixed))
		{
			$js = strval($mixed);
		}
		else if (is_string($mixed))
		{
			$js = sprintf('"%s"', self::stripnl(self::quoteDouble($mixed)));
		}
		else if (is_array($mixed))
		{
			$js = array();
			
			if ($convertArrayAsObject)
			{
				foreach ($mixed as $key => $value)
				{
					if (!is_numeric($key))
					{
						$js[] = sprintf('%s: %s', $key, self::php_to_js($value));
					}
				}
				
				$js = '{' . implode(', ', $js) . '}';
			}
			else
			{
				$mixed = array_values($mixed);
				
				foreach ($mixed as $value)
				{
					$js[] = self::php_to_js($value);
				}
				
				$js = '[' . implode(', ', $js) . ']';
			}
		}
		else if (is_object($mixed))
		{
			// @fixme not implemented :
			$js = 'undefined';
		}
		else if (is_null($mixed))
		{
			$js = 'null';
		}
		else if ($mixed)
		{
			$js = 'true';
		}
		else
		{
			$js = 'false';
		}
		
		return $js;
	}
	
	/**
	 * @deprecated
	 */
	public static function mergeAttributes($current, $new)
	{
		$current = explode(";", $current);
		$new = explode(";", $new);
		$mergeArray = array();
		foreach ($new as $newDeclaration)
		{
			if ($newDeclaration)
			{
				list ($attribute, $value) = explode(':', $newDeclaration);
				$attribute = trim($attribute);
				$value = trim($value);
				$mergeArray[$attribute] = $value;
			}
		}
		foreach ($current as $currentDeclaration)
		{
			if ($currentDeclaration)
			{
				list ($attribute, $value) = explode(':', $currentDeclaration);
				$attribute = trim($attribute);
				$value = trim($value);
				if (!isset($mergeArray[$attribute]))
				{
					$mergeArray[$attribute] = $value;
				}
			}
		}
		$merge = '';
		foreach ($mergeArray as $attribute => $value)
		{
			if ($attribute && $value)
			{
				$merge .= sprintf('%s: %s; ', $attribute, $value);
			}
		}
		return trim($merge);
	}
	
	/**
	 * @deprecated
	 */
	public static function cleanString($string)
	{
		$string = self::htmlToText($string, false, true);
		$string = self::strip_accents($string);
		$string = preg_replace(array('/[^a-z0-9]/i', '/\s[a-z0-9]{1,2}\s/i'), array(' ', ' '), $string);
		$string = str_replace('  ', ' ', $string);
		return trim(strtolower($string));
	}
	
	/**
	 * @deprecated
	 */
	public static function ordString($string)
	{
		$int = "";
		for ($i = 0; $i < strlen($string); $i++)
		{
			$int .= strval(ord($string[$i]));
		}
		return $int;
	}
	
	/**
	 * @deprecated
	 */
	public static function prefixReplace($old_prefix, $new_prefix, $string)
	{
		// if we found the prefix into the string, we replace, else we don't do
		// anything.
		$len = mb_strlen($old_prefix);
		if (strncmp($string, $old_prefix, $len) == 0)
		{
			$string = $new_prefix . mb_substr($string, $len);
		}
		return ($string);
	}
	
	/**
	 * @deprecated
	 */
	public static function containsCharBetween($string, $first, $last)
	{
		$found = false;
		for ($i = 0; $i < self::strlen($string) && !$found; $i++)
		{
			$char = self::substr($string, $i, 1);
			if ($char >= $first && $char <= $last)
			{
				$found = true;
			}
		}
		return $found;
	}
	
	/**
	 * @deprecated
	 */
	public static function parseTextContent($content, $substData = array())
	{
		if (!empty($substData))
		{
			$substitueFrom = array();
			$substitueTo = array();
			foreach ($substData as $name => $value)
			{
				$substitueFrom[] = '{' . $name . '}';
				$substitueTo[] = $value;
			}
			$content = str_replace($substitueFrom, $substitueTo, $content);
		}
		$content = preg_replace('/\{[a-z0-9_-]+\}/i', '', $content);
		
		return trim($content);
	}
	
	/**
	 * @deprecated
	 */	
	private static $from_accents = null; 
	
	/**
	 * @deprecated
	 */	
	private static $to_accents = null;
	
	/**
	 * @deprecated
	 */	
	private static $lower = null; 
	
	
	/**
	 * @deprecated
	 */	
	private static $upper = null;
	
	/**
	 * @deprecated
	 */
	public static function handleAccent($string, $action)
	{
		switch ($action)
		{
			case self::STRIP_ACCENTS :
				if (is_null(self::$from_accents))
				{
					self::$from_accents = array('à', 'â', 'ä', 'á', 'ã', 'å', 'À', 'Â', 'Ä', 'Á', 'Ã', 'Å', 'æ', 'Æ', 'ç', 'Ç', 'è', 'ê', 'ë', 'é', 
						'È', 'Ê', 'Ë', 'É', 'ð', 'Ð', 'ì', 'î', 'ï', 'í', 'Ì', 'Î', 'Ï', 'Í', 'ñ', 'Ñ', 'ò', 'ô', 'ö', 'ó', 'õ', 'ø', 'Ò', 'Ô', 'Ö', 
						'Ó', 'Õ', 'Ø', 'œ', 'Œ', 'ù', 'û', 'ü', 'ú', 'Ù', 'Û', 'Ü', 'Ú', 'ý', 'ÿ', 'Ý', 'Ÿ');
					self::$to_accents = array('a', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'A', 'ae', 'AE', 'c', 'C', 'e', 'e', 'e', 'e', 
						'E', 'E', 'E', 'E', 'ed', 'ED', 'i', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'n', 'N', 'o', 'o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 
						'O', 'O', 'O', 'oe', 'OE', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'Y');
				}
				return str_replace(self::$from_accents, self::$to_accents, $string);
			case self::TO_LOWER_CASE :
				return mb_strtolower($string, "UTF-8");
			case self::TO_UPPER_CASE :
				return mb_strtoupper($string, "UTF-8");
			default :
				throw new Exception("Unkown handleAccent action $action");
		}
	}
	
	/**
	 * @deprecated
	 */
	public static function utf8Ereg($pattern, $subject, &$matches)
	{
		mb_regex_encoding('utf-8');
		return mb_ereg($pattern, self::utf8Encode($subject), $matches);
	}
	
	/**
	 * @deprecated
	 */
	public static function utf8EregReplace($pattern, $replacement, $subject, $option = null)
	{
		mb_regex_encoding('utf-8');
		return mb_ereg_replace($pattern, self::utf8Encode($replacement), self::utf8Encode($subject), $option);
	}
	
	/**
	 * @deprecated
	 */
	public static function utf8Eregi($pattern, $subject, &$matches)
	{
		mb_regex_encoding('utf-8');
		return mb_eregi($pattern, self::utf8Encode($subject), $matches);
	}
	
	/**
	 * @deprecated
	 */
	public static function is_hexa($in_hexaTest = null)
	{
		return self::isHexa($in_hexaTest);
	}
	
	/**
	 * @deprecated
	 */
	public static function parse_assoc_string($in_assoc_string = '')
	{
		return self::parseAssocString($in_assoc_string);
	}
	
	/**
	 * @deprecated
	 */
	public static function strip_accents($string)
	{
		return self::stripAccents($string);
	}
	
	/**
	 * @deprecated
	 */
	public static function strtolower($string)
	{
		return mb_strtolower($string, "UTF-8");
	}
	
	/**
	 * @deprecated
	 */
	public static function strtoupper($string)
	{
		return mb_strtoupper($string, "UTF-8");
	}
}