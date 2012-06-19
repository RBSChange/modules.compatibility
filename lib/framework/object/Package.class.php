<?php
/**
 * @deprecated
 */
class Package
{

	/**
	 * @deprecated
	 */
	public $classPath = null;
	
	static private $packageInstance = array();


	/**
	 * @deprecated
	 */
	const PHP_EXTENSION = '.php';

	/**
	 * @deprecated
	 */
	const CLASS_PHP_EXTENSION = '.class.php';

	/**
	 * Construct a Package object with the specified classPath.
	 *
	 * @param string $classPath Class path to use in this Package object.
	 */
	private function __construct($classPath)
	{
		$this->classPath = $classPath;
	}

	/**
	 * @deprecated
	 */
	static public function getInstance($classPath)
	{
		if (!array_key_exists($classPath, self::$packageInstance)) {
			self::$packageInstance[$classPath] = new Package($classPath);
		}
		return self::$packageInstance[$classPath];
	}

	/**
	 * @deprecated
	 */
	public function newClassInstance($className)
	{
		$className = $this->getClassName($className);
		$funArgs = func_get_args();
		if (count($funArgs) > 1) {
			$id = $funArgs[1];
		} else {
			$id = null;
		}
		$newObj = new $className($id);
		if (is_callable(array($className, 'initialize')) === true) {
			call_user_func_array(array($newObj, 'initialize'), array_slice($funArgs, 2));
		}
		return $newObj;
	}

	/**
	 * @deprecated
	 */
	public function getClassName($className)
	{
		$className = str_replace('.', '_', $this->classPath) . '_' . $className;

		return $className;
	}

	/**
	 * @deprecated
	 */
	static public function getShortClassName($realClassName)
	{
		$className = explode('_', $realClassName);
		return $className[count($className) - 1];
	}

	/**
	 * @deprecated
	 */
	public function callClassMethod($className, $methodName)
	{
		try {
			if (!is_callable(array($this->getClassName($className), $methodName))) {
				$error = sprintf('Unknown method "%s" for class "%s" in package "%s"', $methodName, $className, $this->classPath);
				throw new Exception($error);
			}
			$funArgs = func_get_args();
			$result = call_user_func_array(array($this->getClassName($className), $methodName), array_slice($funArgs, 2));
		}
		catch (Exception $e) {

			$e = new Exception($e->getMessage(), null,$e);
			$e->printStackTrace();
		}
		return $result;
	}

	/**
	 * @deprecated
	 */
	public function isInstance($object, $className)
	{
		$longClassName = $this->getClassName($className);
		return ($object instanceof $longClassName);
	}

	/**
	 * @deprecated
	 */
	public static function makeSystemPath($dotPathPath)
	{
		return str_replace(array('.', '/', '\\'), DIRECTORY_SEPARATOR, $dotPathPath);
	}
}