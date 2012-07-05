<?php
/**
 * @deprecated
 */
class TemplateResolver extends FileResolver implements ResourceResolver
{

	/**
	 * @deprecated
	 */
	private static $instance = null;

	/**
	 * @deprecated
	 */
	private $engine = 'all';
	/**
	 * @deprecated
	 */
	private $engineVersion = 'all';

	/**
	 * @deprecated
	 */
	private $mimeContentType = 'html' ;

	/**
	 * @deprecated
	 */
	public static function getInstance()
	{

		if ( is_null(self::$instance) )
		{
			self::$instance = new self();

		}
		self::$instance->reset();
		return self::$instance;

	}

	/**
	 * @deprecated
	 */
	protected function __construct()
	{
		// Set engine informations
		$this->resolveBrowserEngine();
		$this->setDirectory('templates');
	}

	/**
	 * @deprecated
	 */
	public function reset()
	{
		parent::reset();
		$this->setDirectory('templates');
		$this->setMimeContentType('html');
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getPath($templateName)
	{

		// If not found in cache file search with FileResolver
		// Test multi name case
		// Engine + Engine Version
		$path = parent::getPath($this->getFullFileName($templateName));

		// Engine + all
		if (NULL === $path)
		{
			$path = parent::getPath($this->getFullFileName($templateName, false));
		}

		// all + all
		if (NULL === $path)
		{
			$path = parent::getPath($this->getFullFileName($templateName, false, false));
		}
		
		if (NULL === $path)
		{
			$path = parent::getPath($templateName . '.' . $this->mimeContentType);
		}

		return $path;

	}

	/**
	 * @deprecated
	 */
	public function setMimeContentType($type)
	{

		$this->mimeContentType = $type;
		return $this;

	}

	/**
	 * @deprecated
	 */
	public function getMimeContentType()
	{

		return $this->mimeContentType;

	}

	/**
	 * @deprecated
	 */
	private function getFullFileName($templateName, $useVersion = true, $useEngine = true)
	{

		$engine = $this->engine;
		$engineVersion = $this->engineVersion;

		// Test if the name must use the engine version
		if ($useVersion === false)
		{
			$engineVersion = 'all';
		}

		// Test if the name must use the engine name
		if ($useEngine === false)
		{
			$engine = 'all';
		}

		return $templateName . "." . $engine . "." . $engineVersion . "." . $this->mimeContentType;

	}

	/**
	 * @deprecated
	 */
	private function resolveBrowserEngine()
	{
        $requestContext = RequestContext::getInstance();
        $this->engine = $requestContext->getUserAgentType();
        $this->engineVersion = $requestContext->getUserAgentTypeVersion();
	}

}
