<?php
/**
 * @deprecated
 */
class list_GetItemsSuccessView extends change_View
{
	/**
	 * @deprecated
	 */
	public function _execute($context, $request)
	{
		$this->sendHttpHeaders();
		$this->setTemplateName('Generic-Response', K::XML);
		$this->setStatus(self::STATUS_OK);
		$lang = RequestContext::getInstance()->getUILang();
		$rc = RequestContext::getInstance();
		try
		{
			$rc->beginI18nWork($lang);
			$contents = array();
			$items = $request->getAttribute('items');
			
			foreach ($items as $item)
			{
				if ($item->getType())
				{
					$type = sprintf(' type="%s"', $item->getType());
				}
				else
				{
					$type = '';
				}
				
				if ($item->getIcon())
				{
					$icon = sprintf(' icon="%s"', $item->getIcon());
				}
				else
				{
					$icon = '';
				}
				
				$contents[] = sprintf('<document id="%s"%s%s><![CDATA[%s]]></document>', $item->getValue(), $type, $icon, $item->getLabel());
			
			}
			$rc->endI18nWork();
		}
		catch (Exception $e)
		{
			Framework::exception($e);
			$rc->endI18nWork($e);
		}
		$this->setAttribute('id', $request->getAttribute('listid'));
		$this->setAttribute('lang', $lang);
		$this->setAttribute('contents', join(K::CRLF, $contents));
	}
}