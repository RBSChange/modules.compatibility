<?php
/**
 * @deprecated
 */
class TemplateLoader extends FileLoader implements ResourceLoader
{

	/**
	 * @deprecated
	 */
	private static $instance;

	/**
	 * @deprecated
	 */
	protected function __construct()
	{
		$this->resolver = TemplateResolver::getInstance();
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
		self::$instance->resolver->reset();
		return self::$instance;
	}

	/**
	 * @deprecated
	 */
	public function load($filename)
	{
		$currentPageId = website_PageService::getInstance()->getCurrentPageId();
		if ($currentPageId)
		{
			$currentPage = DocumentHelper::getDocumentInstance($currentPageId, "modules_website/page");
			list($theme, ) = explode('/', $currentPage->getTemplate());
			
			$themeDir = f_util_FileUtils::buildProjectPath('themes', $theme);
			$this->resolver->addPotentialDirectory($themeDir);
			$overrideThemeDir = f_util_FileUtils::buildOverridePath('themes', $theme);
			$this->resolver->addPotentialDirectory($overrideThemeDir);
		}
		$path = $this->resolver->getPath($filename);
		if ($path === null)
		{
			throw new TemplateNotFoundException($this->getDirectory()."/".$filename, $this->getPackageName());
		}
		
		$template = new TemplateObject($path, $this->resolver->getMimeContentType());
		
		if (Framework::inDevelopmentMode() && $this->resolver->getMimeContentType() === 'html')
		{
			$template->setOriginalPath($path); 
		}
		return $template;
	}

	/**
	 * @deprecated
	 */
	public function setMimeContentType($type)
	{
		$this->resolver->setMimeContentType($type);
		return $this;
	}
}
