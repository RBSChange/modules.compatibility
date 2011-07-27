<?php
/**
 * @deprecated
 */
class comment_CommentHelper 
{
	/**
	 * @deprecated use comment_CommentService::frontendValidation()
	 */
	static function validateComment($comment)
	{
		$comment->getDocumentService()->frontendValidation($comment);
	}
}