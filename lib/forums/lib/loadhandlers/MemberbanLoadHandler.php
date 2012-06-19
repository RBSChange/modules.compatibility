<?php
/**
 * @deprecated
 */
class forums_MemberbanLoadHandler extends website_ViewLoadHandlerImpl
{
	/**
	 * @deprecated
	 */
	public function execute($request, $response)
	{
		$post = $this->getDocumentParameter();
		$request->setAttribute('post', $post);
		$request->setAttribute('bans', forums_BanService::getInstance()->getBansForUser($post->getPostauthor()));
	}
}