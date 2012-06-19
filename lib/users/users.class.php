<?php
Framework::fatal('Load Deprecated class in: ' . __FILE__);

/**
 * @deprecated
 */
class users_FrontendgroupService extends users_GroupService
{

}

/**
 * @deprecated
 */
class users_WebsitefrontendgroupService extends users_FrontendgroupService
{
	
}

/**
 * @deprecated
 */
class users_DynamicfrontendgroupService extends users_DynamicgroupService
{
	
}

/**
 * @deprecated
 */
class users_BackenduserService extends users_UserService
{
	
}

/**
 * @deprecated
 */
class users_FrontenduserService extends users_UserService
{
	
}

/**
 * @deprecated
 */
class users_WebsitefrontenduserService extends users_FrontenduserService
{
	
}

/**
 * @deprecated
 */
class users_PreferencesService extends f_persistentdocument_DocumentService
{
}

/**
 * @deprecated
 */
class users_persistentdocument_backenduser extends users_persistentdocument_user
{

}

/**
 * @deprecated
 */
class users_persistentdocument_dynamicfrontendgroup extends users_persistentdocument_dynamicgroup
{
	
}

/**
 * @deprecated
 */
class users_persistentdocument_frontendgroup extends users_persistentdocument_group
{
}

/**
 * @deprecated
 */
class users_persistentdocument_frontenduser extends users_persistentdocument_user
{
}

/**
 * @deprecated
 */
class users_persistentdocument_websitefrontendgroup extends users_persistentdocument_frontendgroup
{
}

/**
 * @deprecated
 */
class users_persistentdocument_websitefrontenduser extends users_persistentdocument_frontenduser
{

}

/**
 * @deprecated
 */
class CreateDynamicfrontendgroupAction extends users_CreateDynamicgroupAction
{
	
}

/**
 * @deprecated
 */
class users_FrontendLogoutAction extends users_LogoutAction
{
	
}

/**
 * @deprecated
 */
abstract class user_UserHelper
{

	/**
	 * @deprecated
	 */
	const DEFAULT_BACKEND_REPLACEMENTS = 'login, password, accesslink, fullname, title';
}

/**
 * @deprecated
 */
class users_UserConverterHelper
{

	/**
	 * @deprecated
	 */
	public static function convertFrontEndUserToWebsiteFronEndUser($frontUserId, $websiteId = null)
	{
		throw new Exception('Deprecated'); 
	}
}