<?php
/**
 * @deprecated use website_StyleService
 */
class StyleService extends website_StyleService
{
	/**
	 * @deprecated use website_StyleService::getInstance()
	 */
	public static function getInstance()
	{
		Framework::error('Call to deprecated class JsService. Use website_StyleService instead.');
		return parent::getInstance();
	}
}