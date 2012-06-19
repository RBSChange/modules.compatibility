<?php
/**
 * @deprecated use website_ShortenUrlService
 * @method sharethis_ShortenUrlService getInstance()
 */
class sharethis_ShortenUrlService extends change_BaseService
{
	/**
	 * @deprecated use website_ShortenUrlService::shortenUrl
	 */
	public function shortenUrl($url)
	{
		return website_ShortenUrlService::getInstance()->shortenUrl($url);
	}
}