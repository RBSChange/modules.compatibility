<?php
/**
 * @deprecated
 */
abstract class commands_AbstractChangeCommand extends c_ChangescriptCommand
{
	
}

/**
 * @deprecated
 */
abstract class commands_AbstractChangedevCommand extends c_ChangescriptCommand
{
	/**
	 * @deprecated use executeCommand
	 */
	protected function changecmd($cmdName, $params = array())
	{
		$this->executeCommand($cmdName, $params);
	}
}