<?php
/**
 * @deprecated
 */
abstract class f_util_TypeValidator
{
	/**
	 * @deprecated
	 */
	const TYPE_INTEGER      = 1;
	
	/**
	 * @deprecated
	 */
	const TYPE_FLOAT        = 2;
	
	/**
	 * @deprecated
	 */
	const TYPE_NUMERIC      = 3;
	
	/**
	 * @deprecated
	 */
	const TYPE_STRING       = 4;
	
	/**
	 * @deprecated
	 */
	const TYPE_OBJECT       = 5;
	
	/**
	 * @deprecated
	 */
	const TYPE_ARRAY        = 6;
	
	/**
	 * @deprecated
	 */
	const TYPE_ARRAY_OBJECT = 7;
	
	/**
	 * @deprecated
	 */
	const TYPE_DOUBLE       = 8;
	
	/**
	 * @deprecated
	 */
	const TYPE_DOCUMENT     = 9;

	/**
	 * @deprecated
	 */
	public static function check()
	{
		if (func_num_args() & 1)
		{
			throw new Exception('f_util_TypeValidator::check() expects an even number of parameters: $varToCheck, "type of var1", ...');
		}
		for ($i = 0 ; $i<func_num_args() ; $i += 2)
		{
			$var  = func_get_arg($i);
			$type = func_get_arg($i + 1);
			$argIndex = (($i/2)+1);
			if ( ! self::isVariableValid($var, $type, $expectedType) )
			{
				throw new IllegalArgumentException('argument '.$argIndex, $expectedType);
			}
		}
	}
	
	/**
	 * @deprecated
	 */
	public static function checkAll($vars)
	{
		if (!is_array($vars) || count($vars) != func_num_args() - 1)
		{
			throw new IllegalArgumentException('$vars', 'array');
		}
		for ($i = 1 ; $i<func_num_args() ; $i++)
		{
			$type = func_get_arg($i);
			$argIndex = $i - 1;
			if ( ! self::isVariableValid($vars[$i-1], $type, $expectedType) )
			{
				throw new IllegalArgumentException('argument '.$argIndex, $expectedType);
			}
		}
	}


	/**
	 * @deprecated
	 */
	private static function isVariableInstanceOf($var, $className)
	{
		$className = trim($className);

		if (strcasecmp('null', $className) == 0 && is_null($var))
		{
			return true;
		}

		// FIXME intbonjf: class_exists throws an exception or not???
		try
		{
			if ((class_exists($className, false) || interface_exists($className, false)) && $var instanceof $className)
			{
				return true;
			}
		} catch (AutoloadException $e) { }

		return false;
	}

	/**
	 * @deprecated
	 */
	private static function isVariableValid($var, $type, &$expectedType)
	{
		$expectedType = null;
		switch ($type)
		{
			case self::TYPE_INTEGER :
				if ( ! is_int($var) ) { $expectedType = 'integer'; }
				break;

			case self::TYPE_FLOAT :
				if ( ! is_float($var) ) { $expectedType = 'float'; }
				break;

			case self::TYPE_DOUBLE :
				if ( ! is_double($var) ) { $expectedType = 'double'; }
				break;

			case self::TYPE_NUMERIC :
				if ( ! is_numeric($var) ) { $expectedType = 'numeric'; }
				break;

			case self::TYPE_STRING :
				if ( ! is_string($var) ) { $expectedType = 'string'; }
				break;

			case self::TYPE_OBJECT :
				if ( ! is_object($var) ) { $expectedType = 'object'; }
				break;

			case self::TYPE_ARRAY :
				if ( ! is_array($var) ) { $expectedType = 'array'; }
				break;

			case self::TYPE_ARRAY_OBJECT :
				if ( ! $var instanceof ArrayObject  ) { $expectedType = 'ArrayObject'; }
				break;

			case self::TYPE_DOCUMENT :
				if ( ! $var instanceof f_persistentdocument_PersistentDocument ) { $expectedType = 'f_persistentdocument_PersistentDocument'; }
				break;

			default :
				if (strpos($type, '|'))
				{
					$isValid = false;
					foreach(explode('|', $type) as $type)
					{
						if (self::isVariableInstanceOf($var, $type))
						{
							$isValid = true;
							break;
						}
					}
					if (!$isValid)
					{
						$expectedType = 'instance of '.str_replace('|', ' or ', $type);
					}
				}
				else
				{
					if (!self::isVariableInstanceOf($var, $type))
					{
						$expectedType = 'instance of '.$type;
					}
				}
		}
		return is_null($expectedType) ? true : false;
	}
}