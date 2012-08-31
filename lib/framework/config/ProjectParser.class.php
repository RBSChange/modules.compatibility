<?php
/**
 * @package framework.config
 * Project parser is used to converted project.XX.xml file in php file useable by the framework
 */
class config_ProjectParser
{
	/**
	 * @deprecated
	 */
	public static function isCompiled()
	{
		return change_ConfigurationService::getInstance()->isCompiled();
	}
	
	/**
	 * @depreacted
	 */
	public function evaluateTmpPath()
	{
		if (function_exists('sys_get_temp_dir'))
		{
			$TMP_PATH = sys_get_temp_dir();
		}
		else
		{
			$tmpfile = @tempnam(null, 'loc_');
			if ($tmpfile)
			{
				$TMP_PATH = dirname($tmpfile);
				@unlink($tmpfile);
			}
			else  if (DIRECTORY_SEPARATOR === '\\')
			{
				if (isset($_ENV['TMP']))
				{
					$TMP_PATH = $_ENV['TMP'];
				} 
				else if (isset($_ENV['TEMP']))
				{
					$TMP_PATH = $_ENV['TEMP'];
				}
				else 
				{
					throw new Exception('Please define TMP_PATH in project.xml config file');
				}
			}
			else
			{
				$TMP_PATH ='/tmp';
			}
		}	
		return $TMP_PATH;	
	}
	
	/**
	 * @deprecated use change_ConfigurationService::addProjectConfigurationEntry()
	 */
	public static function addProjectConfigurationEntry($path, $value)
	{
		return change_ConfigurationService::getInstance()->addProjectConfigurationEntry($path, $value);
	}
	
	/**
	 * @deprecated use change_ConfigurationService::addProjectConfigurationNamedEntry()
	 */
	public static function addProjectConfigurationNamedEntry($path, $entryName, $value)
	{
		return change_ConfigurationService::getInstance()->addProjectConfigurationNamedEntry($path, $entryName, $value);
	}
	
	/**
	 * @deprecated use change_ConfigurationService::projectParserExecute()
	 */
	public function execute($computedDeps)
	{
		return change_ConfigurationService::getInstance()->compile($computedDeps);
	}
}