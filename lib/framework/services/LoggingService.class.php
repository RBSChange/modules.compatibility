<?php
/**
 * @deprecated
 */
class Logger
{
	/**
	 * @deprecated
	 */
	const DEBUG = 1000;

	/**
	 * @deprecated
	 */
	const ERROR = 4000;

	/**
	 * @deprecated
	 */
	const INFO = 2000;

	/**
	 * @deprecated
	 */
	const WARN = 3000;

	/**
	 * @deprecated
	 */
	const FATAL = 5000;
}

/**
 * @deprecated
 */
class LoggingService
{
	protected $stdLogFilePath;
	 
	protected $errLogFilePath;
	
	protected function __construct()
	{
		$logDir = implode(DIRECTORY_SEPARATOR, array(PROJECT_HOME , 'log', 'project'));
		if (!is_dir($logDir)) {@mkdir($logDir, 0777, true);}		
		$this->stdLogFilePath = $logDir . DIRECTORY_SEPARATOR . 'application.log';
		$this->errLogFilePath = $logDir . DIRECTORY_SEPARATOR . 'phperror.log';
	}
	
	/**
	 * the singleton instance
	 * @var LoggingService
	 */
	private static $instance = null;
	
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
	public function log($stringLine, $loggerGroup)
	{
		error_log("\n". gmdate('Y-m-d H:i:s')."\t".$stringLine, 3, $this->stdLogFilePath);
	}
	
	/**
	 * @deprecated
	 */
	public function errorLog($stringLine, $loggerGroup)
	{
		error_log("\n". gmdate('Y-m-d H:i:s')."\t".$stringLine, 3, $this->errLogFilePath);
	}
}