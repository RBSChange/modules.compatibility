<?php
/**
 * @deprecated
 */
class export_SparatedValuesExporter
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
	private $toEncoding = 'UTF-8';
	
	/**
	 * @deprecated
	 */
	public function setToEncoding($toEncoding)
	{
		$this->toEncoding = $toEncoding;
	}
	
	/**
	 * @deprecated
	 */
	public function getToEncoding()
	{
		return $this->toEncoding;
	}
	
	/**
	 * @deprecated
	 */
	public function write($data)
	{
		$compiledDefinition = $this->getCompiledDefinition();
		
		$contentArray = array();
		foreach ($data as $dataRow)
		{
			$contentArray[] = $this->writeLine($dataRow, $compiledDefinition);
		}

		return implode($this->getLineSeparator(), $contentArray);
	}
		
	/**
	 * @deprecated
	 */
	public function export($data, $filePath)
	{
		$fileContent = $this->write($data);
		$fileContent = f_util_StringUtils::convertEncoding($fileContent, 'UTF-8', $this->getToEncoding());
		f_util_FileUtils::write($filePath, $fileContent, f_util_FileUtils::OVERRIDE);
	}
	
	/**
	 * @deprecated
	 */
	private function writeLine($dataRow, $compiledDefinition)
	{
		// Get the different values.
		$values = array();
		foreach ($compiledDefinition as $column)
		{
			switch ($column['type'])
			{
				case 'object' :
					if (isset($dataRow[$column['key']]))
					{
						$object = $dataRow[$column['key']];
						$getter = $column['getter'];
						if (f_util_ClassUtils::methodExists($object, $getter))
						{
							$value = f_util_ClassUtils::callMethodOn($object, $getter);
						}
					}
					break;
				
				case 'basic' :
					if (isset($dataRow[$column['key']]))
					{
						$value = $dataRow[$column['key']];
					}
					break;
					
				default :
					$value = '';
					break;
			}
			
			$values[] = $value;
		}
		
		// Construct the line.
		return join($this->getSeparator(), $values);
	}
	
	/**
	 * @deprecated
	 */
	private function getCompiledDefinition()
	{
		$compiledDefinition = array();
		foreach ($this->getDefinition() as $index => $value)
		{
			$keys = explode('.', $value);
			if (count($keys) == 2 && $keys[0] && $keys[1])
			{
				$objectKey = $keys[0];
				$propertyKey = $keys[1];				
				$getter = 'get' . ucfirst($propertyKey);
				$compiledDefinition[$index] = array('type' => 'object', 'key' => $objectKey, 'getter' => $getter);
			}
			else if (count($keys) == 1 && $keys[0])
			{
				$objectKey = $keys[0];
				$compiledDefinition[$index] = array('type' => 'basic', 'key' => $objectKey);
			}
			else
			{
				$compiledDefinition[$index] = array('type' => 'empty');
			}
		}
		return $compiledDefinition;
	}
}