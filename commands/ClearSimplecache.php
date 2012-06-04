<?php
/**
 * @deprecated use clear-datacache instead
 */
class commands_ClearSimplecache extends c_ChangescriptCommand
{
	/**
	 * @return String
	 */
	function getUsage()
	{
		return "";
	}
	
	/**
	 * @return String
	 */
	function getDescription()
	{
		return "Deprecated use clear-datacache instead";
	}
	
	function isHidden()
	{
		return true;
	}

	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	function _execute($params, $options)
	{
		$this->message("== Clear simple cache ==");
		$this->loadFramework();
		$this->errorMessage($this->getDescription());
		$this->quitOk(".");
	}
}