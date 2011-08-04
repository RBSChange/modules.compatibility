<?php
/**
 * @deprecated
 */
class list_GetItemsErrorView extends change_View
{
	/**
	 * @deprecated
	 */
	public function _execute($context, $request)
	{
		$this->sendHttpHeaders();
		$this->setTemplateName('Generic-Response', K::XML);
		$this->setStatus(self::STATUS_ERROR);
		
		if ($request->hasAttribute('message'))
		{
			$this->setAttribute('message', $request->getAttribute('message'));
		}
		
		if ($request->hasAttribute('document'))
		{
			$document = $request->getAttribute('document');
			$this->setAttribute('id', $document->getId());
			if (method_exists($document, 'getLang'))
			{
				$this->setAttribute('lang', $document->getLang());
			}
		}
		else
		{
			$this->setAttribute('id', '0');
		}
		
		if ($request->hasAttribute('contents'))
		{
			$this->setAttribute('contents', $request->getAttribute('contents'));
		}
		
		if ($request->hasAttribute('access'))
		{
			$this->setAttribute('access', $request->getAttribute('access'));
		}
	}
}