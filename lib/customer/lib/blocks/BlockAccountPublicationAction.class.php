<?php
/**
 * @deprecated (will be removed in 4.0)
 */
class customer_BlockAccountPublicationAction extends website_BlockAction
{
	/**
	 * @deprecated (will be removed in 4.0)
	 */
	function execute($request, $response)
	{
		if ($this->isInBackofficeEdition())
		{
			return website_BlockView::BACKOFFICE;
		}
		
		// Customer state.
		$customerService = customer_CustomerService::getInstance();
		$customer = $customerService->getCurrentCustomer();
		if ($customer !== null)
		{
			$customerService->publishIfPossible($customer->getId());
			if (!$customer->isPublished())
			{
				switch ($customer->getNotActivatedReason())
				{
					case customer_CustomerService::REASON_CONFIRM_EMAIL_ADDRESS :
						$request->setAttribute('message', f_Locale::translate('&modules.customer.frontoffice.Unpublished-confirm-email;'));
						$request->setAttribute('display', array(
							'contactLink' => true, 
							'resendLink' => true, 
							'completeFormLink' => false
						));
						break;
						
					case customer_CustomerService::REASON_CONFIRM_ACCOUNT :
						$request->setAttribute('message', f_Locale::translate('&modules.customer.frontoffice.Unpublished-confirm-informations;'));
						$request->setAttribute('display', array(
							'contactLink' => true, 
							'resendLink' => false, 
							'completeFormLink' => true
						));
						break;
					
					default :
						$request->setAttribute('message', f_Locale::translate('&modules.customer.frontoffice.Unpublished-deactivated;'));
						$request->setAttribute('display', array(
							'contactLink' => true, 
							'resendLink' => false, 
							'completeFormLink' => false
						));
						break;
				}
			}
		}
	
		// Confirmation status.
		switch ($request->getParameter('confirmationCode'))
		{
			case customer_CustomerService::EMAIL_CONFIRMATION_OK :
				$request->setAttribute('successMessage', f_Locale::translate('&modules.customer.frontoffice.Confirmation-ok;'));
				break;
				
			case customer_CustomerService::EMAIL_CONFIRMATION_NO_CUSTOMER :
				$request->setAttribute('errorMessage', f_Locale::translate('&modules.customer.frontoffice.Confirmation-no-customer;'));
				break;

			case customer_CustomerService::EMAIL_CONFIRMATION_BAD_STATE :
				$request->setAttribute('errorMessage', f_Locale::translate('&modules.customer.frontoffice.Confirmation-bad-state;'));
				break;
				
			case customer_CustomerService::EMAIL_CONFIRMATION_BAD_EMAIL :
				$request->setAttribute('errorMessage', f_Locale::translate('&modules.customer.frontoffice.Confirmation-bad-email;'));
				break;		
		}
		
		return website_BlockView::SUCCESS;
	}
}