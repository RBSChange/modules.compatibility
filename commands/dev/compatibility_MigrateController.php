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
	 * @var compatibility_ClassReplacer
	 */
	private $classReplacer;
			
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== Migrate Controller ==");
		$this->loadFramework();
		
		$this->classReplacer = new compatibility_ClassReplacer(array(
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
		), true);
		
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
				$this->classReplacer->migrateFile($fullpath);
			}
		}
		
		
		foreach ($packages as $packageName)
		{
			list (, $moduleName) = explode('_', $packageName);
			$paths = $this->scanModule($moduleName);
			$this->errors = array();
			foreach ($paths as $fullpath)
			{
				$this->classReplacer->migrateFile($fullpath);
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
}