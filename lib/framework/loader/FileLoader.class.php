<?php
/**
 * @deprecated
 */
class FileLoader implements ResourceLoader
{

	/**
	 * @deprecated
	 */
	private static $instance = null;

	/**
	 * @deprecated
	 */
	protected $resolver;

	protected function __construct()
	{
		$this->resolver = FileResolver::getInstance();
	}

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
	 * @deprecated
	 */
	public function load($filename)
	{
		return  $this->resolver->getPath($filename);
	}

	/**
	 * @deprecated
	 */
	public function setPackageName($packageName)
	{
		$this->resolver->setPackageName($packageName);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getPackageName()
	{
		return $this->resolver->getPackageName();
	}

	/**
	 * @deprecated
	 */
	public function setDirectory($directory)
	{
		$this->resolver->setDirectory($directory);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getDirectory()
	{
		return $this->resolver->getDirectory();
	}

	/**
	 * @deprecated
	 */
	public function reset()
	{
		$this->resolver->reset();
		return $this;
	}

}
