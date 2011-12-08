<?php
/**
 * commands_compatibility_MigrateFrameworkClasses
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateFrameworkClasses extends c_ChangescriptCommand
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
		return "Migrate framework classes";
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
		$this->message("== Migrate Framework Classes ==");
		
		$this->classReplacer = new compatibility_ClassReplacer(array(
			'f_web_CSSDeclaration' => 'website_CSSDeclaration',
			'f_web_CSSRule' => 'website_CSSRule',
			'f_web_CSSStylesheet' => 'website_CSSStylesheet',
			'f_web_CSSVariables' => 'website_CSSVariables',
			'f_permission_RoleService' => 'change_RoleService',
			'f_permission_PermissionService' => 'change_PermissionService',
			'f_persistentdocument_PersistentDocumentImpl' => 'f_persistentdocument_PersistentDocument'
		), true);
		$this->loadFramework();
		$migrateFramework = false;
		$allPackages = ModuleService::getInstance()->getPackageNames();
		
		if ( f_util_ArrayUtils::isEmpty($params))
		{
			$packages = $allPackages;
			$migrateFramework = true;
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
			if ($moduleName === 'compatibility') {continue;}
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
	