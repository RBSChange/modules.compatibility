<?php
/**
 * @deprecated
 */
class customer_EmailConfirmationAction extends change_Action
{
	/**
	 * @deprecated
	 */
	public function _execute($context, $request)
	{
		$cs = customer_CustomerService::getInstance();

		// Validate and perform the confirmation.
		$customer = $this->getDocumentInstanceFromRequest($request);
		$confirmationStatus = $cs->validateEmailConfirmation($customer, $request->getParameter('mailref'));

		// Redirect to the good page.
		$url = $cs->getEmailConfirmationRedirectionUrl($confirmationStatus);
		$context->getController()->redirectToUrl(str_replace('&amp;', '&', $url));
		return change_View::NONE;
	}
	
	/**
	 * @deprecated
	 */
	public function getRequestMethods()
	{
		return change_Request::POST | change_Request::GET;
	}
	
	/**
	 * @deprecated
	 */
	public function isSecure()
	{
		return false;
	}
}