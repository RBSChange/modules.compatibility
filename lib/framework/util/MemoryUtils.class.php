<?php
/**
 * @deprecated
 */
abstract class f_util_MemoryUtils
{
	/**
	 * @deprecated
	 */
	public static $limit = null;

	/**
	 * @deprecated
	 */
	public static function getLimit()
	{
		if (is_null(self::$limit))
		{
			$limit = trim(ini_get('memory_limit'));
			switch (strtolower($limit{strlen($limit) - 1}))
			{
				case 'g':
		   $limit *= 1024;
				case 'm':
		   $limit *= 1024;
				case 'k':
		   $limit *= 1024;
			}
			self::$limit = $limit;
		}
		return self::$limit;
	}

    /**
	 * @deprecated
	 */
	public static function getUsage($unit = null, $round = 2)
	{
		$usage = memory_get_usage();
		if (!is_null($unit))
		{
			switch (strtolower($unit))
			{
				case 'g':
		   $usage /= 1024;
				case 'm':
		   $usage /= 1024;
				case 'k':
		   $usage /= 1024;
			}
			$usage = round($usage, $round) . $unit;
		}
		return $usage;
	}

    /**
	 * @deprecated
	 */
	public static function getLoad($asPercentage = false)
	{
		$limit = self::getLimit();
		if ($limit == -1)
		{
			return -1;
		}
		$load = self::getUsage() / self::getLimit();
		if ($asPercentage)
		{
			return round($load * 100, 2);
		}
		return round($load, 2);
	}

    /**
	 * @deprecated
	 */
	public static function hasExceeded($threshold, $asPercentage = false)
	{
		$load = self::getLoad($asPercentage);
		if ($load == -1)
		{
			return false;
		}
		return $load > $threshold;
	}

    /**
	 * @deprecated
	 */
	public static function seeGlobalMemoryConsumption()
	{
		//http://www.procata.com/blog/archives/2004/05/27/rephlux-and-php-memory-usage/
		foreach (array_keys($GLOBALS) as $key) {
			echo "$key=" . strlen(serialize($GLOBALS[$key]))."<br>";
		}
	}

    /**
	 * @deprecated
	 */
	public static function logLimit()
	{
		Framework::debug("memory_limit ".f_util_StringUtils::return_bytes(ini_get('memory_limit')).",memory_consumption ,".memory_get_usage()." , max execution time ".ini_get('max_execution_time')." currentTime ".time());
	}
}