<?php
/**
 * @deprecated
 */
class f_util_Properties
{
	/**
	 * @deprecated
	 */
	private $properties;
	
    /**
	 * @deprecated
	 */
	private $preserveComments = false;
	
    /**
	 * @deprecated
	 */
	private $preserveEmptyLines = false;

    /**
	 * @deprecated
	 */
	function load($path)
	{
		if (!is_readable($path))
		{
			throw new Exception("Can not read file $path");
		}
		$this->parse($path);
	}

    /**
	 * @deprecated
	 */
	function save($path)
	{
		$dir = dirname($path);
		if ((!file_exists($path) && !is_writable($dir)) || (file_exists($path) && !is_writable($path)))
		{
			throw new Exception("Can not write to $path");
		}
		if (file_put_contents($path, $this->__toString()) === false)
		{
			throw new Exception("Could not write to $path");
		}
	}
	
	/**
	 * @deprecated
	 */
	function setPreserveComments($preserveComments)
	{
		$this->preserveComments = $preserveComments;
	}
	
	/**
	 * @deprecated
	 */
	function setPreserveEmptyLines($preserveEmptyLines)
	{
		$this->preserveEmptyLines = $preserveEmptyLines;
	}

	/**
	 * @deprecated
	 */
	public function __toString()
	{
		if ($this->properties !== null)
		{
			$buf = "";
			foreach($this->properties as $key => $item)
			{
				if ($this->preserveComments && is_int($key))
				{
					$buf .= $item."\n";
				}
				else
				{
					$buf .= $key . "=" . $this->writeValue($item)."\n";
				}
			}
			return $buf;
		}
		return "";
	}

	/**
	 * @deprecated
	 */
	function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @deprecated
	 */
	function getProperty($prop, $defaultValue = null)
	{
		if (!isset($this->properties[$prop]))
		{
			return $defaultValue;
		}
		return $this->properties[$prop];
	}

	/**
	 * @deprecated
	 */
	function setProperty($key, $value)
	{
		$oldValue = @$this->properties[$key];
		$this->properties[$key] = $value;
		return $oldValue;
	}

	/**
	 * @deprecated
	 */
	function propertyNames()
	{
		return $this->keys();
	}

	/**
	 * @deprecated
	 */
	function hasProperty($key)
	{
		return isset($this->properties[$key]);
	}

	/**
	 * @deprecated
	 */
	function isEmpty()
	{
		return empty($this->properties);
	}

	// protected methods

	/**
	 * @deprecated
	 */
	protected function readValue($val)
	{
		if ($val === "true")
		{
			$val = true;
		}
		elseif ($val === "false")
		{
			$val = false;
		}
		else
		{
			$valLength = strlen($val);
			if ($val[0] == "'" && $val[$valLength-1] == "'" || $val[0] == "\"" && $val[$valLength-1] == "\"")
			{
				$val = substr($val, 1, -1);
			}
		}
		return $val;
	}

	/**
	 * @deprecated
	 */
	protected function writeValue($val)
	{
		if ($val === true)
		{
			$val = "true";
		}
		elseif ($val === false)
		{
			$val = "false";
		}
		return $val;
	}

	// private methods

	/**
	 * @deprecated
	 */
	private function parse($filePath)
	{
		$lines = @file($filePath);
		$this->properties = array();
		foreach($lines as $line)
		{
			$line = trim($line);
			if($line == "")
			{
				if ($this->preserveEmptyLines)
				{
					$this->properties[] = " ";
				}
				continue;
			}

			if ($line{0} == '#' || $line{0} == ';')
			{
				// it's a comment, so continue to next line
				if ($this->preserveComments)
				{
					$this->properties[] = $line;
				}
				continue;
			}
			else
			{
				$pos = strpos($line, '=');
				if ($pos === false)
				{
					throw new Exception("Invalid property file line $line");
				}
				$property = trim(substr($line, 0, $pos));
				$value = trim(substr($line, $pos + 1));
				$this->properties[$property] = $this->readValue($value);
			}
		}
	}
}