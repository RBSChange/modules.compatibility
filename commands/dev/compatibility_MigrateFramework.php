<?php
/**
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateFramework extends c_ChangescriptCommand
{
	/**
	 * @return string
	 */
	public function getUsage()
	{
		return '';
	}
	
	/**
	 * @return string
	 */
	public function getDescription()
	{
		return 'Migrate framework code.';
	}
	
	/**
	 * @param string[] $params
	 * @param array<string, string>
	 * @return boolean
	 */
	protected function validateArgs($params, $options)
	{
		return count($params) === 0;
	}

	/**
	 * @param string[] $params
	 * @param array<string, string> $options where the option array key is the option name, the potential option value or true
	 * @return string
	 */
	public function _execute($params, $options)
	{
		$this->message("== Migrate Framework ==");
		$this->loadFramework();
		$directory = PROJECT_HOME. '/framework';
		
		$converter = new compatibility_FrameworkConverter('framework', $directory, $this);	
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