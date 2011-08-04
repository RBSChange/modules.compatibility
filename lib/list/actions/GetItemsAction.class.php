<?php
/**
 * @deprecated
 */
class list_GetItemsAction extends change_Action
{
	/**
	 * @deprecated
	 */
	public function _execute($context, $request)
	{
		// Retrieve request data
		$listName = $request->getParameter(K::COMPONENT_ID_ACCESSOR);
		$rc = RequestContext::getInstance();
		try 
		{
			$rc->beginI18nWork($rc->getUILang());		
			$ls = $this->getListService();
	
			try
			{
				$list = $ls->getDocumentInstanceByListId($listName);
			}
			catch (BaseException $e)
			{
				Framework::exception($e);	
				// The list has not been found: switch to error view
				$request->setAttribute('message', $e->getMessage());
				return change_View::ERROR;
			}
			
			$request->setAttribute('items', $list->getItems());
			$rc->endI18nWork();
		}
		catch (Exception $e)
		{
			$request->setAttribute('items', array());
			$rc->endI18nWork($e);
			Framework::exception($e);
		}

		return change_View::SUCCESS;
	}

	/**
	 * @deprecated
	 */
	public function getListService()
	{
		return list_ListService::getInstance();
	}

	/**
	 * @deprecated
	 */
	public function getRequestMethods()
	{
		return change_Request::POST | change_Request::GET;
	}
}