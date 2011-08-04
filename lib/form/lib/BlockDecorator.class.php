<?php
/**
 * @deprecated
 */
abstract class form_BlockFormDecorator
{
	/**
	 * @deprecated
	 */
	private $blockAction;

	/**
	 * @deprecated
	 */
	public final function __construct($blockAction)
	{
		$this->blockAction = $blockAction;
	}

	/**
	 * @deprecated
	 */
	protected final function setParameter($name, $value)
	{
		$this->blockAction->setParameter($name, $value);
	}

	/**
	 * @deprecated
	 */
	protected final function getParameter($name)
	{
		return $this->blockAction->getParameter($name);
	}

	/**
	 * @deprecated
	 */
	abstract public function execute($context, $request);
}