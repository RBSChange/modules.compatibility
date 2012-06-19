<?php
/**
 * @deprecated use website_ShortenUrlService
 */
class sharethis_ShortenUrlService extends change_BaseService
{
	/**
	 * @deprecated
	 */
	private static $instance = null;

	/**
	 * @deprecated
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * @deprecated use website_ShortenUrlService::shortenUrl
	 */
	public function shortenUrl($url)
	{
		return website_ShortenUrlService::getInstance()->shortenUrl($url);
	}
}