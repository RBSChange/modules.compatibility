<?php
/**
 * @deprecated
 */
class import_SparatedValuesImporter
{
	/**
	 * @deprecated
	 */
	public function __construct($definition, $separator, $lineSeparator = "\n")
	{
		$this->definition = $definition;
		$this->separator = $separator;
		$this->lineSeparator = $lineSeparator;
	}

	/**
	 * @deprecated
	 */
	private $definition;	

	/**
	 * @deprecated
	 */
	private function setDefinition($definition)
	{
		$this->definition = $definition;
	}

	/**
	 * @deprecated
	 */
	private function getDefinition()
	{
		return $this->definition;
	}
	
	/**
	 * @deprecated
	 */
	private $separator;
	
	/**
	 * @deprecated
	 */
	private function setSeparator($separator)
	{
		$this->separator = $separator;
	}
	
	/**
	 * @deprecated
	 */
	private function getSeparator()
	{
		return $this->separator;
	}
	
	/**
	 * @deprecated
	 */
	private $lineSeparator;
	
	/**
	 * @deprecated
	 */
	private function setLineSeparator($lineSeparator)
	{
		$this->lineSeparator = $lineSeparator;
	}
	
	/**
	 * @deprecated
	 */
	private function getLineSeparator()
	{
		return $this->lineSeparator;
	}
	
	/**
	 * @deprecated
	 */
	private $fromEncoding = 'UTF-8';
	
	/**
	 * @deprecated
	 */
	public function setFromEncoding($fromEncoding)
	{
		$this->fromEncoding = $fromEncoding;
	}
	
	/**
	 * @deprecated
	 */
	public function getFromEncoding()
	{
		return $this->fromEncoding;
	}
	
	/**
	 * @deprecated
	 */
	public function read($fileContent)
	{
		$lines = explode($this->getLineSeparator(), $fileContent);
		
		$data = array();
		foreach ($lines as $line)
		{
			// Ignore empty lines.
			if ($line)
			{
				$data[] = $this->readLine($line);
			}
		}
		
		return $data;
	}
	
	/**
	 * @deprecated
	 */
	public function import($filePath)
	{
		$fileContent = f_util_FileUtils::read($filePath);
		$fileContent = f_util_StringUtils::convertEncoding($fileContent, $this->getFromEncoding());
		return $this->read($fileContent);
	}
	
	/**
	 * @deprecated
	 */
	private function readLine($line)
	{
		$lineValues = explode($this->getSeparator(), $line);
		
		// Get the different values.
		$values = array();
		foreach ($this->getDefinition() as $index => $column)
		{
			if ($column != '')
			{
				$values[$column] = $lineValues[$index];
			}
		}
		return $values;
	}
}