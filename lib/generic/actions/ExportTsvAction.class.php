<?php
/**
 * @deprecated
 */
class generic_ExportTsvAction extends generic_ExportAction
{
	/**
	 * @deprecated
	 */
	public function _execute($context, $request)
	{
		$this->separator = "\t";
		$this->fileName = $request->getParameter('module') . "_" . date('Y-m-d_H\Hi') . ".txt" ;
		 
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