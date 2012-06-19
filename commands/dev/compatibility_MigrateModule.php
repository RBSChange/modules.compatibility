<?php
/**
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateModule extends c_ChangescriptCommand
{
	
	/**
	 * @return string
	 */
	function getUsage()
	{
		return "package1";
	}
	
	/**
	 * @return string
	 */
	function getDescription()
	{
		return "migrate a module from 3.6.x release";
	}
	
	
	/**
	 * @param integer $completeParamCount the parameters that are already complete in the command line
	 * @param string[] $params
	 * @param array<string, string> $options where the option array key is the option name, the potential option value or true
	 * @return string[] or null
	 */
	function getParameters($completeParamCount, $params, $options, $current)
	{
		$package = array();
		if (count($params) == 0)
		{
			$directory = PROJECT_HOME. '/oldmodules';
			if (is_dir($directory))
			{
				$iterator = new DirectoryIterator($directory);
				foreach ($iterator as $fileinfo) 
				{
					/* @var $fileinfo SplFileInfo */
					if ($fileinfo->isDir()) 
					{
						$name = $fileinfo->getBasename();
						if ($name[0] != '.') {$package[] = $name;}
					}
				}
			}
			
			if ($this->inReleaseDevelopement())
			{
				$directory = PROJECT_HOME. '/modules';
				if (is_dir($directory))
				{
					$iterator = new DirectoryIterator($directory);
					foreach ($iterator as $fileinfo)
					{
						/* @var $fileinfo SplFileInfo */
						if ($fileinfo->isDir())
						{
							$name = $fileinfo->getBasename();
							if ($name[0] != '.' && $name !== 'compatibility') {$package[] = $name;}
						}
					}
				}
			}
			
		}
		return array_diff($package, $params);
	}
	
	/**
	 * @param string[] $params
	 * @param array<string, string>
	 * @return boolean
	 */
	protected function validateArgs($params, $options)
	{
		if (count($params) == 1)
		{
			$directory = PROJECT_HOME. '/oldmodules/' . $params[0];
			if (is_dir($directory))
			{
				if (file_exists($directory.'/change.xml') || file_exists($directory.'/install.xml'))
				{
					return true;
				}
			}
			
			if ($this->inReleaseDevelopement())
			{
				$directory = PROJECT_HOME. '/modules/' . $params[0];
				if (is_dir($directory))
				{
					if (file_exists($directory.'/change.xml') || file_exists($directory.'/install.xml'))
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * @param string[] $params
	 * @param array<string, string> $options where the option array key is the option name, the potential option value or true
	 * @return string
	 */
	function _execute($params, $options)
	{
		$moduleName = $params[0];
		$this->message("== Migrate Module " .$params[0]. " ==");
		$this->loadFramework();
		$directory = PROJECT_HOME. '/oldmodules/' . $moduleName;
		if (!is_dir($directory)) 
		{
			$directory = PROJECT_HOME. '/modules/' . $moduleName;
		}
		
		$converter = new compatibility_ModuleConverter($moduleName, $directory, $this);	
		$converter->convert();
		
		if ($this->hasError())
		{
			return $this->quitError('Command executed with ' . $this->getErrorCount() . ' error(s)');
		}
		return $this->quitOk("Command successfully executed");
	}
	
	public function logInfo($message)
	{
		$this->message($message);
	}
	
	public function logWarn($message)
	{
		$this->warnMessage($message);	
	}
	
	public function logError($message)
	{
		$this->errorMessage($message);
	}
}
