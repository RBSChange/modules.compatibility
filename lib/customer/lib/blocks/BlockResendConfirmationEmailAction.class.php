<?php
/**
 * @deprecated
 */
class customer_BlockResendConfirmationEmailAction extends website_BlockAction
{
	/**
	 * @deprecated
	 */
	function execute($request, $response)
	{
		if ($this->isInBackofficeEdition())
		{
			return website_BlockView::NONE;
		}

		$customerService = customer_CustomerService::getInstance();
		$customer = $customerService->getCurrentCustomer();
		if ($customer !== null)
		{
			$success = $customerService->sendEmailConfirmationEmail($customer);
			$request->setAttribute('success', $success);
			return website_BlockView::SUCCESS;
		}
		
		return website_BlockView::NONE;
	}
}