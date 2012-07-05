<?php
/**
 * @deprecated
 */
abstract class Resolver
{
		
	/**
	 * @deprecated
	 */
	public static function getInstance( $type )
	{
		$className = ucfirst( strtolower( $type ) ) . "Resolver";
		$method = new ReflectionMethod($className, 'getInstance');
		return $method->invoke(null);
	
	}
	
}

