<?php
/**
 * @deprecated
 */
class generic_ExportCsvAction extends generic_ExportAction
{
	/**
	 * @deprecated
	 */
	public function _execute($context, $request)
	{
		$this->separator = ";";
		$this->fileName = $request->getParameter('module') . "_" . date('Y-m-d_H\Hi') . ".csv" ;
		 
		return parent::_execute($context, $request);
	}

	/**
	 * @deprecated
	 */
	protected function isDocumentAction()
	{
		return true;
	}
}