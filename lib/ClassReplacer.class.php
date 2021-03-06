<?php
/**
 * @todo 
 * class ClassResolver 
 * class ClassLoader
 * class contactcard_ModuleService (sendNotificationToContactCallback, getNotificationParametersCallback, getNotificationParameters) 
 * function generateUrl
 * 
 * 
 */
class compatibility_ClassReplacer
{
	private $classes = array();
	
	/**
	 * @var compatibility_Logger
	 */
	private $verbose = null;
	
	/**
	 * @var string
	 */
	private $logPrefix = 'Fix: ';
	
	public function setClasses($classes)
	{
		$this->classes = $classes;
	}
	
	public function __construct($classes = array(), $verbose = null)
	{
		$this->setClasses($classes);
		$this->verbose = $verbose;
	}
	
	public function convertPHPService($fullpath)
	{
		$this->logPrefix = 'Fix Service: ';
		$this->setClasses(array(
'$this->pp' => '$this->getPersistentProvider()', '$this->tm' => '$this->getTransactionManager()',
'	private static $instance' => '	//private static $instance',
'	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}' =>
'//	public static function getInstance()
//	{
//		if (is_null(self::$instance))
//		{
//			self::$instance = self::getServiceClassInstance(get_class());
//		}
//		return self::$instance;
//	}',
'	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}' =>
'//	public static function getInstance()
//	{
//		if (self::$instance === null)
//		{
//			self::$instance = self::getServiceClassInstance(get_class());
//		}
//		return self::$instance;
//	}'));
		$this->replaceFile($fullpath);
		
		
		$classes = array(
			'addTreeAttributes' => array('t' => 'warn'),
			'generateUrl' => array('t' => 'err'),
		);
		
		$this->setClasses($classes);
		$this->checkFile($fullpath);
	}
	
	public function convertPHPBlock($fullpath)
	{
		$this->logPrefix = 'Fix Block: ';
		$this->setClasses(array(
			'$this->getPage()' => '$this->getContext()'));
		$this->replaceFile($fullpath);
	}
	
	public function convertPHPView($fullpath)
	{
		$this->logPrefix = 'Fix view: ';
		$this->setClasses(array(
			'$this->getJsService()' => 'website_JsService::getInstance()',
			'$this->getStyleService()' => 'website_StyleService::getInstance()'
		));
		$this->replaceFile($fullpath);
		
		
		$classes = array(
			'getJsService' => array('t' => 'err'),
			'getStyleService' => array('t' => 'err'),
		);
		$this->setClasses($classes);
		$this->checkFile($fullpath);
	}
	
	public function convertPHPCommand($fullpath)
	{
		$this->logPrefix = 'Fix Command: ';

		$classes = array(
			'getParent' => array('t' => 'warn', 'msg' => 'use $this directly'),
			'forward' => array('t' => 'warn', 'msg' => 'use executeCommand'),
			'changecmd' => array('t' => 'warn', 'msg' => 'use executeCommand'),
			'systemExec' => array('t' => 'err'),
		);	
		$this->setClasses($classes);
		$this->checkFile($fullpath);
	}
	
	public function convertPHPFile($fullpath)
	{
		//Migrate Controller
		$this->setClasses( array(
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

			'f_web_CSSDeclaration' => 'website_CSSDeclaration',
			'f_web_CSSRule' => 'website_CSSRule',
			'f_web_CSSStylesheet' => 'website_CSSStylesheet',
			'f_web_CSSVariables' => 'website_CSSVariables',
			'f_permission_RoleService' => 'change_RoleService',
			'f_permission_PermissionService' => 'change_PermissionService',
			'f_persistentdocument_PersistentDocumentImpl' => 'f_persistentdocument_PersistentDocument',
			'commands_AbstractChangeCommand' => 'c_ChangescriptCommand',
			'commands_AbstractChangedevCommand' => 'c_ChangescriptCommand',
			'BaseService' => 'change_BaseService',
			'featurepacka_IdsContainer' => 'filter_StaticQuery'
		));	
		$this->logPrefix = 'Rename generic classes: ';
		$this->migrateFile($fullpath);
		
		//String replacements in PHP classes
		$this->setClasses(array(
			'website_WebsiteModuleService::getInstance()->getCurrentWebsite()' => 'website_WebsiteService::getInstance()->getCurrentWebsite()',
			'f_mvc_HTTPRequest::getInstance()' => 'change_Controller::getInstance()->getRequest()',
			'generic_persistentdocument_Documentmodel' => 'generic_persistentdocument_documentmodel',
			'modules_generic/Document' => 'modules_generic/document',
			'LinkHelper::getCurrentUrlComplete(' => 'LinkHelper::getCurrentUrl(',
			
			'WEBEDIT_HOME' => 'PROJECT_HOME',
			'AG_DEVELOPMENT_MODE' => 'DEVELOPMENT_MODE',
			'AG_LOGGING_LEVEL' => 'LOGGING_LEVEL',
			'AG_SUPPORTED_LANGUAGES' => 'SUPPORTED_LANGUAGES',
			'AG_UI_SUPPORTED_LANGUAGES' => 'UI_SUPPORTED_LANGUAGES',
			'AG_DISABLE_BLOCK_CACHE' => 'DISABLE_BLOCK_CACHE',
			'AG_DISABLE_SIMPLECACHE' => 'DISABLE_DATACACHE',
			'DISABLE_SIMPLECACHE' => 'DISABLE_DATACACHE'
		));
		$this->logPrefix = 'Fix Classes: ';
		$this->replaceFile($fullpath);
		
		//Remove K Constant	
		$this->setClasses(array(
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
		));
		$this->logPrefix = 'Fix K: ';
		$this->removeConstants($fullpath);
		
		$renameArray = array(
			'isPublicated' => array('isPublished', false),
			'transFO' => array('trans', false),
			'transBO' => array('trans', false),
			'strip_accents' => array('stripAccents', 'f_util_StringUtils'),
			'is_hexa' => array('isHexa', 'f_util_StringUtils'),
			'strtolower' => array('toLower', 'f_util_StringUtils'),
			'strtoupper' => array('toUpper', 'f_util_StringUtils'),
			'parse_assoc_string' => array('parseAssocString', 'f_util_StringUtils'),
			'htmlToText' => array('htmlToText', 'f_util_StringUtils', 'f_util_HtmlUtils'),
			'parseHtml' => array('renderHtmlFragment', 'f_util_StringUtils', 'f_util_HtmlUtils'),
			'buildWebeditPath' => array('buildProjectPath', 'f_util_FileUtils'),
			'buildCachePath' => array('buildChangeCachePath', 'f_util_FileUtils'),
			'getPublicationstatusLocaleKey' => array('getStatusLocaleKey', 'DocumentHelper'),
			'sessionStarted' => array('getInstance()->registerSessionId', 'Framework', 'change_LoggingService'),
			'getLogLevelName' => array('getInstance()->getLogLevelName', 'Framework', 'change_LoggingService'),
			'getLogPriority' => array('getInstance()->getLogPriority', 'Framework', 'change_LoggingService')
		);		
		$this->logPrefix = 'Fix Function rename: ';
		$this->renameFunction($fullpath, $renameArray);
		
		//Migrate Users
		$this->setClasses(array(
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
		
			'users_FrontendgroupFeederBaseService' => 'users_GroupFeederBaseService'
		));
		$this->logPrefix = 'Fix Users Classes: ';
		$this->migrateFile($fullpath, true);	

		$this->logPrefix = 'Fix I18n convertion: ';
		$this->migratePHPI18n($fullpath);
		
		//Code an class checking
		$this->setClasses(array(
			'f_Locale' => array('t' => 'err', 'msg' => 'use LocaleService::getInstance()->trans()'),
			'f_mvc_HTTPRequest' => array('t' => 'warn'),
			'f_mvc_FakeHttpRequest' => array('t' => 'warn'),
			'f_mvc_Session' => array('t' => 'warn'),
			'f_mvc_HTTPSession' => array('t' => 'warn'),
			'FileLoader' => array('t' => 'warn', 'msg' => 'use change_FileResolver'),
			'ResourceLoader' => array('t' => 'warn', 'msg' => 'use change_FileResolver'),
			'FileResolver' => array('t' => 'warn', 'msg' => 'use change_FileResolver'),
			'ResourceResolver' => array('t' => 'warn', 'msg' => 'use change_FileResolver'),
			'Loader' => array('t' => 'warn', 'msg' => 'use change_FileResolver'),
			'Resolver' => array('t' => 'warn', 'msg' => 'use change_FileResolver'),
			'TemplateLoader' => array('t' => 'warn', 'msg' => 'use change_TemplateLoader'),
			'TemplateResolver' => array('t' => 'warn', 'msg' => 'use change_TemplateLoader'),
			'date_DateFormat' => array('t' => 'err', 'msg' => 'use date_Formatter'),
			'ClassResolver' => array('t' => 'err', 'msg' => 'use change_AutoloadBuilder'),
			'ClassLoader' => array('t' => 'err', 'msg' => 'use change_AutoloadBuilder'),
			'config_ProjectParser' => array('t' => 'err', 'msg' => 'use change_ConfigurationService'),
			'website_WebsiteModuleService' => array('t' => 'err', 'msg' => 'use website_WebsiteService or website_PageService'),
			'contactcard_ModuleService' => array('t' => 'warn'),
			'sendNotificationToContactCallback' => array('b' => 'contactcard_ModuleService', 't' => 'err'),
			'getNotificationParametersCallback' => array('b' => 'contactcard_ModuleService', 't' => 'err'),
			'getNotificationParameters' => array('b' => 'contactcard_ModuleService', 't' => 'err'),
			'getOriginalModuleName' => array('t' => 'err', 'msg' => 'replaced by getModuleName in persistent models'),
			'getOriginalModelName' => array('t' => 'err', 'msg' => 'replaced by getName in persistent models'),
			'publishOnDayChange' => array('t' => 'err', 'msg' => 'replaced by usePublicationDates in persistent models'),
			'isLinkedToRootFolder' => array('t' => 'err', 'msg' => 'does not exist any more in persistent models'),
			'getNearestContainerId' => array('t' => 'err', 'msg' => 'does not exist in website_Page'),
			'cleanOldKey' => array('t' => 'err', 'msg' => 'old key format support removed'),
			'getOrderInfosByShelfAndShop' => array('t' => 'err', 'msg' => 'in catalog_CompiledProductService, this method is replaced by getOrderInfosByShelfAndShopAndLang'),
			'setPositionsByShelfAndShop' => array('t' => 'err', 'msg' => 'in catalog_CompiledProductService, this method is replaced by setPositionsByShelfAndShopAndLang'),
			'buildRepositoryPath' => array('t' => 'err'),
			'AG_WEBAPP_NAME' => array('t' => 'err'),
			'MOD_NOTIFICATION_SENDER_HOST' => array('t' => 'err'),
			'DEFAULT_SENDER_HOST' => array('t' => 'err'),
			'MOD_NOTIFICATION_SENDER' => array('t' => 'err'),
			'NOREPLY_DEFAULT_EMAIL' => array('t' => 'err'),
			'ModuleService' => array(),
			'getModules' => array('b' => 'ModuleService', 't' => 'err', 'msg' => 'use getPackageNames'),
			'getLinkedModules' => array('b' => 'ModuleService', 't' => 'err'),
			'customer_ModuleService' => array(),
			'getUIDateFormat' => array('b' => 'customer_ModuleService', 't' => 'err', 'msg' => 'use default date formats in date_Formatter'),
			'getUIDateTimeFormat' => array('b' => 'customer_ModuleService', 't' => 'err', 'msg' => 'use default date formats in date_Formatter'),
			'customer_CustomerService' => array(),
			'EMAIL_CONFIRMATION_OK' => array('b' => 'customer_CustomerService', 't' => 'err'),
			'EMAIL_CONFIRMATION_NO_CUSTOMER' => array('b' => 'customer_CustomerService', 't' => 'err'),
			'EMAIL_CONFIRMATION_BAD_STATE' => array('b' => 'customer_CustomerService', 't' => 'err'),
			'EMAIL_CONFIRMATION_BAD_EMAIL' => array('b' => 'customer_CustomerService', 't' => 'err'),
			'sendEmailConfirmationEmail' => array('b' => 'customer_CustomerService', 't' => 'err'),
			'validateEmailConfirmation' => array('b' => 'customer_CustomerService', 't' => 'err'),
			'getEmailConfirmationRedirectionUrl' => array('b' => 'customer_CustomerService', 't' => 'err'),
			'LinkHelper' => array(),
			'getCurrentUrlComplete' => array('b' => 'LinkHelper', 't' => 'err', 'msg' => 'use getCurrentUrl'),
		));
		$this->checkFile($fullpath, false);
		
		$content = file_get_contents($fullpath);
		if (strpos($content, '&modules.') || strpos($content, '&framework.'))
		{
			$this->verbose->logError('Old locale key found in: ' . $fullpath);
		}
	}
	
	public function convertXMLFile($fullpath)
	{
		//Migrate Users
		$this->setClasses(array(
			'modules_users/frontendgroup' => 'modules_users/group',
			'modules_users/dynamicfrontendgroup' => 'modules_users/dynamicgroup',
			'modules_users/websitefrontendgroup' => 'modules_users/group',
			'modules_users/backenduser' => 'modules_users/user',
			'modules_users/frontenduser' => 'modules_users/user',
			'modules_users/websitefrontenduser' => 'modules_users/user',
			'modules_generic/Document' => 'modules_generic/document',
		
			'modules_users_frontendgroup' => 'modules_users_group',
			'modules_users_dynamicfrontendgroup' => 'modules_users_dynamicgroup',
			'modules_users_websitefrontendgroup' => 'modules_users_group',
			'modules_users_backenduser' => 'modules_users_user',
			'modules_users_frontenduser' => 'modules_users_user',
			'modules_users_websitefrontenduser' => 'modules_users_user',
			'generic_persistentdocument_Documentmodel' => 'generic_persistentdocument_documentmodel',
			'${transui:' => '${trans:',
			"\r\n" => PHP_EOL,	
			"    " => "\t",));
		
		$this->logPrefix = 'Fix Users models: ';
		$content = file_get_contents($fullpath);
		$content = str_replace(array_keys($this->classes), array_values($this->classes), $content);
		$content = preg_replace_callback('/\$\{trans:[^,}]+/', array($this, 'normalizeI18nKey'), $content);
		
		$content = preg_replace_callback('/["\']&((?:modules|themes|framework)\.[a-zA-Z0-9-_.]+);["\']/', array($this, 'normalizeI18nCDataKey'), $content);

		file_put_contents($fullpath, $content);	
	}
	
	/**
	 * @param string[] $matches
	 * @return string
	 */
	public function normalizeI18nKey($matches)
	{
		return strtolower($matches[0]);
	}
	
	/**
	 * @param string[] $matches
	 * @return string
	 */
	public function normalizeI18nCDataKey($matches)
	{
		$oldKey = $matches[1];
		$p = explode('.', $oldKey);
		$p[0] = $p[0][0];
		$l = end($p);		
		return '"${trans:'. strtolower(implode('.', $p)) . ((strtolower($l[0]) != $l[0]) ? ',ucf,js}"' : ',js}"');
	}
	
	
	public function checkFile($fullpath, $deprecated = true)
	{
		$tokens = token_get_all(file_get_contents($fullpath));
		foreach ($tokens as $tn => $tv)
		{
			if (is_array($tv))
			{
				if ($tv[0] === T_STRING)
				{
					$kw = $tv[1];
					if (!isset($this->classes[$kw])) {continue;}
					$this->classes[$kw]['tk'] = $tn;
					
					$d = $this->classes[$kw];
					if (!isset($d['t'])) {continue;}
					
					if (isset($d['b']))
					{
						if (!isset($this->classes[$d['b']]['tk'])) {continue;}
					}
					
					
					if ($d['t'] === 'err')
					{
						$this->verbose->logError($kw. ' -> Invalid usage in ' . $fullpath);
						if (isset($d['msg']))
						{
							$this->verbose->logInfo('	' . $d['msg']);
						}
					}
					elseif ($d['t'] === 'warn')
					{
						$this->verbose->logWarn($kw. ' -> Suscpicious usage in ' . $fullpath);
						if (isset($d['msg']))
						{
							$this->verbose->logInfo('	' . $d['msg']);
						}
					}
				}
				elseif (!$deprecated && ($tv[0] === T_COMMENT || $tv[0] === T_DOC_COMMENT))
				{
					if (strpos(strtolower($tv[1]), 'deprecated') !== false)
					{
						$deprecated = true;
						$this->verbose->logWarn('Deprecated keyword in : '. $fullpath);
					}
				}
			}
		}
	}

	public function migrateFile($fullpath, $inString = false)
	{
		$tokens = token_get_all(file_get_contents($fullpath));
		$content = $this->replaceClasses($tokens, $this->classes, $inString);
		if ($content !== null)
		{
			if ($this->verbose) {$this->verbose->logInfo($this->logPrefix .  $fullpath);}
			file_put_contents($fullpath, $content);
		}
	}
	
	public function migrateString($string, $inString = false)
	{
		$tokens = token_get_all($string);
		return $this->replaceClasses($tokens, $this->classes, $inString);
	}
	
	public function replaceFile($fullpath)
	{
		$content = file_get_contents($fullpath);
		$newContent = str_replace(array_keys($this->classes), array_values($this->classes), $content);
		if ($newContent !== $content)
		{
			if ($this->verbose) {$this->verbose->logInfo($this->logPrefix . $fullpath);}
			file_put_contents($fullpath, $newContent);
		}
	}	
	
	public function replaceString($string)
	{
		$newContent = str_replace(array_keys($this->classes), array_values($this->classes), $string);
		if ($newContent !== $string)
		{
			return $newContent;
		}
		return null;
	}	
	
	public function renameFunction($filePath, $renameArray)
	{
		$phpContent = file_get_contents($filePath);
		$tokens = token_get_all($phpContent);
		foreach ($tokens as $i => $ta)
		{
			if (is_array($ta) && $ta[0] === T_STRING)
			{
				if (isset($renameArray[$ta[1]]))
				{
					$ri = $renameArray[$ta[1]];
					if ($this->isFunctionCall($i, $tokens, $ri[1]))
					{
						$tokens[$i][1] = $ri[0];
						if ($ri[1] && isset($ri[2]))
						{
							while (true)
							{
								$ta = $tokens[--$i];
								if (is_array($ta) && $ta[1] === $ri[1])
								{
									$tokens[$i][1] = $ri[2];
									break;
								}
							}						
						}
					}
				}
			}
		}
	
		$newphpContent = $this->tokenToString($tokens);
		if ($newphpContent !== $phpContent)
		{
			if ($this->verbose) {$this->verbose->logInfo($this->logPrefix . $filePath);}
			file_put_contents($filePath, $newphpContent);
		}
	}
	
	function migratePHPI18n($fullpath)
	{
		$content = file_get_contents($fullpath);
		$tokens = token_get_all($content);
		$this->migratePHPI18nToken($tokens);
		$newphpContent = $this->tokenToString($tokens);
		if ($content !== $newphpContent)
		{
			if ($this->verbose)
			{
				$this->verbose->logInfo($this->logPrefix . $fullpath);
			}
			file_put_contents($fullpath, $newphpContent);
		}
	}
	
	private function migratePHPI18nToken(&$tokens)
	{
		$fl_tsi = 0;
		$fl_tei = 0;
		$fl_pc = 0;
		$fl_ucf = false;
		foreach ($tokens as $i => $token)
		{
			if (is_array($token))
			{
				switch ($token[0])
				{
					case T_STRING :
						if ($token[1] === 'f_Locale')
						{
							if ($fl_tsi === 0)
							{
								$fl_tsi = $i;
								$fl_ucf = false;
								$fl_tei = $i;
								$fl_pc = -1;
							}
						}
						elseif ($token[1] === 'translate' || $token[1] === 'translateUI')
						{
							if ($fl_tsi !== 0 && $fl_pc === -1)
							{
								$fl_pc = 0;
								for ($ti = $fl_tsi; $ti < $i; $ti++)
								{
									$tokens[$ti] = '';
								}
								$tokens[$i] = 'LocaleService::getInstance()->trans';
							}
						}
						elseif ($fl_tsi !== 0 && $token[1] === 'ucfirst')
						{
							$fl_ucf = true;
							$tokens[$i][1] = 'strtolower';
						}
						break;
					case T_CONSTANT_ENCAPSED_STRING : // "&modules.catalog.bo.dashboard.Shelves-count;"
						if ($fl_tsi !== 0 && $fl_pc == 1)
						{
							$s = $token[1];
							$tab = explode('.', $s);
							if (count($tab) > 1)
							{
								$last = end($tab);
								$fl_ucf = (strtolower($last[0]) !== $last[0]);
							}
							
							if ($s[1] === '&')
							{
								$s = strtolower(str_replace(array('&modules.', '&framework.'), array('m.', 'f.'), $s));
							}
							$sl = strlen($s);
							
							if ($s[$sl - 2] === ';')
							{
								$s = strtolower(substr($s, 0, $sl - 2)) . $s[$sl - 1];
							}
							$tokens[$i][1] = $s;
						}
						elseif (preg_match('/^["\']\&(modules|framework|themes)\.([a-zA-Z0-9-_.]+);["\']$/', $token[1], $match))
						{
							$tokens[$i] = '\'' . $match[1][0] . '.' . strtolower($match[2]) . '\' /* @TODO CHECK */';
						}
						break;
				}
			}
			elseif ($fl_tsi !== 0)
			{
				if ($token === '(')
				{
					$fl_pc++;
				}
				elseif ($token === ')')
				{
					$fl_pc--;
					if ($fl_pc === 0)
					{
						$tokens[$i] = ' /* @TODO CHECK */' . ($fl_ucf ? ', array(\'ucf\'))' : ')');
						$plop = array();
						for ($ti = $fl_tsi; $ti <= $i; $ti++)
						{
							$plop[] = $tokens[$ti];
						}
						$fl_tsi = 0;
					}
				}
				elseif ($token === ',' && $fl_pc === 1)
				{
					$tokens[$i] = ' /* @TODO CHECK */' . ($fl_ucf ? ', array(\'ucf\'),' : ', array(),');
					$plop = array();
					for ($ti = $fl_tsi; $ti <= $i; $ti++)
					{
						$plop[] = $tokens[$ti];
					}
					$fl_tsi = 0;
				}
			}
		}
	}
					
	/**
	 * @example
	 * @param unknown_type $tokens
	 * @param unknown_type $classes
	 * @param unknown_type $inString
	 * @return Ambigous <NULL, string>
	 */
	private function replaceClasses($tokens, $classes, $inString = false)
	{
		$content = array();
		$updated = false;
		$commentCheck = array('* @return String', '* @return Boolean', '* @return Integer', '* @return Double',
				'* @param String', '* @param Boolean', '* @param Integer', '* @param Double', '* @example');
		$commentReplace = array('* @return string', '* @return boolean', '* @return integer', '* @return float',
				'* @param string', '* @param boolean', '* @param integer', '* @param float', '* For exemple');
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
						$str = str_replace(array("\r\n", "    "), array(PHP_EOL, "\t"), $str);
						if ($str !== $tv[1])
						{
							$updated = true;
						}
						$content[] = $str;
						break;
						
					case T_COMMENT :
						$str = str_replace(array("\r\n", "    "), array(PHP_EOL, "\t"), $tv[1]);
						if ($str !== $tv[1])
						{
							$updated = true;
						}
						$content[] = $str;
						break;
						
					case T_WHITESPACE :
						$str = str_replace(array("\r\n", "    "), array(PHP_EOL, "\t"), $tv[1]);
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
	
	private function isTokenClass($tn, $tokens)
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
	
	private function removeConstants($fullpath)
	{
		$tokens = token_get_all(file_get_contents($fullpath));
		try
		{
			$content = $this->replaceConstants($tokens);
			if ($content !== null)
			{
				if ($this->verbose) {$this->verbose->logInfo($this->logPrefix .  $fullpath);}
				file_put_contents($fullpath, $content);
			}
		}
		catch (Exception $e)
		{
			if ($this->verbose) {$this->verbose->logError($this->logPrefix .  $e->getMessage() . ' in ' . $fullpath);}
		}
	}
	
	private function replaceConstants($tokens)
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
							list($next, $constStr) = $this->isStaticTokenClass($tn, $tokens);
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
	
	private function isStaticTokenClass($tn, $tokens)
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
				if (!isset($this->classes[$tv[1]]))
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
					$value = var_export($this->classes[$tv[1]], true);
				}
				if ($this->verbose) {$this->verbose->logInfo("K::". $tv[1]. ' -> '. $value);}
				return array($i, $value);
			}
			break;
		}
		return array(false, null);
	}
	
	private function isFunctionCall($ti, $tokens, $staticCall = false)
	{
		$t = $tokens[$ti];
		if (!is_array($t) || $t[0] !== T_STRING) {return false;}
		$nti = $ti + 1;
		$nt = $tokens[$nti];
		if (is_array($nt) && ($nt[0] === T_WHITESPACE)) {$nt = $tokens[++$nti];}
		if (is_array($nt) || $nt !== '(') {return false;}
		
		$pti = $ti - 1;
		$pt = $tokens[$pti]; 
		$ptt = $staticCall ? T_DOUBLE_COLON : T_OBJECT_OPERATOR;
		if (is_array($pt) && ($pt[0] === T_WHITESPACE)) {$pt = $tokens[--$pti];}
		if (!is_array($pt) || $pt[0] !== $ptt) {return false;}
		if ($staticCall)
		{
			$pt = $tokens[--$pti];
			if (is_array($pt) && ($pt[0] === T_WHITESPACE)) {$pt = $tokens[--$pti];}
			if (!is_array($pt) || $pt[1] !== $staticCall) {return false;}
		}
		return true;
	}
	
	private function tokenToString($tokens)
	{
		$content = array();
		foreach ($tokens as $v)
		{
			$content[] = is_array($v) ? $v[1] : $v;
		}
		return implode('', $content);
	}
	
	private function tokenDump($tokens)
	{
		$content = array();
		foreach ($tokens as $i => $ta)
		{
			if (is_array($ta))
			{
				$content[] = $i .') '. token_name($ta[0]). ' -> '. $ta[1]. PHP_EOL;
			}
			else
			{
				$content[] = $i.') RAW: '. var_export($ta, true). PHP_EOL;
			}
		}
		return implode(PHP_EOL, $content);
	}
}