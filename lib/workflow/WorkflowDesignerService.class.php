<?php
/**
 * @deprecated
 */
class workflow_WorkflowDesignerService extends change_BaseService
{
	/**
	 * @deprecated
	 */
	private static $instance;

	/**
	 * @deprecated
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @deprecated use workflow_WorkflowService::getInstance()->getNewDocumentInstance()
	 */
	public function getNewWorkflowInstance()
	{
		return workflow_WorkflowService::getInstance()->getNewDocumentInstance();
	}

	/**
	 * @deprecated use workflow_PlaceService::getInstance()->getNewDocumentInstance()
	 */
	public function getNewPlaceInstance()
	{
		return workflow_PlaceService::getInstance()->getNewDocumentInstance();
	}

	/**
	 * @deprecated use workflow_TransitionService::getInstance()->getNewDocumentInstance()
	 */
	public function getNewTransitionInstance()
	{
		return workflow_TransitionService::getInstance()->getNewDocumentInstance();
	}

	/**
	 * @deprecated use workflow_ArcService::getInstance()->getNewDocumentInstance()
	 */
	public function getNewArcInstance()
	{
		return workflow_ArcService::getInstance()->getNewDocumentInstance();
	}
}