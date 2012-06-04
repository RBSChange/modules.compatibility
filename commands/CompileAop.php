<?php
/**
 * @deprecated use compile-injection instead
 */
class commands_CompileAop extends c_ChangescriptCommand
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
		return "Deprecated. configure and use compile-injection.";
	}
	
	/**
	 * @see c_ChangescriptCommand::getEvents()
	 */
	public function getEvents()
	{
		/*
		return array(
			array('target' => 'compile-autoload'),
			array('target' => 'compile-config'),
		);
		*/
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
		$this->message("== Compile AOP ==");
		$this->loadFramework();
		$this->errorMessage($this->getDescription());	
		$this->quitOk(".");
	}
}