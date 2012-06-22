<?php
/**
 * @deprecated
 */
class comment_InitFormInfoLoadHandler extends website_ViewLoadHandlerImpl
{
	/**
	 * @deprecated
	 */
	function execute($request, $response)
	{
		$currentUser = users_UserService::getInstance()->getCurrentFrontEndUser();
		$request->setAttribute('currentUser', $currentUser);
	}
}