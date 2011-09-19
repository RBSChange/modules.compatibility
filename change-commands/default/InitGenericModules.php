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

	function getAlias()
	{
		return "igm";
	}

	/**
	 * @return String
	 */
	function getDescription()
	{
		return "init generic modules";
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
		$this->quitOk("Please use update-dependencies");
	}
}
