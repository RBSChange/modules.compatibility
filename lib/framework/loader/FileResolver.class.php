<?php
/**
 * @deprecated
 */
class FileResolver implements ResourceResolver
{
	/**
	 * @deprecated
	 */
	private static $instance = null;

	/**
	 * @deprecated
	 */
	private $packageName = null;

	/**
	 * @deprecated
	 */
	private $directory = null;

	protected function __construct()
	{

	}

	/**
	 * @deprecated
	 */
	public static function getInstance()
	{

		if( is_null(self::$instance) )
		{
			self::$instance = new self();
		}
		self::$instance->reset();
		return self::$instance;

	}

	/**
	 * @deprecated
	 */
	public function reset()
	{
		$this->directory = null;
		$this->packageName = null;
		$this->resetPotentialDirectories();
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getPath($fileName)
	{
		return $this->resolvePath($fileName);
	}

	/**
	 * @deprecated
	 */
	public function getPaths($fileName)
	{
		return $this->resolvePaths($fileName);
	}

	/**
	 * @deprecated
	 */
	public function setPackageName($packageName)
	{
		$this->packageName = str_replace('_', DIRECTORY_SEPARATOR, $packageName);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getPackageName()
	{
		return $this->packageName;
	}

	/**
	 * @deprecated
	 */
	public function setDirectory($directory)
	{
		$this->directory = $this->cleanPath($directory);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getDirectory()
	{
		return $this->directory;
	}

	/**
	 * @deprecated
	 */
	private function resolvePath($fileName)
	{
		// If package not defined throw exception because you don't know where you must search.
		if (NULL === $this->packageName)
		{
			throw new BadInitializationException('Package name must be defined. Use setPackageName($packageName).');
		}

		// Construct the relative path
		$relativePath = DIRECTORY_SEPARATOR . $this->packageName;
		if (NULL !== $this->directory)
		{
			$relativePath .= DIRECTORY_SEPARATOR . $this->directory;
		}	
		$relativePath .= DIRECTORY_SEPARATOR . $fileName;
		
		$potentialDirectories = $this->getPotentialDirectories();
		foreach ($potentialDirectories as $directory)
		{
			$sourceFile = $directory . $relativePath;
			if ( is_readable($sourceFile) )
			{
				return $sourceFile;
			}
		}
		return NULL;
	}

	/**
	 * @deprecated
	 */
	private $potentialDirectories;
	
	/**
	 * @deprecated
	 */
	private function resetPotentialDirectories()
	{
		$this->potentialDirectories = array(f_util_FileUtils::buildOverridePath(), f_util_FileUtils::buildProjectPath(), f_util_FileUtils::buildChangeBuildPath());
	}
	
	/**
	 * @deprecated
	 */
	public function addPotentialDirectory($directory)
	{
		if (!in_array($directory, $this->potentialDirectories))
		{
			array_unshift($this->potentialDirectories, $directory);
		}
	}
	
	/**
	 * @deprecated
	 */
	private function getPotentialDirectories()
	{
		return $this->potentialDirectories;
	}
	
	/**
	 * @deprecated
	 */
	public function addCurrentWebsiteToPotentialDirectories()
	{
		$currentWebsite = website_WebsiteService::getInstance()->getCurrentWebsite();
		if (!is_null($currentWebsite))
		{
			$directory = f_util_FileUtils::buildOverridePath('hostspecificresources', $currentWebsite->getDomain());
			if (is_dir($directory)) 
			{
				$this->addPotentialDirectory($directory);
			}
		}	
	}
	
	/**
	 * @deprecated
	 */
	private function resolvePaths($fileName)
	{
		// If package not defined throw exception because you don't know where you must search.
		if (NULL === $this->packageName)
		{
			throw new BadInitializationException('Package name must be defined. Use setPackageName($packageName).');
		}

		// Construct the relative path
		$relativePath = DIRECTORY_SEPARATOR . $this->packageName;
		if (NULL !== $this->directory)
		{
			$relativePath .= DIRECTORY_SEPARATOR . $this->directory;
		}
		$relativePath .= DIRECTORY_SEPARATOR . $fileName;

		$potentialDirectories = $this->getPotentialDirectories();
		$paths = array();
		foreach ($potentialDirectories as $directory)
		{
			$sourceFile = $directory . $relativePath;
			if ( is_readable($sourceFile) )
			{
				$paths[] = $sourceFile;
			}
		}
		if (count($paths) === 0)
		{
			return NULL;
		}
		return $paths;
	}

	/**
	 * @deprecated
	 */
	private function cleanPath($directory)
	{
		while ($directory{0} == DIRECTORY_SEPARATOR)
		{
			$directory = substr($directory, 1);
		}
		while (substr($directory, -1) == DIRECTORY_SEPARATOR)
		{
			$directory = substr($directory, 0, -1);
		}
		return $directory;
	}
}

