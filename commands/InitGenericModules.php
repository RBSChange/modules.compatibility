<?php
/**
 * @deprecated use update-dependencies instead
 */
class commands_InitGenericModules extends c_ChangescriptCommand
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
		return "Deprecated use use update-dependencies";
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
		$this->message("== Deprecated Init generic modules ==");
		$this->errorMessage($this->getDescription());
		$this->quitOk(".");
	}
}
