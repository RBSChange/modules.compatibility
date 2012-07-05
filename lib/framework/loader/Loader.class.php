<?php
/**
 * @deprecated
 */
abstract class Loader
{
		
	/**
	 * @deprecated
	 */
	public static function getInstance( $type )
	{
		$className = ucfirst( strtolower( $type ) ) . "Loader";
		$method = new ReflectionMethod($className, 'getInstance');
		return $method->invoke(null);
	}
}

