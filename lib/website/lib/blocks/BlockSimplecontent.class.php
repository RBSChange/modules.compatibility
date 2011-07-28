<?php
/**
 * @deprecated
 */
class website_BlockSimplecontentAction extends website_BlockAction
{
	/**
	 * @deprecated
	 */
	function execute($request, $response)
	{
		$viewName = $this->getConfiguration()->getView();
		if (f_util_StringUtils::isEmpty($viewName))
		{
			throw new Exception("Block website_simplecontent: missing view parameter");
		}
		$viewInfo = explode('/', $viewName);
		if (count($viewInfo) == 1)
		{
			$module = "website";
		}
		else
		{
			$module = $viewInfo[0];
			$viewName = $viewInfo[1];
		}
		$templateName = ucfirst($module) .'-Block-Simplecontent-' . $viewName;

		$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
		$request->setAttribute("website", $website);

		return $this->getTemplateByFullName("modules_".$module, $templateName);
	}
}
