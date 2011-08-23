<?php
class generic_ConvertPdfAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$controller = $context->getController();
		
		$url = base64_decode($request->getParameter('url'));
		$url = str_replace('&amp;', '&', $url);
		
		$pdfService = UrlPDFService::getInstance();
		
		$serverIp = $this->getConfigParameter('server_ip');
		if ($serverIp !== null)
		{
			$pdfService->setServerIP($serverIp);	
		}
		$serverPort = $this->getConfigParameter('server_port');
		if ($serverPort !== null)
		{
			$pdfService->setServerPort($serverPort);
		}

		try
		{
			$pdfConfiguration = $this->getConfiguration();
			$pdfService->setUserConnection($pdfConfiguration['user']);
			$pdfService->setPasswordConnection($pdfConfiguration['password']);
			$pdfService->setCustomerConnection($pdfConfiguration['customer']);
			
			$pdfService->setCachePath(PROJECT_HOME . DIRECTORY_SEPARATOR . MediaHelper::ROOT_MEDIA_PATH . CHANGE_CACHE_PDF);
			$pdfService->forceHTMLFormat();
			$pdfFile = $pdfService->getPDF($url);
		}
		catch (Exception $e)
		{
			$pdfFile = null;
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . ' EXCEPTION: ' . $e->getMessage());
			}
			echo f_Locale::translate("&framework.pdf.messages.error;");
		}
		
		if ($pdfFile !== null)
		{
			$controller->redirectToUrl("/publicmedia/" . CHANGE_CACHE_PDF . DIRECTORY_SEPARATOR . $pdfFile);
		}
		
		return change_View::NONE;
	}
	
	/**
	 * @param String $parameterName
	 * @param String $defaultValue
	 * @return String
	 */
	private function getConfigParameter($parameterName, $defaultValue = null)
	{
		$conf = $this->getConfiguration();
		if (isset($conf[$parameterName]))
		{
			return $conf[$parameterName];
		}
		return $defaultValue;
	}
	
	/**
	 * @var array
	 */
	private $configuration;
		
	/**
	 * @return array
	 */
	private function getConfiguration()
		{
			if ($this->configuration === null)
			{
			try
			{
				$this->configuration = Framework::getConfiguration('pdf');
			}
		catch (Exception $e)
		{
			Framework::exception($e->getMessage());
		}
	}
		return $this->configuration;
	}
	
	/**
	 * @return string
	 */
	public function getRequestMethods()
	{
		return change_Request::GET;
	}
	
	/**
	 * @return boolean
	 */
	public function isSecure()
	{
		return false;
	}
}