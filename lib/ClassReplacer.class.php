<?php
class compatibility_ClassReplacer
{
	private $classes = array();
	private $verbose = false;
	
	public function setClasses($classes)
	{
		$this->classes = $classes;
	}
	
	public function __construct($classes = array(), $verbose = false)
	{
		$this->setClasses($classes);
		$this->verbose = $verbose;
	}
	
	public function migrateFile($fullpath, $inString = false)
	{
		$tokens = token_get_all(file_get_contents($fullpath));
		$content = $this->replaceClasses($tokens, $this->classes, $inString);
		if ($content !== null)
		{
			if ($this->verbose) {echo 'Fix : ', $fullpath, PHP_EOL;}
			file_put_contents($fullpath, $content);
		}
	}
	
	public function migrateString($string, $inString = false)
	{
		$tokens = token_get_all($string);
		return $this->replaceClasses($tokens, $this->classes, $inString);
	}
	
	public function replaceFile($fullpath)
	{
		$content = file_get_contents($fullpath);
		$newContent = str_replace(array_keys($this->classes), array_values($this->classes), $content);
		if ($newContent !== $content)
		{
			if ($this->verbose) {echo 'Fix : ', $fullpath, PHP_EOL;}
			file_put_contents($fullpath, $newContent);
		}
	}	
	
	public function replaceString($string)
	{
		$newContent = str_replace(array_keys($this->classes), array_values($this->classes), $string);
		if ($newContent !== $string)
		{
			return $newContent;
		}
		return null;
	}	
	
	private function replaceClasses($tokens, $classes, $inString = false)
	{
		$content = array();
		$updated = false;
		$commentCheck = array();
		$commentReplace = array();
		$stringSearch = array();
		$stringReplace = array();
			
		foreach ($classes as $old => $new) 
		{
			$commentCheck[] = '* @param '.$old.' ';
			$commentCheck[] = '* @return '.$old;
			$commentCheck[] = '* @var '.$old;
			
			$commentReplace[] =  '* @param '.$new.' ';
			$commentReplace[] =  '* @return '.$new;	
			$commentReplace[] =  '* @var '.$new;
			if ($inString)
			{
				$stringSearch[] = '"'.$old.'"';
				$stringSearch[] = '\''.$old.'\'';			
				$stringReplace[] = '\''.$new.'\'';
				$stringReplace[] = '\''.$new.'\'';
			}
		}
		
		foreach ($tokens as $tn => $tv)
		{
			if (is_array($tv))
			{
				switch ($tv[0])
				{
					case T_STRING :
						if (isset($classes[$tv[1]]))
						{
							if ($this->isTokenClass($tn, $tokens))
							{
								$content[] = $classes[$tv[1]];
								$updated = true;
								continue;
							}
						}
						$content[] = $tv[1];
						break;
					case T_CONSTANT_ENCAPSED_STRING :
						if ($inString)
						{
							$str = str_replace($stringSearch, $stringReplace, $tv[1]);
							if ($str !== $tv[1])
							{
								$updated = true;
							}
							$content[] = $str;
						}
						else
						{
							$content[] = $tv[1];
						}
						break;
					case T_DOC_COMMENT :
						$str = str_replace($commentCheck, $commentReplace, $tv[1]);
						if ($str !== $tv[1])
						{
							$updated = true;
						}
						$content[] = $str;
						
						break;
					default :
						$content[] = $tv[1];
						break;
				}
			}
			else
			{
				$content[] = $tv;
			}
		}	
		return ($updated) ? implode('', $content) : null;
	}
	
	private function isTokenClass($tn, $tokens)
	{
		$i = $tn + 1;
		while ($i < count($tokens))
		{
			$tv = $tokens[$i];
			if (! is_array($tv))
			{
				break;
			}
			if ($tv[0] === T_WHITESPACE)
			{
				$i ++;
				continue;
			}
			if ($tv[0] === T_DOUBLE_COLON || $tv[0] === T_VARIABLE)
			{
				return true;
			}
			break;
		}
		
		$i = $tn - 1;
		$virg = false;
		while ($i >= 0)
		{
			$tv = $tokens[$i];
			if (! is_array($tv))
			{
				if ($tv === ',')
				{
					$virg = true;
					$i --;
					continue;
				}
				return false;
			}
			switch ($tv[0])
			{
				case T_CLASS :
				case T_INSTANCEOF :
				case T_EXTENDS :
				case T_NEW :
				case T_IMPLEMENTS :
					return true;
				
				case T_STRING :
					if (! $virg)
					{
						return false;
					}
				case T_WHITESPACE :
					$i --;
					break;
				default :
					return false;
			}
		}
	}	
}