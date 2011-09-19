<?php
/**
 * commands_compatibility_MigrateController
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateController extends c_ChangescriptCommand
{
	/**
	 * @return String
	 */
	public function getUsage()
	{
		return "[framework module1 module2 ... moduleN]";
	}
	
	/**
	 * @return String
	 * @example "initialize a document"
	 */
	public function getDescription()
	{
		return "Migrate Controller";
	}
	
	/**
	 * This method is used to handle auto-completion for this command.
	 * @param Integer $completeParamCount the parameters that are already complete in the command line
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @return String[] or null
	 */
	public function getParameters($completeParamCount, $params, $options, $current)
	{
		$components = array('framework');
		
		foreach (glob(PROJECT_HOME. "/modules/*/templates", GLOB_ONLYDIR) as $path)
		{
			$module = dirname($path);
			$components[] = basename($module);
		}	
		
		return $components;
	}
			
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== Migrate Controller ==");
		$this->loadFramework();
		$migrateFramework = false;
		$allPackages = ModuleService::getInstance()->getPackageNames();
		
		if ( f_util_ArrayUtils::isEmpty($params))
		{
			$packages = $allPackages;
		}
		else
		{
			foreach ($params as $moduleName) 
			{
				if (in_array('modules_' .$moduleName, $allPackages))
				{
					$packages[] = 'modules_' .$moduleName;
				}
				elseif (in_array($moduleName, $allPackages))
				{
					$packages[] = $moduleName;
				}
				elseif ($moduleName === 'framework')
				{
					$migrateFramework = true;
				}
			}
		}
		if ($migrateFramework)
		{
			$paths = $this->scanDir(f_util_FileUtils::buildFrameworkPath());
			foreach ($paths as $fullpath)
			{
				$this->migrateFile($fullpath);
			}
		}
		
		
		foreach ($packages as $packageName)
		{
			list (, $moduleName) = explode('_', $packageName);
			$paths = $this->scanModule($moduleName);
			$this->errors = array();
			foreach ($paths as $fullpath)
			{
				$this->migrateFile($fullpath);
			}
		}

		$this->quitOk("Command successfully executed");
	}
	
	
	private function scanModule($moduleName)
	{
		$baseTemplatePath = f_util_FileUtils::buildModulesPath($moduleName);
		$paths = $this->scanDir($baseTemplatePath);
		
		$baseTemplatePath = f_util_FileUtils::buildOverridePath('modules', $moduleName);
		$paths = array_merge($paths, $this->scanDir($baseTemplatePath));
				
		return $paths;
	}
	
	
	private function scanDir($basePath)
	{
		$paths = array();
		if (! is_dir($basePath) || ! is_readable($basePath))
		{
			return $paths;
		}
		
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));
		foreach ($objects as $name => $splFileInfo)
		{
			/* @var $splFileInfo SplFileInfo */
			if (! $splFileInfo->isDir())
			{
				$ext = end(explode('.', $splFileInfo->getBasename()));
				if ($ext === 'php')
				{
					$paths[] = $splFileInfo->getPathname();
				}
			}
		}
		return $paths;
	}
	
	function migrateFile($fullpath)
	{
		$tokens = token_get_all(file_get_contents($fullpath));
		$content = $this->replaceClasses($tokens, $this->classes, false);
		if ($content !== null)
		{
			echo 'Fix : ', $fullpath, "\n";
			file_put_contents($fullpath, $content);
		}
	}
	
	private $classes = array(
		'controller_ChangeController' => 'change_Controller',
		'controller_XulController' => 'change_XulController',
		'Controller' => 'change_Controller',
		'HttpController' => 'change_Controller',
		'WebController' => 'change_Controller',
		'Context' => 'change_Context',
		'Storage' => 'change_Storage',
		'SessionStorage' => 'change_Storage',
		'ChangeSessionStorage' => 'change_Storage',
		'User' => 'change_User',
		'FrameworkSecurityUser' => 'change_User',
		'SecurityUser' => 'change_User',
		'Action' => 'change_Action',
		'f_action_BaseAction' => 'change_Action',
		'f_action_BaseJSONAction' => 'change_JSONAction',	
		'Request' => 'change_Request',
		'WebRequest' => 'change_Request',
		'ChangeRequest' => 'change_Request',
		'View' => 'change_View',
		'f_view_BaseView' => 'change_View',
		'WebRequest' => 'change_Request',
	);
	
	function replaceClasses($tokens, $classes, $inString = false)
	{
		$content = array();
		$updated = false;
		$commentCheck = array();
		$commentReplace = array();
		$stringSearch = array();
		$stringReplace = array();
			
		foreach ($classes as $old => $new) 
		{
			$commentCheck[] = '* @param '.$old.' ';
			$commentCheck[] = '* @return '.$old;
			$commentCheck[] = '* @var '.$old;
			
			$commentReplace[] =  '* @param '.$new.' ';
			$commentReplace[] =  '* @return '.$new;	
			$commentReplace[] =  '* @var '.$new;
			if ($inString)
			{
				$stringSearch[] = '"'.$old.'"';
				$stringSearch[] = '\''.$old.'\'';			
				$stringReplace[] = '\''.$new.'\'';
				$stringReplace[] = '\''.$new.'\'';
			}
		}
		
		foreach ($tokens as $tn => $tv)
		{
			if (is_array($tv))
			{
				switch ($tv[0])
				{
					case T_STRING :
						if (isset($classes[$tv[1]]))
						{
							if ($this->isTokenClass($tn, $tokens))
							{
								$content[] = $classes[$tv[1]];
								$updated = true;
								continue;
							}
						}
						$content[] = $tv[1];
						break;
					case T_CONSTANT_ENCAPSED_STRING :
						if ($inString)
						{
							$str = str_replace($stringSearch, $stringReplace, $tv[1]);
							if ($str !== $tv[1])
							{
								$updated = true;
							}
							$content[] = $str;
						}
						else
						{
							$content[] = $tv[1];
						}
						break;
					case T_DOC_COMMENT :
						$str = str_replace($commentCheck, $commentReplace, $tv[1]);
						if ($str !== $tv[1])
						{
							$updated = true;
						}
						$content[] = $str;
						
						break;
					default :
						$content[] = $tv[1];
						break;
				}
			}
			else
			{
				$content[] = $tv;
			}
		}	
		return ($updated) ? implode('', $content) : null;
	}
	
	function isTokenClass($tn, $tokens)
	{
		$i = $tn + 1;
		while ($i < count($tokens))
		{
			$tv = $tokens[$i];
			if (! is_array($tv))
			{
				break;
			}
			if ($tv[0] === T_WHITESPACE)
			{
				$i ++;
				continue;
			}
			if ($tv[0] === T_DOUBLE_COLON || $tv[0] === T_VARIABLE)
			{
				return true;
			}
			break;
		}
		
		$i = $tn - 1;
		$virg = false;
		while ($i >= 0)
		{
			$tv = $tokens[$i];
			if (! is_array($tv))
			{
				if ($tv === ',')
				{
					$virg = true;
					$i --;
					continue;
				}
				return false;
			}
			switch ($tv[0])
			{
				case T_CLASS :
				case T_INSTANCEOF :
				case T_EXTENDS :
				case T_NEW :
				case T_IMPLEMENTS :
					return true;
				
				case T_STRING :
					if (! $virg)
					{
						return false;
					}
				case T_WHITESPACE :
					$i --;
					break;
				default :
					return false;
			}
		}
	}
}