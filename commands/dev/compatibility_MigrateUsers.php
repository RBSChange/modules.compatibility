<?php
/**
 * commands_compatibility_MigrateUsers
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateUsers extends c_ChangescriptCommand
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
		$parameters = array();
		foreach ($this->getBootStrap()->getProjectDependencies() as $cPackage) 
		{
			/* @var $cPackage c_Package */
			if ($cPackage->isFramework() || $cPackage->isModule())
			{
				$name = $cPackage->getName();
				if ($name != 'compatibility')
				{
					$parameters[] = $name;
				}
			}
		}
		
		return $parameters;
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
		$this->message("== Migrate Users ==");
		$this->loadFramework();
		
		$this->classReplacer = new compatibility_ClassReplacer(array(
			'users_FrontendgroupService' => 'users_GroupService',
			'users_WebsitefrontendgroupService' => 'users_GroupService',
			'users_DynamicfrontendgroupService' => 'users_DynamicgroupService',
			'users_BackenduserService' => 'users_UserService',
			'users_FrontenduserService' => 'users_UserService',
			'users_WebsitefrontenduserService' => 'users_UserService',
		
			'users_persistentdocument_backenduser' => 'users_persistentdocument_user',
			'users_persistentdocument_frontenduser' => 'users_persistentdocument_user',
			'users_persistentdocument_websitefrontenduser' => 'users_persistentdocument_user',
	
		
			'users_persistentdocument_dynamicfrontendgroup' => 'users_persistentdocument_dynamicgroup',	
			'users_persistentdocument_frontendgroup' => 'users_persistentdocument_group',
			'users_persistentdocument_websitefrontendgroup' => 'users_persistentdocument_group',
		
			'users_FrontendgroupFeederBaseService' => 'users_GroupFeederBaseService',
		), true);
		if (f_util_ArrayUtils::isEmpty($params))
		{
			$packages = $this->getBootStrap()->getProjectDependencies();
		}
		else
		{
			$packages = array();
			foreach ($params as $moduleName) 
			{
				$package = $this->getPackageByName($moduleName);
				$packages[$package->getKey()] = $package;
			}
		}
		
		foreach ($packages  as $cPackage) 
		{
			/* @var $cPackage c_Package */
			if ($cPackage->isFramework())
			{
				$paths = $this->scanDir($cPackage->getPath());
				foreach ($paths as $fullpath)
				{
					$this->classReplacer->migrateFile($fullpath, true);
				}
			}
			elseif ($cPackage->isModule() && $cPackage->getName() != 'compatibility')
			{
				$paths = $this->scanModule($cPackage->getName());
				foreach ($paths as $fullpath)
				{
					$this->classReplacer->migrateFile($fullpath, true);
				}
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