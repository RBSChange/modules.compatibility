<?php
/**
 * @deprecated
 */
interface f_mvc_Session
{
	/**
	 * @deprecated
	 */
	function setAttribute($name, $value);
	
	/**
	 * @deprecated
	 */
	function getAttribute($name);
	
	/**
	 * @deprecated
	 */
	function getAttributes();
	
	/**
	 * @deprecated
	 */
	function removeAttribute($name);
}

/**
 * @deprecated
 */
class f_mvc_HTTPSession implements f_mvc_Session 
{
	/**
	 * @deprecated
	 */
	function setAttribute($name, $value)
	{
		$storage = change_Controller::getInstance()->getStorage();
		$storage->write($name, $value);
	}

	/**
	 * @deprecated
	 */
	function getAttribute($name)
	{
		$storage = change_Controller::getInstance()->getStorage();
		$storage->read($name);
	}

	/**
	 * @deprecated
	 */
	function removeAttribute($name)
	{
		$storage = change_Controller::getInstance()->getStorage();
		$storage->remove($name);
	}
	

	/**
	 * @deprecated
	 */
	function getAttributes()
	{
		$storage = change_Controller::getInstance()->getStorage();
		return $storage->readAll();
	}

	/**
	 * @deprecated
	 */
	function hasAttribute($name)
	{
		$storage = change_Controller::getInstance()->getStorage();
		return $storage->read($name) !== null;
	}
}