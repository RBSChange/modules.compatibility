<?php
/**
 * @deprecated (will be removed in 4.0)
 */
class customer_EmailConfirmationAction extends f_action_BaseAction
{
	/**
	 * @deprecated (will be removed in 4.0)
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
		return View::NONE;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function getRequestMethods()
	{
		return Request::POST | Request::GET;
	}
	
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	public function isSecure()
	{
		return false;
	}
}