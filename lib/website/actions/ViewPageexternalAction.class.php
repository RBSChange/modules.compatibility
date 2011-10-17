<?php
/**
 * @deprecated
 */
class website_ViewPageexternalAction extends change_Action
{
	/**
 * @deprecated
 */
	public function _execute($context, $request)
	{
		try 
		{
			$pageexternal = null;
			if ($request->hasParameter('cmpref'))
			{
				$pageexternal = $this->getDocumentInstanceFromRequest($request);
			}
			else if ($request->hasModuleParameter('website', 'cmpref'))
			{
				$pageexternal = DocumentHelper::getDocumentInstance($request->getModuleParameter('website', 'cmpref'));
			}
			
			if ($pageexternal instanceof website_persistentdocument_pageexternal && $pageexternal->isPublished())
			{
				change_Controller::getInstance()->redirectToUrl($pageexternal->getUrl());
			}
		}
		catch (Exception $e)
		{		
			Framework::exception($e);
		}
		$context->getController()->forward('website', 'Error404');
	}
	
	/**
	 * @deprecated
	 */
	public function isSecure()
	{
		return false;
	}

}