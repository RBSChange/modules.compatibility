<?php
/**
 * @deprecated
 */
class website_WebsiteModuleService extends change_BaseService
{
	/**
	 * @deprecated
	 */
	public static $modelNamesForMenu = array('modules_website/topic',
		'modules_website/page', 'modules_website/pagegroup',
		'modules_website/pageexternal', 'modules_website/pagereference');

	/**
	 * @deprecated
	 */
	public function __call($name, $arguments)
	{
		switch ($name)
		{
			case 'setCurrentPageId':
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_ListStylesheetsService::getInstance()->getWebsiteAndTopicStylesheets();			
			case 'setCurrentPageId': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				return website_PageService::getInstance()->setCurrentPageId($arguments[0]);	
			case 'getCurrentPageId': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				return website_PageService::getInstance()->getCurrentPageId();
			case 'getCurrentPage': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				return website_PageService::getInstance()->getCurrentPage();
			case 'getCurrentPageAncestorsIds': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				return website_PageService::getInstance()->getCurrentPageAncestorsIds();
			case 'getCurrentPageAncestors': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				return website_PageService::getInstance()->getCurrentPageAncestors();				
			case 'getIndexPage': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				$topic = $arguments[0]; $getFirstPageIfNotFound = isset($arguments[1]) ? $arguments[1] : true;	
				if ($topic instanceof website_persistentdocument_topic || $topic instanceof website_persistentdocument_website)
				{
					return $topic->getDocumentService()->getIndexPage($topic, $getFirstPageIfNotFound);
				}
				return null;
			case 'removeIndexPage': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				$topicOrPage = $arguments[0]; $userSetting = isset($arguments[1]) ? $arguments[1] : false;	
				if ($topicOrPage instanceof website_persistentdocument_topic)
				{
				   	$topicOrPage->getDocumentService()->removeIndexPage($topicOrPage, $userSetting);
				}
			   	elseif ($topicOrPage instanceof website_persistentdocument_page)
				{
					$topicOrPage->getDocumentService()->removeIndexPage($topicOrPage, $userSetting);
				}
				elseif ($topicOrPage instanceof website_persistentdocument_pageexternal)
				{
					$topicOrPage->getDocumentService()->removeIndexPage($topicOrPage, $userSetting);
				}
				return;
			case 'setHomePage': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				if ($arguments[0] instanceof website_persistentdocument_page)
				{
					$arguments[0]->getDocumentService()->makeHomePage($arguments[0]);
				}
				return;
			case 'setIndexPage': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				if ($arguments[0] instanceof website_persistentdocument_page)
				{
					$arguments[0]->getDocumentService()->makeIndexPage($arguments[0], $userSetting);
				}
				return;

			case 'setWebsiteMetaFromParentId': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				return website_WebsiteService::getInstance()->setWebsiteMetaFromParentId($arguments[0], $arguments[1]);					
			case 'hasUniqueDomainNameForLang': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');	
				return website_WebsiteService::getInstance()->hasUniqueDomainNameForLang($arguments[0], $arguments[1]);	
			case 'getDefaultWebsite': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->getDefaultWebsite();				
			case 'setDefaultWebsite': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->setDefaultWebsite($arguments[0]);				 
			case 'getWebsiteByUrl': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->getByUrl($arguments[0], isset($arguments[1]) ? $arguments[1] : false);
			case 'getCurrentWebsite': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->getCurrentWebsite(isset($arguments[0]) ? $arguments[0] : false);				
			case 'setCurrentWebsiteId': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->setCurrentWebsiteId($arguments[0]);
			case 'setCurrentWebsite': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->setCurrentWebsite($arguments[0]);
			case 'getWebsiteInfos': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->getWebsiteInfos($arguments[0]);
			case 'getParentWebsite': 
				Framework::error('Call to deleted ' . get_class($this) . '->' . $name . ' method');
				return website_WebsiteService::getInstance()->getByDocument($arguments[0]);								
			default:
				throw new Exception('No method ' . get_class($this) . '->' . $name);				
		}
	}
}