<?php
/**
 * @deprecated use website_JsService
 */
class JsService extends website_JsService
{
	/**
	 * @deprecated use website_JsService::getInstance()
	 */
	public static function getInstance()
	{
		Framework::error('Call to deprecated class JsService. Use website_JsService instead.');
		return parent::getInstance();
	}
	
	/**
	 * @deprecated use website_JsService::newInstance()
	 */
	public static function newInstance()
	{
		Framework::error('Call to deprecated class JsService. Use website_JsService instead.');
		return parent::newInstance();
	}
}