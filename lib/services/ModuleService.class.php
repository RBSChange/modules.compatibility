<?php
/**
 * @package modules.compatibility.lib.services
 */
class compatibility_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var compatibility_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return compatibility_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
}