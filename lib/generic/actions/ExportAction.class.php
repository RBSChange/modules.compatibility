<?php
/**
 * @deprecated
 */
class generic_ExportAction extends change_Action
{
	/**
	 * @deprecated
	 */
	protected $separator = null;
	
	/**
	 * @deprecated
	 */
	protected $fileName = null;
	
	/**
	 * @deprecated
	 */
	protected $currentRow = 0;

	/**
	 * @deprecated
	 */
	protected function getDocumentIdArrayFromRequest($request)
	{
		$ids = $request->getParameter(K::COMPONENT_ID_ACCESSOR, array());
		if (is_string($ids))
		{
			return explode(',', $request->getParameter(K::COMPONENT_ID_ACCESSOR));
		}
		return $ids;
	}

    /**
	 * @deprecated
	 */
	public function _execute($context, $request)
    {

    	try
    	{
	    	// Get the list of document to export
	    	$ids = $this->getDocumentIdArrayFromRequest($request);

	    	$headers = array();
	    	$headers[] = 'Cache-Control: public, must-revalidate';
		    $headers[] = 'Pragma: hack';
	        $headers[] = 'Content-type: application/octet-stream';
		    $headers[] = 'Content-Disposition: attachment; filename="' . $this->fileName . '"';
		    $headers[] = 'Content-Transfer-Encoding: binary';

		    foreach ($headers as $header)
		    {
		    	header($header);
		    }

	    	foreach ($ids as $id)
			{
				$document = DocumentHelper::getDocumentInstance($id);
				$this->exportDocument($document);
			}
    	}
    	catch (Exception $e)
    	{
    		Framework::exception($e);
    	}

		return change_View::NONE;
	}

	/**
	 * @deprecated
	 */
	protected function exportDocument($document)
	{
		if ($document instanceof export_ExportableDocument)
		{
			foreach ($document->getExportedDocument()->getProperties() as $array)
			{
				$string = "";
				if ($this->currentRow == 0)
				{
					$columnsHeader = "";
					foreach (array_keys($array) as $value)
					{
						$columnsHeader .= "\"".utf8_decode($value)."\"".$this->separator;
					}
					echo $columnsHeader .= K::CRLF;

				}

				$this->currentRow += 1;
				foreach (array_values($array) as $value)
				{
					$string .= "\"".utf8_decode(str_replace(array("\n", "\r"), "", f_util_StringUtils::htmlToText($value, false)))."\"".$this->separator;
				}
				echo $string .= K::CRLF;
			}
			flush();
		}
	}

	/**
	 * @deprecated
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}