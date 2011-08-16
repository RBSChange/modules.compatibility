<?php
/**
 * commands_compatibility_RemoveK
 * @package modules.compatibility.command
 */
class commands_compatibility_RemoveK extends commands_AbstractChangeCommand
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
		return "Remove K Constant";
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
		$this->message("== Remove K Constant ==");
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
			$packages = array();
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
		try 
		{
			$content = $this->replaceClasses($tokens);
			if ($content !== null)
			{
				echo "\n", 'Fixed : ', $fullpath, "\n\n";
				file_put_contents($fullpath, $content);
			}			
		}
		catch (Exception $e)
		{
			echo $e->getMessage() , ' in ', $fullpath, "\n";
			die();
		}
	}
	
	function replaceClasses($tokens)
	{
		$content = array();
		$updated = false;
		$constantClass = 'K';
		$length = count($tokens);
		$tn = 0;
		while ($tn < $length)
		{
			$tv = $tokens[$tn];
			if (is_array($tv))
			{
				switch ($tv[0])
				{
					case T_STRING :
						if ($tv[1] === $constantClass)
						{
							list($next, $constStr) = $this->isTokenClass($tn, $tokens);
							if ($next !== false)
							{
								$content[] = $constStr;
								$tn = $next;
								$updated = true;
								continue;
							}
						}
						$content[] = $tv[1];
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
			$tn++;
		}	
		return ($updated) ? implode('', $content) : null;
	}
	
	function isTokenClass($tn, $tokens)
	{
		$isStatic = false;
		$i = $tn + 1;
		while ($i < count($tokens))
		{
			$tv = $tokens[$i];
			if (!is_array($tv))
			{
				break;
			}
			if ($tv[0] === T_WHITESPACE)
			{
				$i++;
				continue;
			}
			if ($tv[0] === T_DOUBLE_COLON)
			{
				$i++;
				$isStatic = true;
				continue;
			}
			
			if ($isStatic && $tv[0] === T_STRING)
			{
				if (!isset($this->kValues[$tv[1]]))
				{
					throw new Exception('Invalid Const:' . $tv[1]);
				}
				
				if ($tv[1] === 'COMPONENT_ID_ACCESSOR')
				{
					$value = 'change_Request::DOCUMENT_ID';
				}
				elseif ($tv[1] === "CRLF")
				{
					$value = 'PHP_EOL';
				}
				else
				{
					$value = var_export($this->kValues[$tv[1]], true);
				}
				echo "\nK::", $tv[1], ' -> ', $value;
				return array($i, $value);
			}
			break;
		}	
		return array(false, null);
	}
	
	private $kValues = array(
	'WEBEDIT_MODULE_ACCESSOR'=>'wemod',
	'LANG_ACCESSOR'=>'lang',
	'COMPONENT_ACCESSOR'=>'cmp',
	'COMPONENT_ID_ACCESSOR'=>'cmpref',
	'COMPONENT_LANG_ACCESSOR'=>'lang',
	'COMPONENT_STATUS_ACCESSOR'=>'cmpstatus',
	'COMPONENT_REVISION_ACCESSOR'=>'cmprev',
	'COMPONENT_VIEW_ACCESSOR'=>'cmpview',
	'DATA_ACCESSOR'=>'data',
	'DESTINATION_ID_ACCESSOR'=>'destref',
	'PARENT_ID_ACCESSOR'=>'parentref',
	'PAGE_REF_ACCESSOR'=>'pageref',
	'FOLDER_ID_ACCESSOR'=>'folderref',
	'VALUE_ACCESSOR'=>'value',
	'LABEL_ACCESSOR'=>'label',
	'DEFAULT_DISPLAY_ACCESSOR'=>'defaultDisplay',
	'LINKED_COMPONENT_ACCESSOR'=>'lnkcmp',
	'PARSER_ACCESSOR'=>'parser',
	'FULL_TREE_CONTENT_ACCESSOR'=>'fullTreeContent',
	'URL_REWRITE_PAGE_NAME_ACCESSOR'=>'pagename',
	'CHILDREN_ORDER_ACCESSOR'=>'co',
	'PERSPECTIVE_ACCESSOR'=>'perspective',
	'WIDGET_ACCESSOR'=>'widgetref',
	'CLASS_ACCESSOR'=>"class",
    'JOB_NAME_ACCESSOR'=>'job',
    'FORWARD_TO_MODULE'=>'fmodule',
    'FORWARD_TO_ACTION'=>'faction',
	'PARENT_MODULE_ACCESSOR'=>'parentmodulename',

	// other constantes
	'LISTBOX_VALUES_SEPARATOR'=>',',
	'DATACOMPONENT_GET_RAW_VALUE'=>'raw',
	'DEFAULT_PERSPECTIVE_NAME'=>'default',

	'TREE_OFFSET'=>'treeOffset',
	'TREE_ORDER'=>'order',
	'TREE_FILTER'=>'treeFilter',
	'TREE_ID'=>'treeId',
	'TREE_TYPE'=>'treeType',
	'TREE_GOTO_CMPID'=>'gotocmpid',

	'GENERIC_MODULE_NAME'=>'generic',
	'DB_TABLENAME_PREFIX_FRAMEWORK'=>'f_',
	'DB_TABLENAME_PREFIX_MODULE'=>'m_',

	'DEFAULT_LANG'=>'fr',
	'CRLF'=>"\n",
	'VALUES_SEPARATOR'=>',',

	'XUL'=>'xul',
	'XML'=>'xml',
	'HTML'=>'html',
	'MAIL'=>'mail',

    'ROLE_NAME'=>'roleName',
    'ROLE_SYSTEM_DO'=>'do',
    'ROLE_GROUP_ADN_USER_COMPONENT_ID'=>'groupAndUserComponentIdArray',
    'ROLE_CHECK_CONTEXT'=>'rckcontext',
    
    'EFFECTIVE_MODULE_NAME'=>'BaseAction_effectiveModuleName'
    );
}