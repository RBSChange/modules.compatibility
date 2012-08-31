<?php
/**
 * commands_compatibility_UpdateOverride
 * @package modules.compatibility
 */
class commands_compatibility_UpdateOverride extends c_ChangescriptCommand
{
	/**
	 * @return String
	 */
	public function getUsage()
	{
		return "<moduleName>";
	}

	/**
	 * @return String
	 */
	public function getDescription()
	{
		return "Copies the deprecated files into the override folder.";
	}

	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 */
	protected function validateArgs($params, $options)
	{
		return count($params) == 1;
	}
	
	/**
	 * @param Integer $completeParamCount the parameters that are already complete in the command line
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @return String[] or null
	 */
	public function getParameters($completeParamCount, $params, $options, $current)
	{
		if ($completeParamCount == 0)
		{
			$components = array();
			foreach (glob("modules/compatobility/override/*", GLOB_ONLYDIR) as $module)
			{
				$components[] = basename($module);
			}
			return $components;
		}
	}
		
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== Copy templates ==");
		$this->loadFramework();
		
		$module = $params[0];		
		$from = f_util_FileUtils::buildWebeditPath('modules', 'compatibility', 'override', $module);
		$dest = f_util_FileUtils::buildOverridePath('modules', $module);
		$this->cpDir($from, $dest);
			
		$this->quitOk("All templates are copied successfully.");
	}
	
	/**
	 * Recursively copy a directory.
	 * @param String $from
	 * @param String $dest
	 */
	private function cpDir($from, $dest)
	{
		f_util_FileUtils::mkdir($dest);
		$fromLength = strlen($from);
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, RecursiveDirectoryIterator::KEY_AS_PATHNAME), RecursiveIteratorIterator::SELF_FIRST) as $file => $info)
		{
			$relFile = substr($file, $fromLength+1);
			$destFile = $dest."/".$relFile;
			if ($info->isDir())
			{
				if (is_dir($destFile))
				{
					continue;
				}
				else if (!mkdir($destFile))
				{
					throw new Exception("Could not make $destFile dir");
				}
			}
			else if (file_exists($destFile))
			{
				$this->log('File "'.$destFile.'" already exists.');
				continue;
			}
			else
			{
				if (!copy($file, $destFile))
				{
					throw new Exception("Could not copy $file to $destFile");
				}
				else
				{
					$this->log('Add file "'.$destFile.'".');
				}
			}
		}
	}
}